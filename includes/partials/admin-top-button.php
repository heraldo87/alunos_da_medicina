<?php
declare(strict_types=1);
?>

<?php if (can_access_admin_panel($user ?? null)): ?>
    <a href="/admin/index.php" class="btn btn-primary btn-sm admin-top-button">
        Painel administrativo
    </a>
<?php endif; ?>
