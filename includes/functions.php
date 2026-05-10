<?php
declare(strict_types=1);

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function current_path(): string
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
}

function is_active_url(string $url): bool
{
    return current_path() === $url;
}

function first_name(string $fullName): string
{
    $fullName = trim($fullName);

    if ($fullName === '') {
        return 'Aluno';
    }

    return explode(' ', $fullName)[0];
}

function first_letter(string $text): string
{
    $text = trim($text);

    if ($text === '') {
        return 'A';
    }

    if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
        return mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8');
    }

    return strtoupper(substr($text, 0, 1));
}

function role_label(?string $role): string
{
    $labels = [
        'student' => 'Aluno',
        'admin' => 'Administrador',
        'moderator' => 'Moderador',
    ];

    return $labels[$role ?? 'student'] ?? 'Aluno';
}

function status_label(?string $status): string
{
    $labels = [
        'active' => 'Conta ativa',
        'pending' => 'Aguardando validação',
        'blocked' => 'Conta bloqueada',
    ];

    return $labels[$status ?? 'pending'] ?? 'Status indefinido';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && !empty($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}


function asset_url(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $absolutePath = dirname(__DIR__) . $path;

    if (is_file($absolutePath)) {
        return $path . '?v=' . filemtime($absolutePath);
    }

    return $path;
}


function can_access_admin_panel(?array $user): bool
{
    if (!$user) {
        return false;
    }

    $allowedRoles = ['admin', 'moderator'];

    return in_array($user['role'] ?? '', $allowedRoles, true);
}
