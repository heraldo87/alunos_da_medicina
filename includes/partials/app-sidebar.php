<?php
declare(strict_types=1);
?>

<aside class="sidebar">
    <a href="/dashboard.php" class="brand sidebar-brand">
        <span class="brand-logo">
            <?= brand_logo_svg(28); ?>
        </span>

        <span>
            <span class="brand-name"><?= e($brand['name']); ?></span>
            <small class="sidebar-brand-subtitle"><?= e($brand['slogan']); ?></small>
        </span>
    </a>

    <nav class="sidebar-menu" aria-label="Menu principal">
        <?php foreach ($dashboardMenu as $item): ?>
            <a
                class="sidebar-link <?= is_active_url($item['url']) ? 'active' : ''; ?>"
                href="<?= e($item['url']); ?>"
            >
                <?= sidebar_icon_svg($item['icon']); ?>
                <span><?= e($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
