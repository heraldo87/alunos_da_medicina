<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

load_env(dirname(__DIR__) . '/.env');

$n8nConfig = [
    'ai_webhook_url' => env_value('N8N_AI_WEBHOOK_URL'),
    'ai_webhook_token' => env_value('N8N_AI_WEBHOOK_TOKEN'),
    'ai_timeout' => (int) env_value('N8N_AI_TIMEOUT', 90),
];

if (empty($n8nConfig['ai_webhook_url'])) {
    error_log('N8N_AI_WEBHOOK_URL não configurado no .env');
}

if (empty($n8nConfig['ai_webhook_token'])) {
    error_log('N8N_AI_WEBHOOK_TOKEN não configurado no .env');
}
