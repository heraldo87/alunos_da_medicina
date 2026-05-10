<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login();

$pageTitle = 'Perfil | ' . $brand['name'];
$pageDescription = 'Perfil do aluno na plataforma Alunos da Medicina.';

$appPageBadge = 'Perfil do aluno';
$appPageHeading = 'Meu perfil';
$appPageDescription = 'Consulte seus dados pessoais, instituição, perfil de acesso e status da conta.';

require __DIR__ . '/includes/layouts/app-start.php';

require __DIR__ . '/includes/partials/app-page-header.php';

require __DIR__ . '/includes/partials/profile-content.php';

require __DIR__ . '/includes/layouts/app-end.php';
