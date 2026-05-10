<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/n8n.php';

$user = require_login();

function redirectAssistantError(string $message, ?int $conversationId = null): void
{
    $_SESSION['flash_error'] = $message;

    $url = '/assistente.php';

    if ($conversationId) {
        $url .= '?conversation_id=' . $conversationId;
    }

    header('Location: ' . $url);
    exit;
}

function redirectAssistantSuccess(int $conversationId): void
{
    header('Location: /assistente.php?conversation_id=' . $conversationId);
    exit;
}

function extractN8nAnswer(mixed $data): ?string
{
    if (is_string($data)) {
        $data = trim($data);
        return $data !== '' ? $data : null;
    }

    if (!is_array($data)) {
        return null;
    }

    if (isset($data[0])) {
        return extractN8nAnswer($data[0]);
    }

    if (isset($data['json'])) {
        return extractN8nAnswer($data['json']);
    }

    foreach (['answer', 'output', 'text', 'response', 'message'] as $key) {
        if (isset($data[$key]) && is_string($data[$key]) && trim($data[$key]) !== '') {
            return trim($data[$key]);
        }
    }

    return null;
}

function getOrCreateConversation(PDO $pdo, int $userId, ?int $conversationId = null): int
{
    if ($conversationId) {
        $stmt = $pdo->prepare("
            SELECT id
            FROM ai_conversations
            WHERE id = :id
            AND user_id = :user_id
            AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $conversationId,
            ':user_id' => $userId,
        ]);

        $conversation = $stmt->fetch();

        if ($conversation) {
            return (int) $conversation['id'];
        }
    }

    $insertStmt = $pdo->prepare("
        INSERT INTO ai_conversations (user_id, title, status)
        VALUES (:user_id, 'Nova conversa', 'active')
    ");

    $insertStmt->execute([
        ':user_id' => $userId,
    ]);

    return (int) $pdo->lastInsertId();
}

function updateConversationTitle(PDO $pdo, int $conversationId, string $message): void
{
    $stmt = $pdo->prepare("
        SELECT title
        FROM ai_conversations
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $conversationId,
    ]);

    $conversation = $stmt->fetch();

    if (!$conversation) {
        return;
    }

    $currentTitle = trim((string) $conversation['title']);

    if (!in_array($currentTitle, ['Nova conversa', 'Assistente de Estudos'], true)) {
        return;
    }

    $title = trim($message);

    if (function_exists('mb_substr')) {
        $title = mb_substr($title, 0, 70, 'UTF-8');
    } else {
        $title = substr($title, 0, 70);
    }

    if ($title === '') {
        $title = 'Nova conversa';
    }

    $updateStmt = $pdo->prepare("
        UPDATE ai_conversations
        SET title = :title
        WHERE id = :id
        LIMIT 1
    ");

    $updateStmt->execute([
        ':title' => $title,
        ':id' => $conversationId,
    ]);
}

function touchConversation(PDO $pdo, int $conversationId): void
{
    $stmt = $pdo->prepare("
        UPDATE ai_conversations
        SET updated_at = NOW()
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $conversationId,
    ]);
}

