<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';

function redirectWithError(string $message): void
{
    $_SESSION['flash_error'] = $message;
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Método inválido.');
}

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    empty($csrfToken) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    redirectWithError('Falha de segurança. Atualize a página e tente novamente.');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Informe um e-mail válido.');
}

if ($password === '') {
    redirectWithError('Informe sua senha.');
}

try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            email,
            phone,
            institution,
            password_hash,
            role,
            status,
            last_login_at,
            created_at
        FROM users
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch();

    if (!$user) {
        redirectWithError('E-mail ou senha incorretos.');
    }

    if (!password_verify($password, $user['password_hash'])) {
        redirectWithError('E-mail ou senha incorretos.');
    }

    if ($user['status'] !== 'active') {
        redirectWithError('Sua conta ainda não está ativa. Aguarde a validação administrativa.');
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'institution' => $user['institution'],
        'role' => $user['role'],
        'status' => $user['status'],
    ];

    $updateStmt = $pdo->prepare("
        UPDATE users
        SET last_login_at = NOW()
        WHERE id = :id
        LIMIT 1
    ");

    $updateStmt->execute([
        ':id' => $user['id']
    ]);

    unset($_SESSION['csrf_token']);

    header('Location: /dashboard.php');
    exit;

} catch (PDOException $e) {
    error_log('Erro no login: ' . $e->getMessage());
    redirectWithError('Erro interno ao tentar fazer login.');
}
