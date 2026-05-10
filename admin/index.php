<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

$user = require_admin_panel_access();

$pageTitle = 'Painel administrativo | ' . $brand['name'];
$pageDescription = 'Área administrativa da plataforma Alunos da Medicina.';

$appPageBadge = 'Administração';
$appPageHeading = 'Painel administrativo';
$appPageDescription = 'Área reservada para administradores e moderadores.';

require __DIR__ . '/../includes/layouts/app-start.php';

require __DIR__ . '/../includes/partials/app-page-header.php';
?>

<section class="grid grid-3 mt-3">
    <article class="metric-card">
        <div class="metric-value">0</div>
        <div class="metric-label">Alunos cadastrados</div>
    </article>

    <article class="metric-card">
        <div class="metric-value">0</div>
        <div class="metric-label">Cadastros pendentes</div>
    </article>

    <article class="metric-card">
        <div class="metric-value">MVP</div>
        <div class="metric-label">Versão administrativa</div>
    </article>
</section>

<section class="card card-md mt-4">
    <h2 class="heading-md">Gestão da plataforma</h2>

    <p class="text-muted mt-1">
        Este painel será usado para aprovar alunos, gerenciar perfis, acompanhar cadastros
        e monitorar atividades importantes da plataforma.
    </p>

    <div class="profile-actions mt-3">
        <a href="/admin/aprovar-alunos.php" class="btn btn-primary">Aprovar alunos</a>
        <a href="#" class="btn btn-outline">Gerenciar usuários em breve</a>
    </div>
</section>

<?php
require __DIR__ . '/../includes/layouts/app-end.php';
