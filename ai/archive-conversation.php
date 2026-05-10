<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$user = require_login();

function redirectAssistantArchive(string $type, string $message): void
{
    $_SESSION[$type === 'success' ? 'flash_success' : 'flash_error'] = $message;
    header('Location: /assistente.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectAssistantArchive('error', 'Método inválido.');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirectAssistantArchive('error', 'Falha de segurança. Atualize a página e tente novamente.');
}

$conversationId = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);

if (!$conversationId) {
    redirectAssistantArchive('error', 'Conversa inválida.');
}

try {
    $stmt = $pdo->prepare("
        UPDATE ai_conversations
        SET status = 'archived'
        WHERE id = :id
        AND user_id = :user_id
        AND status = 'active'
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $conversationId,
        ':user_id' => $user['id'],
    ]);

    if ($stmt->rowCount() < 1) {
        redirectAssistantArchive('error', 'Não foi possível remover esta conversa da sua lista.');
    }

    redirectAssistantArchive('success', 'Conversa removida da sua lista com sucesso.');

} catch (PDOException $e) {
    error_log('Erro ao arquivar conversa: ' . $e->getMessage());
    redirectAssistantArchive('error', 'Erro interno ao remover a conversa.');
}
