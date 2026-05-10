<?php
declare(strict_types=1);

$appPageBadge = $appPageBadge ?? 'Área do aluno';
$appPageHeading = $appPageHeading ?? 'Página';
$appPageDescription = $appPageDescription ?? '';

$firstName = first_name((string) ($user['name'] ?? 'Aluno'));
$roleLabel = role_label($user['role'] ?? 'student');
$statusLabel = status_label($user['status'] ?? 'pending');
?>

<div class="topbar">
    <div class="page-title">
        <span class="badge"><?= e($appPageBadge); ?></span>
        <h1><?= e($appPageHeading); ?></h1>

        <?php if ($appPageDescription): ?>
            <p><?= e($appPageDescription); ?></p>
        <?php endif; ?>
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

<?php
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success mb-3">
        <?= e($flashSuccess); ?>
    </div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="alert alert-danger mb-3">
        <?= e($flashError); ?>
    </div>
<?php endif; ?>
