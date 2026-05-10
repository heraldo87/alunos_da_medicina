<?php
declare(strict_types=1);
?>

<header class="header">
    <div class="container nav">
        <a href="/" class="brand" aria-label="<?= e($brand['name']); ?>">
            <span class="brand-logo">
                <?= brand_logo_svg(28); ?>
            </span>

            <span class="brand-name"><?= e($brand['name']); ?></span>
        </a>

        <nav class="nav-actions">
            <a href="/login.php" class="btn btn-outline">Entrar</a>
            <a href="/cadastro.php" class="btn btn-primary">Criar conta</a>
        </nav>
    </div>
</header>
