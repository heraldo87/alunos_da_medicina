<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/components.php';

$brand = [
    'name' => 'Alunos da Medicina',
    'short_name' => 'ADM',
    'slogan' => 'Estude melhor. Evolua com inteligência.',
    'description' => 'Plataforma acadêmica para estudantes de medicina.',
    'institution' => 'UPDS - Universidad Privada Domingo Savio',
];

$dashboardMenu = [
    [
        'label' => 'Início',
        'url' => '/dashboard.php',
        'icon' => 'home',
    ],
    [
        'label' => 'Perfil',
        'url' => '/perfil.php',
        'icon' => 'user',
    ],
    [
        'label' => 'Sair',
        'url' => '/logout.php',
        'icon' => 'logout',
    ],
];
