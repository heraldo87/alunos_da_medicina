<?php
declare(strict_types=1);

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
