<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => false, 'message' => 'Método não permitido.']);
    exit;
}

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);

if (!is_array($data)) {
    $data = $_POST;
}

$eventType = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($data['event_type'] ?? 'evento_indefinido'));
$area = isset($data['area']) ? substr((string) $data['area'], 0, 80) : null;
$fileName = isset($data['file_name']) ? basename((string) $data['file_name']) : null;
$extra = isset($data['extra']) && is_array($data['extra']) ? $data['extra'] : [];

try {
    prova_track_event($provaConfig, $eventType, $area, $fileName, $extra);
    $ok = true;
} catch (Throwable $e) {
    error_log('Erro ao registrar analytics da prova: ' . $e->getMessage());
    $ok = false;
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['ok' => $ok]);
