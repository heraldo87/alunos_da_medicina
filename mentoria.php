<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login();

$pageTitle = 'MentorIA | ' . $brand['name'];
$pageDescription = 'Central de inteligência acadêmica da plataforma Alunos da Medicina.';

$appPageBadge = 'Aplicativo inteligente';
$appPageHeading = 'MentorIA';
$appPageDescription = 'Seu espaço de apoio acadêmico com inteligência artificial para estudo, revisão e prática guiada.';

require __DIR__ . '/includes/layouts/app-start.php';
require __DIR__ . '/includes/partials/app-page-header.php';
?>

<section class="mentoria-hero-card">
    <div class="mentoria-hero-content">
        <span class="badge badge-success">Novo</span>

        <h2>Seu mentor inteligente para estudos de medicina.</h2>

        <p>
            Escolha como deseja estudar com IA: por texto, com histórico organizado por assunto,
            ou por voz, em uma experiência mais interativa e imersiva.
        </p>
    </div>

    <div class="mentoria-hero-icon">
        <div class="ios-app-icon-large">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 3l7 4v10l-7 4-7-4V7l7-4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="M9.5 11.5h5M9.5 14.5h3.5M8 8.5h8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>
    </div>
</section>

<section class="mentoria-grid">
    <a href="/assistente.php" class="mentoria-card">
        <div class="mentoria-card-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 3a7 7 0 0 0-7 7c0 2.3 1.1 4.4 2.8 5.7L8 21l4-2 4 2 .2-5.3A7 7 0 0 0 12 3z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="M9 10h.01" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                <path d="M15 10h.01" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                <path d="M9.5 14c1.5 1 3.5 1 5 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>

        <h3>Assistente de Estudos</h3>
        <p>Converse por texto, organize dúvidas por assunto e mantenha conversas separadas.</p>
    </a>

    <a href="/professor-ao-vivo.php" class="mentoria-card">
        <div class="mentoria-card-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 3a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V6a3 3 0 0 0-3-3Z" fill="none" stroke="currentColor" stroke-width="1.8"/>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M12 19v3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M8 22h8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>

        <h3>Professor ao Vivo</h3>
        <p>Converse por voz em tempo real para revisar conteúdos e praticar explicações.</p>
    </a>
</section>

<?php
require __DIR__ . '/includes/layouts/app-end.php';
