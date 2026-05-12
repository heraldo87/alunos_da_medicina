<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

$token = (string) ($_GET['token'] ?? '');
if (!hash_equals((string) $provaConfig['relatorio_token'], $token)) {
    http_response_code(403);
    exit('Acesso negado.');
}

$events = prova_read_file_events($provaConfig);
$eventsByType = prova_group_count($events, 'event_type');
$eventsByArea = prova_group_count($events, 'area');
$eventsByFile = prova_group_count($events, 'file_name');
$uniqueVisitors = count(array_unique(array_filter(array_column($events, 'visitor_hash'))));

function table_rows(array $data): string
{
    if (!$data) {
        return '<tr><td colspan="2">Sem dados ainda.</td></tr>';
    }

    $html = '';
    foreach ($data as $label => $count) {
        $html .= '<tr><td>' . e((string) $label) . '</td><td>' . e((string) $count) . '</td></tr>';
    }
    return $html;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatório da Prova | <?= e($provaConfig['materia']) ?></title>
    <link rel="stylesheet" href="/assets/css/branding.css">
    <link rel="stylesheet" href="<?= e(prova_url('assets/prova.css')) ?>?v=1">
</head>
<body>
    <main class="section">
        <div class="container">
            <div class="section-header">
                <span class="badge">Relatório interno</span>
                <h1 class="heading-lg"><?= e($provaConfig['materia']) ?></h1>
                <p class="text-lead">Métricas aproximadas sem login, sem sessão e sem cookie.</p>
            </div>

            <div class="prova-stats prova-report-stats">
                <div><strong><?= count($events) ?></strong><span>eventos</span></div>
                <div><strong><?= $uniqueVisitors ?></strong><span>visitantes únicos/dia aprox.</span></div>
                <div><strong><?= $eventsByType['clicou_whatsapp'] ?? 0 ?></strong><span>cliques WhatsApp</span></div>
                <div><strong><?= $eventsByType['finalizou_quiz'] ?? 0 ?></strong><span>quizzes finalizados</span></div>
            </div>

            <div class="grid grid-3 prova-report-grid">
                <section class="card">
                    <h2 class="heading-sm">Eventos</h2>
                    <table class="prova-table"><tbody><?= table_rows($eventsByType) ?></tbody></table>
                </section>
                <section class="card">
                    <h2 class="heading-sm">Áreas</h2>
                    <table class="prova-table"><tbody><?= table_rows($eventsByArea) ?></tbody></table>
                </section>
                <section class="card">
                    <h2 class="heading-sm">Arquivos</h2>
                    <table class="prova-table"><tbody><?= table_rows($eventsByFile) ?></tbody></table>
                </section>
            </div>
        </div>
    </main>
</body>
</html>
