<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

$tipo = (string) ($_GET['tipo'] ?? '');
$arquivo = (string) ($_GET['arquivo'] ?? '');
$acao = (string) ($_GET['acao'] ?? 'abrir');

$allowedActions = ['abrir', 'baixar', 'tocar'];
if (!in_array($acao, $allowedActions, true)) {
    $acao = 'abrir';
}

$path = prova_safe_file_path($tipo, $arquivo, $provaConfig);
if (!$path) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

$event = match ($acao) {
    'baixar' => 'baixou_' . $tipo,
    'tocar' => 'carregou_audio_' . $tipo,
    default => 'abriu_' . $tipo,
};

prova_track_event($provaConfig, $event, $tipo, basename($path));

$contentType = prova_content_type($path);
$disposition = $acao === 'baixar' ? 'attachment' : 'inline';

header('Content-Type: ' . $contentType);
header('Content-Length: ' . (string) filesize($path));
header('Content-Disposition: ' . $disposition . '; filename="' . addslashes(basename($path)) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');

readfile($path);
exit;
