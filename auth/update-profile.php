<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$user = require_login();

function redirectProfileError(string $message): void
{
    $_SESSION['flash_error'] = $message;
    header('Location: /perfil-editar.php');
    exit;
}

function redirectProfileSuccess(string $message): void
{
    $_SESSION['flash_success'] = $message;
    header('Location: /perfil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectProfileError('Método inválido.');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirectProfileError('Falha de segurança. Atualize a página e tente novamente.');
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($name === '') {
    redirectProfileError('Informe seu nome completo.');
}

if (mb_strlen($name) < 3) {
    redirectProfileError('O nome precisa ter pelo menos 3 caracteres.');
}

try {
    $stmt = $pdo->prepare("
        UPDATE users
        SET
            name = :name,
            phone = NULLIF(:phone, '')
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':id' => $user['id'],
    ]);

    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['phone'] = $phone;

    redirectProfileSuccess('Perfil atualizado com sucesso.');

} catch (PDOException $e) {
    error_log('Erro ao atualizar perfil: ' . $e->getMessage());
    redirectProfileError('Erro interno ao atualizar o perfil.');
}
