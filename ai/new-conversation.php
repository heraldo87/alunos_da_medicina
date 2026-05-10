<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /assistente.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $_SESSION['flash_error'] = 'Falha de segurança. Atualize a página e tente novamente.';
    header('Location: /assistente.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO ai_conversations (user_id, title, status)
        VALUES (:user_id, 'Nova conversa', 'active')
    ");

    $stmt->execute([
        ':user_id' => $user['id'],
    ]);

    $conversationId = (int) $pdo->lastInsertId();

    header('Location: /assistente.php?conversation_id=' . $conversationId);
    exit;

} catch (PDOException $e) {
    error_log('Erro ao criar conversa: ' . $e->getMessage());

    $_SESSION['flash_error'] = 'Não foi possível criar uma nova conversa.';
    header('Location: /assistente.php');
    exit;
}
