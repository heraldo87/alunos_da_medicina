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
?>

<section class="dashboard-apps-section">
    <div class="section-heading">
        <h2>Aplicativos</h2>
        <p>Acesse os recursos inteligentes da plataforma.</p>
    </div>

    <a href="/mentoria.php" class="mentoria-dashboard-app" aria-label="Abrir MentorIA">
        <div class="mentoria-dashboard-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                    d="M12 3l7 4v10l-7 4-7-4V7l7-4Z"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                />
                <path
                    d="M9.5 11.5h5M9.5 14.5h3.5M8 8.5h8"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                />
            </svg>
        </div>

        <div class="mentoria-dashboard-content">
            <span class="mentoria-dashboard-kicker">Aplicativo inteligente</span>

            <h3>MentorIA</h3>

            <p>
                Estude com apoio de inteligência artificial: converse por texto,
                organize dúvidas por assunto e acesse o professor ao vivo por voz.
            </p>

            <div class="mentoria-dashboard-tags">
                <span>Assistente de Estudos</span>
                <span>Professor ao Vivo</span>
                <span>IA acadêmica</span>
            </div>
        </div>

        <div class="mentoria-dashboard-action">
            <span>Abrir</span>

            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                    d="M5 12h14"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <path
                    d="M13 6l6 6-6 6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </div>
    </a>
</section>

<?php
require __DIR__ . '/includes/layouts/app-end.php';
