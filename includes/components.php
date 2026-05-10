<?php
declare(strict_types=1);

function brand_logo_svg(int $size = 28): string
{
    return <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 64 64" fill="none" aria-hidden="true">
    <path d="M10 16C18 14 25 16 32 22C39 16 46 14 54 16V50C46 48 39 50 32 56C25 50 18 48 10 50V16Z" stroke="white" stroke-width="4" stroke-linejoin="round"/>
    <path d="M32 22V56" stroke="white" stroke-width="4" stroke-linecap="round"/>
    <path d="M32 28V42" stroke="white" stroke-width="4" stroke-linecap="round"/>
    <path d="M25 35H39" stroke="white" stroke-width="4" stroke-linecap="round"/>
    <circle cx="17" cy="12" r="3" fill="white"/>
    <circle cx="47" cy="12" r="3" fill="white"/>
</svg>
SVG;
}

function sidebar_icon_svg(string $name): string
{
    $icons = [
        'home' => '
            <svg viewBox="0 0 24 24">
                <path d="M3 10.5L12 3l9 7.5"></path>
                <path d="M5 9.5V21h14V9.5"></path>
                <path d="M9 21v-6h6v6"></path>
            </svg>
        ',
        'user' => '
            <svg viewBox="0 0 24 24">
                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                <path d="M4 21a8 8 0 0 1 16 0"></path>
            </svg>
        ',
        'logout' => '
            <svg viewBox="0 0 24 24">
                <path d="M10 4H5v16h5"></path>
                <path d="M14 16l4-4-4-4"></path>
                <path d="M18 12H9"></path>
            </svg>
        ',
    ];

    $icon = $icons[$name] ?? $icons['home'];

    return '<span class="sidebar-icon" aria-hidden="true">' . $icon . '</span>';
}
