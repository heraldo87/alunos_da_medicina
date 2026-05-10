<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$user = require_login();

function redirectAssistantTitle(string $type, string $message, ?int $conversationId = null): void
{
    $_SESSION[$type === 'success' ? 'flash_success' : 'flash_error'] = $message;

    $url = '/assistente.php';

    if ($conversationId) {
        $url .= '?conversation_id=' . $conversationId;
    }

    header('Location: ' . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectAssistantTitle('error', 'Método inválido.');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirectAssistantTitle('error', 'Falha de segurança. Atualize a página e tente novamente.');
}

$conversationId = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);
$title = trim($_POST['title'] ?? '');

if (!$conversationId) {
    redirectAssistantTitle('error', 'Conversa inválida.');
}

if ($title === '') {
    redirectAssistantTitle('error', 'Informe um título para a conversa.', $conversationId);
}

if (mb_strlen($title) > 90) {
    redirectAssistantTitle('error', 'O título deve ter no máximo 90 caracteres.', $conversationId);
}

try {
    $checkStmt = $pdo->prepare("
        SELECT id
        FROM ai_conversations
        WHERE id = :id
        AND user_id = :user_id
        AND status = 'active'
        LIMIT 1
    ");

    $checkStmt->execute([
        ':id' => $conversationId,
        ':user_id' => $user['id'],
    ]);

    if (!$checkStmt->fetch()) {
        redirectAssistantTitle('error', 'Conversa não encontrada.', $conversationId);
    }

    $stmt = $pdo->prepare("
        UPDATE ai_conversations
        SET title = :title
        WHERE id = :id
        AND user_id = :user_id
        AND status = 'active'
        LIMIT 1
    ");

    $stmt->execute([
        ':title' => $title,
        ':id' => $conversationId,
        ':user_id' => $user['id'],
    ]);

    redirectAssistantTitle('success', 'Título atualizado com sucesso.', $conversationId);

} catch (PDOException $e) {
    error_log('Erro ao atualizar título da conversa: ' . $e->getMessage());
    redirectAssistantTitle('error', 'Erro interno ao atualizar o título.', $conversationId);
}
