<?php
declare(strict_types=1);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle ?? $brand['name']); ?></title>

    <meta name="description" content="<?= e($pageDescription ?? $brand['description']); ?>">
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/branding.css')); ?>">

</head>
<body>

<?php require __DIR__ . '/../partials/mobile-app-header.php'; ?>

<div class="app-shell">

    <?php require __DIR__ . '/../partials/app-sidebar.php'; ?>

    <main class="main-content">
