<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$user = require_login();

function redirectPasswordError(string $message): void
{
    $_SESSION['flash_error'] = $message;
    header('Location: /senha-alterar.php');
    exit;
}

function redirectPasswordSuccess(string $message): void
{
    $_SESSION['flash_success'] = $message;
    header('Location: /perfil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectPasswordError('Método inválido.');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirectPasswordError('Falha de segurança. Atualize a página e tente novamente.');
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$newPasswordConfirmation = $_POST['new_password_confirmation'] ?? '';

if ($currentPassword === '') {
    redirectPasswordError('Informe sua senha atual.');
}

if (strlen($newPassword) < 8) {
    redirectPasswordError('A nova senha precisa ter pelo menos 8 caracteres.');
}

if ($newPassword !== $newPasswordConfirmation) {
    redirectPasswordError('A confirmação da nova senha não confere.');
}

try {
    $stmt = $pdo->prepare("
        SELECT password_hash
        FROM users
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $user['id'],
    ]);

    $dbUser = $stmt->fetch();

    if (!$dbUser || !password_verify($currentPassword, $dbUser['password_hash'])) {
        redirectPasswordError('Senha atual incorreta.');
    }

    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $pdo->prepare("
        UPDATE users
        SET password_hash = :password_hash
        WHERE id = :id
        LIMIT 1
    ");

    $updateStmt->execute([
        ':password_hash' => $newPasswordHash,
        ':id' => $user['id'],
    ]);

    redirectPasswordSuccess('Senha alterada com sucesso.');

} catch (PDOException $e) {
    error_log('Erro ao alterar senha: ' . $e->getMessage());
    redirectPasswordError('Erro interno ao alterar a senha.');
}
