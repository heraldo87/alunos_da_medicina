<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/openai.php';

$user = require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método inválido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($openaiConfig['api_key'])) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'OPENAI_API_KEY não configurada.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) $user['id'];
$auditNonce = bin2hex(random_bytes(32));

try {
    $auditStmt = $pdo->prepare("
        INSERT INTO realtime_sessions (
            user_id,
            status,
            audit_nonce
        )
        VALUES (
            :user_id,
            'active',
            :audit_nonce
        )
    ");

    $auditStmt->execute([
        ':user_id' => $userId,
        ':audit_nonce' => $auditNonce,
    ]);

    $localSessionId = (int) $pdo->lastInsertId();
} catch (PDOException $e) {
    error_log('Erro ao criar sessão local de auditoria: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Não foi possível criar sessão local.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$safetyIdentifier = hash('sha256', 'alunos-medicina-realtime-user-' . (string) $userId);

$instructions = <<<TEXT
Você é um Professor de Medicina ao vivo da plataforma Alunos da Medicina.

Converse em português do Brasil, com linguagem didática, clara e adequada para alunos do início da graduação em medicina.

Objetivo:
- Ensinar conceitos médicos e biomédicos passo a passo.
- Confirmar se o aluno entendeu antes de avançar para temas mais complexos.
- Usar exemplos simples, analogias e organização em tópicos quando útil.
- Explicar termos técnicos quando aparecerem.

Limites:
- Não forneça diagnóstico, prescrição, tratamento, conduta clínica individualizada ou substituição de orientação médica.
- Se o aluno relatar um caso real de paciente, oriente buscar supervisão profissional, professor, preceptor ou serviço de saúde.
- Não substitua livro-texto, professor, diretriz institucional ou avaliação profissional.
- Se o aluno fizer brincadeiras, perguntas ofensivas ou fora do contexto acadêmico, redirecione educadamente para um tema de estudo.

Estilo:
- Seja acolhedor, objetivo e professoral.
- Faça perguntas curtas para verificar entendimento.
- Evite respostas longas demais em voz; prefira blocos curtos.
TEXT;

$payload = [
    'session' => [
        'type' => 'realtime',
        'model' => $openaiConfig['realtime_model'],
        'instructions' => $instructions,
        'audio' => [
            'input' => [
                'transcription' => [
                    'model' => $openaiConfig['realtime_transcribe_model'],
                    'language' => 'pt',
                ],
            ],
            'output' => [
                'voice' => $openaiConfig['realtime_voice'],
            ],
        ],
    ],
];

$ch = curl_init('https://api.openai.com/v1/realtime/client_secrets');

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $openaiConfig['api_key'],
        'Content-Type: application/json',
        'Accept: application/json',
        'OpenAI-Safety-Identifier: ' . $safetyIdentifier,
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => $openaiConfig['timeout'],
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($error !== '') {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro cURL ao criar sessão Realtime.',
        'details' => $error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao criar sessão Realtime.',
        'http_code' => $httpCode,
        'response' => json_decode((string) $response, true) ?: $response,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode((string) $response, true);

if (!is_array($data)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Resposta inválida da OpenAI.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data['local_session_id'] = $localSessionId;
$data['audit_nonce'] = $auditNonce;

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
