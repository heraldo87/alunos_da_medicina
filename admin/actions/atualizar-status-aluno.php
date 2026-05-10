<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

$user = require_admin_panel_access();

function redirectAdminApproval(string $type, string $message): void
{
    $_SESSION[$type === 'success' ? 'flash_success' : 'flash_error'] = $message;
    header('Location: /admin/aprovar-alunos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectAdminApproval('error', 'Método inválido.');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    redirectAdminApproval('error', 'Falha de segurança. Atualize a página e tente novamente.');
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$userId) {
    redirectAdminApproval('error', 'Usuário inválido.');
}

$allowedActions = ['approve', 'block'];

if (!in_array($action, $allowedActions, true)) {
    redirectAdminApproval('error', 'Ação inválida.');
}

$newStatus = $action === 'approve' ? 'active' : 'blocked';

try {
    $stmt = $pdo->prepare("
        UPDATE users
        SET status = :status
        WHERE id = :id
        AND status = 'pending'
        LIMIT 1
    ");

    $stmt->execute([
        ':status' => $newStatus,
        ':id' => $userId,
    ]);

    if ($stmt->rowCount() < 1) {
        redirectAdminApproval('error', 'Nenhum cadastro pendente foi atualizado.');
    }

    if ($action === 'approve') {
        redirectAdminApproval('success', 'Aluno aprovado com sucesso.');
    }

    redirectAdminApproval('success', 'Cadastro bloqueado com sucesso.');

} catch (PDOException $e) {
    error_log('Erro ao atualizar status do aluno: ' . $e->getMessage());
    redirectAdminApproval('error', 'Erro interno ao atualizar o cadastro.');
}
