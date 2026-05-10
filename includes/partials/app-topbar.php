<?php
declare(strict_types=1);

$firstName = first_name((string) ($user['name'] ?? 'Aluno'));
$roleLabel = role_label($user['role'] ?? 'student');
$statusLabel = status_label($user['status'] ?? 'pending');
?>

<div class="topbar">
    <div class="page-title">
        <span class="badge">Dashboard acadêmico</span>
        <h1>Olá, <?= e($firstName); ?>.</h1>
        <p>Bem-vindo ao seu ambiente de organização e estudos em medicina.</p>
    </div>

    <div class="topbar-actions">
        <?php require __DIR__ . '/admin-top-button.php'; ?>

        <div class="dashboard-user-card">
        <div class="avatar">
            <?= e(first_letter($firstName)); ?>
        </div>

        <div>
            <strong><?= e($user['name'] ?? 'Aluno'); ?></strong>
            <span><?= e($roleLabel); ?> · <?= e($statusLabel); ?></span>
        </div>
        </div>
    </div>
</div>