function saveAiMessage(
    PDO $pdo,
    int $conversationId,
    int $userId,
    string $role,
    string $content,
    ?string $model = null,
    ?string $errorMessage = null
): void {
    $stmt = $pdo->prepare("
        INSERT INTO ai_messages (
            conversation_id,
            user_id,
            role,
            content,
            model,
            error_message
        )
        VALUES (
            :conversation_id,
            :user_id,
            :role,
            :content,
            :model,
            :error_message
        )
    ");

    $stmt->execute([
        ':conversation_id' => $conversationId,
        ':user_id' => $userId,
        ':role' => $role,
        ':content' => $content,
        ':model' => $model,
        ':error_message' => $errorMessage,
    ]);
}

function getRecentHistory(PDO $pdo, int $conversationId): array
{
    $stmt = $pdo->prepare("
        SELECT role, content
        FROM ai_messages
        WHERE conversation_id = :conversation_id
        ORDER BY id DESC
        LIMIT 12
    ");

    $stmt->execute([
        ':conversation_id' => $conversationId,
    ]);

    $messages = array_reverse($stmt->fetchAll());

    return array_map(static function (array $message): array {
        return [
            'role' => $message['role'],
            'content' => $message['content'],
        ];
    }, $messages);
}

function callN8nAssistant(array $payload, array $n8nConfig): array
{
    $webhookUrl = $n8nConfig['ai_webhook_url'] ?? '';
    $timeout = (int) ($n8nConfig['ai_timeout'] ?? 90);

    if ($webhookUrl === '') {
        throw new RuntimeException('Webhook do n8n não configurado.');
    }

    $ch = curl_init($webhookUrl);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => $timeout,
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($error !== '') {
        throw new RuntimeException('Erro cURL ao chamar n8n: ' . $error);
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new RuntimeException('n8n respondeu com HTTP ' . $httpCode . ': ' . (string) $response);
    }

    $decoded = json_decode((string) $response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $plainText = trim((string) $response);

        if ($plainText !== '') {
            return [
                'output' => $plainText,
                'model' => 'n8n-plain-text',
            ];
        }

        throw new RuntimeException('Resposta inválida do n8n.');
    }

    if (!is_array($decoded)) {
        return [
            'output' => (string) $decoded,
            'model' => 'n8n-response',
        ];
    }

    return $decoded;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectAssistantError('Método inválido.');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirectAssistantError('Falha de segurança. Atualize a página e tente novamente.');
}

$message = trim($_POST['message'] ?? '');

if ($message === '') {
    redirectAssistantError('Digite uma pergunta para o Assistente de Estudos.');
}

if (mb_strlen($message) > 4000) {
    redirectAssistantError('Sua pergunta está muito longa. Tente resumir em até 4000 caracteres.');
}

$userId = (int) $user['id'];
$conversationIdFromPost = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT) ?: null;
$conversationId = null;

try {
    $conversationId = getOrCreateConversation($pdo, $userId, $conversationIdFromPost);

    updateConversationTitle($pdo, $conversationId, $message);

    saveAiMessage(
        $pdo,
        $conversationId,
        $userId,
        'user',
        $message
    );

    touchConversation($pdo, $conversationId);

    $history = getRecentHistory($pdo, $conversationId);

    $payload = [
        'token' => $n8nConfig['ai_webhook_token'],
        'sessionId' => 'alunos_medicina_conversation_' . $conversationId,
        'conversationId' => $conversationId,
        'user' => [
            'id' => $userId,
            'name' => $user['name'] ?? 'Aluno',
            'email' => $user['email'] ?? null,
            'role' => $user['role'] ?? 'student',
        ],
        'message' => $message,
        'history' => $history,
    ];

    $n8nResponse = callN8nAssistant($payload, $n8nConfig);

    $answer = extractN8nAnswer($n8nResponse);

    if (!is_string($answer) || trim($answer) === '') {
        throw new RuntimeException('O n8n não retornou uma resposta válida.');
    }

    $model = $n8nResponse['model'] ?? 'n8n-ai-agent';

    saveAiMessage(
        $pdo,
        $conversationId,
        $userId,
        'assistant',
        trim($answer),
        is_string($model) ? $model : 'n8n-ai-agent'
    );

    touchConversation($pdo, $conversationId);

    redirectAssistantSuccess($conversationId);

} catch (Throwable $e) {
    error_log('Erro no Assistente de Estudos: ' . $e->getMessage());

    if ($conversationId) {
        saveAiMessage(
            $pdo,
            $conversationId,
            $userId,
            'assistant',
            'Não consegui responder agora. Tente novamente em instantes.',
            'n8n-ai-agent',
            $e->getMessage()
        );

        touchConversation($pdo, $conversationId);
    }

    redirectAssistantError('Não foi possível obter resposta do Assistente de Estudos agora.', $conversationId);
}
