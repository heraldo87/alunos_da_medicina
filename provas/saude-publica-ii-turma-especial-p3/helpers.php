<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function prova_base_path(): string
{
    return __DIR__;
}

function prova_project_root(): string
{
    return dirname(__DIR__, 2);
}

function prova_url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/provas/modelo/index.php'), '/');
    return $base . '/' . ltrim($path, '/');
}

function prova_format_bytes(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }

    $units = ['KB', 'MB', 'GB'];
    $value = $bytes / 1024;

    foreach ($units as $unit) {
        if ($value < 1024) {
            return number_format($value, 1, ',', '.') . ' ' . $unit;
        }
        $value /= 1024;
    }

    return number_format($value, 1, ',', '.') . ' TB';
}

function prova_human_name(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = str_replace(['_', '-'], ' ', $name);
    $name = preg_replace('/\s+/', ' ', $name) ?: $name;

    if (function_exists('mb_convert_case')) {
        return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(trim($name));
}

function prova_scan_files(string $tipo, array $config): array
{
    $allowed = $config['extensoes'][$tipo] ?? [];
    $dir = prova_base_path() . DIRECTORY_SEPARATOR . $tipo;

    if (!is_dir($dir)) {
        return [];
    }

    $files = [];
    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $entry;
        if (!is_file($path)) {
            continue;
        }

        $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            continue;
        }

        $files[] = [
            'filename' => $entry,
            'title' => prova_human_name($entry),
            'extension' => strtoupper($extension),
            'size' => prova_format_bytes((int) filesize($path)),
            'modified_at' => date('d/m/Y', (int) filemtime($path)),
            'open_url' => prova_url('download.php?tipo=' . rawurlencode($tipo) . '&arquivo=' . rawurlencode($entry) . '&acao=abrir'),
            'download_url' => prova_url('download.php?tipo=' . rawurlencode($tipo) . '&arquivo=' . rawurlencode($entry) . '&acao=baixar'),
            'play_url' => prova_url('download.php?tipo=' . rawurlencode($tipo) . '&arquivo=' . rawurlencode($entry) . '&acao=tocar'),
        ];
    }

    usort($files, static fn (array $a, array $b): int => strnatcasecmp($a['filename'], $b['filename']));

    return $files;
}

function prova_safe_file_path(string $tipo, string $arquivo, array $config): ?string
{
    $allowed = $config['extensoes'][$tipo] ?? null;
    if ($allowed === null) {
        return null;
    }

    $arquivo = basename($arquivo);
    $extension = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed, true)) {
        return null;
    }

    $dir = realpath(prova_base_path() . DIRECTORY_SEPARATOR . $tipo);
    $path = realpath(prova_base_path() . DIRECTORY_SEPARATOR . $tipo . DIRECTORY_SEPARATOR . $arquivo);

    if (!$dir || !$path || !is_file($path)) {
        return null;
    }

    if (strpos($path, $dir . DIRECTORY_SEPARATOR) !== 0) {
        return null;
    }

    return $path;
}

function prova_content_type(string $path): string
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    $types = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt' => 'text/plain; charset=UTF-8',
        'md' => 'text/plain; charset=UTF-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'mp3' => 'audio/mpeg',
        'm4a' => 'audio/mp4',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'webm' => 'audio/webm',
    ];

    return $types[$ext] ?? 'application/octet-stream';
}

function prova_client_ip(): string
{
    // Em produção com proxy/CDN, ajuste esta função com cuidado.
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function prova_visitor_hash(array $config): string
{
    $salt = (string) ($config['analytics_salt'] ?? 'adm-prova');
    $ip = prova_client_ip();
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $day = gmdate('Y-m-d');

    return hash('sha256', $salt . '|' . $day . '|' . $ip . '|' . $ua);
}

function prova_ip_hash(array $config): string
{
    $salt = (string) ($config['analytics_salt'] ?? 'adm-prova');
    return hash('sha256', $salt . '|' . prova_client_ip());
}

function prova_track_event(array $config, string $eventType, ?string $area = null, ?string $fileName = null, array $extra = []): void
{
    $storage = $config['analytics_storage'] ?? 'file';

    if ($storage === 'database') {
        prova_track_event_database($config, $eventType, $area, $fileName, $extra);
        return;
    }

    prova_track_event_file($config, $eventType, $area, $fileName, $extra);
}

function prova_track_event_file(array $config, string $eventType, ?string $area = null, ?string $fileName = null, array $extra = []): void
{
    $storageDir = prova_base_path() . DIRECTORY_SEPARATOR . 'storage';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0755, true);
    }

    $line = [
        'created_at' => gmdate('c'),
        'prova_slug' => (string) ($config['slug'] ?? 'prova'),
        'event_type' => $eventType,
        'area' => $area,
        'file_name' => $fileName,
        'visitor_hash' => prova_visitor_hash($config),
        'ip_hash' => prova_ip_hash($config),
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'referrer' => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
        'extra' => $extra,
    ];

    @file_put_contents(
        $storageDir . DIRECTORY_SEPARATOR . 'analytics.ndjson',
        json_encode($line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

function prova_track_event_database(array $config, string $eventType, ?string $area = null, ?string $fileName = null, array $extra = []): void
{
    $databaseFile = prova_project_root() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
    if (!is_file($databaseFile)) {
        return;
    }

    require $databaseFile;

    if (!isset($pdo) || !$pdo instanceof PDO) {
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO prova_eventos
        (prova_slug, event_type, area, file_name, visitor_hash, ip_hash, user_agent, referrer, extra_json, created_at)
        VALUES
        (:prova_slug, :event_type, :area, :file_name, :visitor_hash, :ip_hash, :user_agent, :referrer, :extra_json, NOW())'
    );

    $stmt->execute([
        ':prova_slug' => (string) ($config['slug'] ?? 'prova'),
        ':event_type' => $eventType,
        ':area' => $area,
        ':file_name' => $fileName,
        ':visitor_hash' => prova_visitor_hash($config),
        ':ip_hash' => prova_ip_hash($config),
        ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ':referrer' => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
        ':extra_json' => json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

function prova_read_file_events(array $config): array
{
    $file = prova_base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'analytics.ndjson';
    if (!is_file($file)) {
        return [];
    }

    $events = [];
    $handle = fopen($file, 'rb');
    if (!$handle) {
        return [];
    }

    while (($line = fgets($handle)) !== false) {
        $event = json_decode(trim($line), true);
        if (is_array($event)) {
            $events[] = $event;
        }
    }

    fclose($handle);
    return $events;
}

function prova_group_count(array $events, string $key): array
{
    $counts = [];
    foreach ($events as $event) {
        $value = $event[$key] ?? 'indefinido';
        if ($value === null || $value === '') {
            $value = 'indefinido';
        }
        $counts[$value] = ($counts[$value] ?? 0) + 1;
    }

    arsort($counts);
    return $counts;
}
