<?php
declare(strict_types=1);
?>

<div class="mobile-app-header">
    <a href="/dashboard.php" class="brand">
        <span class="brand-logo">
            <?= brand_logo_svg(26); ?>
        </span>

        <span class="brand-name"><?= e($brand['name']); ?></span>
    </a>

    <a href="/logout.php" class="btn btn-outline btn-sm">Sair</a>
</div>
