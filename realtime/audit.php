<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$user = require_login();

header('Content-Type: application/json; charset=utf-8');

function json_response(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function trim_text(string $text, int $limit = 900): string
{
    $text = trim($text);

    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $limit, 'UTF-8');
    }

    return substr($text, 0, $limit);
}

function analyze_realtime_text(string $text, string $role): array
{
    $normalized = function_exists('mb_strtolower')
        ? mb_strtolower($text, 'UTF-8')
        : strtolower($text);

    $rules = [
        [
            'level' => 'medium',
            'reason' => 'Possível uso ofensivo ou brincadeira inadequada',
            'patterns' => [
                'idiota', 'burro', 'xingar', 'palavrão', 'foda-se', 'porra',
            ],
        ],
        [
            'level' => 'medium',
            'reason' => 'Possível tentativa de burlar prova ou avaliação',
            'patterns' => [
                'cola da prova', 'resposta da prova', 'me passa a resposta', 'gabarito da prova',
            ],
        ],
        [
            'level' => 'high',
            'reason' => 'Possível conteúdo sexual explícito ou inadequado',
            'patterns' => [
                'pornô', 'sexo explícito', 'nude', 'nudes',
            ],
        ],
        [
            'level' => 'high',
            'reason' => 'Possível pedido de atividade ilegal ou perigosa',
            'patterns' => [
                'hackear', 'roubar senha', 'fraudar', 'falsificar documento',
            ],
        ],
        [
            'level' => 'low',
            'reason' => 'Possível tema clínico sensível',
            'patterns' => [
                'qual remédio eu tomo', 'posso tomar', 'diagnóstico para mim', 'tratamento para mim',
            ],
        ],
    ];

    foreach ($rules as $rule) {
        foreach ($rule['patterns'] as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return [
                    'risk_level' => $rule['level'],
                    'flag_reason' => $rule['reason'],
                ];
            }
        }
    }

    return [
        'risk_level' => 'none',
        'flag_reason' => null,
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'error' => 'Método inválido.'
    ], 405);
}

$rawInput = file_get_contents('php://input');
$data = json_decode((string) $rawInput, true);

if (!is_array($data)) {
    json_response([
        'success' => false,
        'error' => 'JSON inválido.'
    ], 400);
}

$sessionId = filter_var($data['local_session_id'] ?? null, FILTER_VALIDATE_INT);
$auditNonce = trim((string) ($data['audit_nonce'] ?? ''));
$eventType = trim((string) ($data['event_type'] ?? 'unknown'));
$role = trim((string) ($data['role'] ?? 'user'));
$text = trim((string) ($data['text'] ?? ''));

if (!$sessionId || $auditNonce === '') {
    json_response([
        'success' => false,
        'error' => 'Sessão inválida.'
    ], 422);
}

if (!in_array($role, ['user', 'assistant', 'system'], true)) {
    $role = 'user';
}

try {
    $stmt = $pdo->prepare("
        SELECT id, user_id, audit_nonce, status
        FROM realtime_sessions
        WHERE id = :id
        AND user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $sessionId,
        ':user_id' => $user['id'],
    ]);

    $session = $stmt->fetch();

    if (!$session || !hash_equals((string) $session['audit_nonce'], $auditNonce)) {
        json_response([
            'success' => false,
            'error' => 'Sessão não autorizada.'
        ], 403);
    }

    $analysis = analyze_realtime_text($text, $role);

    $metadata = [
        'browser_event_type' => $eventType,
        'text_length' => strlen($text),
        'received_at' => date('c'),
    ];

    $insertStmt = $pdo->prepare("
        INSERT INTO realtime_audit_events (
            realtime_session_id,
            user_id,
            event_type,
            role,
            transcript_excerpt,
            risk_level,
            flag_reason,
            metadata_json
        )
        VALUES (
            :realtime_session_id,
            :user_id,
            :event_type,
            :role,
            :transcript_excerpt,
            :risk_level,
            :flag_reason,
            :metadata_json
        )
    ");

    $insertStmt->execute([
        ':realtime_session_id' => $sessionId,
        ':user_id' => $user['id'],
        ':event_type' => $eventType,
        ':role' => $role,
        ':transcript_excerpt' => $text !== '' ? trim_text($text) : null,
        ':risk_level' => $analysis['risk_level'],
        ':flag_reason' => $analysis['flag_reason'],
        ':metadata_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $turnColumn = $role === 'assistant'
        ? 'total_assistant_turns'
        : ($role === 'user' ? 'total_user_turns' : null);

    if ($eventType === 'session_ended') {
        $updateStmt = $pdo->prepare("
            UPDATE realtime_sessions
            SET
                status = 'ended',
                ended_at = NOW(),
                duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW())
            WHERE id = :id
            LIMIT 1
        ");

        $updateStmt->execute([
            ':id' => $sessionId,
        ]);
    } else {
        $sql = "
            UPDATE realtime_sessions
            SET updated_at = NOW()
        ";

        if ($turnColumn) {
            $sql .= ", {$turnColumn} = {$turnColumn} + 1";
        }

        if ($analysis['risk_level'] !== 'none') {
            $sql .= ",
                flagged_count = flagged_count + 1,
                risk_level = CASE
                    WHEN risk_level = 'high' THEN 'high'
                    WHEN :risk_level = 'high' THEN 'high'
                    WHEN risk_level = 'medium' THEN 'medium'
                    WHEN :risk_level = 'medium' THEN 'medium'
                    WHEN risk_level = 'low' THEN 'low'
                    WHEN :risk_level = 'low' THEN 'low'
                    ELSE risk_level
                END,
                last_flag_reason = :flag_reason
            ";
        }

        $sql .= " WHERE id = :id LIMIT 1";

        $updateStmt = $pdo->prepare($sql);

        $params = [
            ':id' => $sessionId,
        ];

        if ($analysis['risk_level'] !== 'none') {
            $params[':risk_level'] = $analysis['risk_level'];
            $params[':flag_reason'] = $analysis['flag_reason'];
        }

        $updateStmt->execute($params);
    }

    json_response([
        'success' => true,
        'risk_level' => $analysis['risk_level'],
        'flag_reason' => $analysis['flag_reason'],
    ]);

} catch (Throwable $e) {
    error_log('Erro na auditoria Realtime: ' . $e->getMessage());

    json_response([
        'success' => false,
        'error' => 'Erro interno de auditoria.'
    ], 500);
}
