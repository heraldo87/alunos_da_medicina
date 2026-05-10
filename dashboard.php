<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login();

$pageTitle = 'Dashboard | ' . $brand['name'];
$pageDescription = 'Dashboard acadêmico da plataforma Alunos da Medicina.';

require __DIR__ . '/includes/layouts/app-start.php';

require __DIR__ . '/includes/partials/app-topbar.php';

require __DIR__ . '/includes/partials/dashboard-welcome.php';

require __DIR__ . '/includes/layouts/app-end.php';
