<?php
declare(strict_types=1);

function require_login(): array
{
    if (empty($_SESSION['user']) || !is_array($_SESSION['user'])) {
        $_SESSION['flash_error'] = 'Faça login para acessar esta área.';
        header('Location: /login.php');
        exit;
    }

    return $_SESSION['user'];
}


function require_admin_panel_access(): array
{
    $user = require_login();

    if (!can_access_admin_panel($user)) {
        http_response_code(403);
        exit('Acesso não autorizado.');
    }

    return $user;
}
