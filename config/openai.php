<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

load_env(dirname(__DIR__) . '/.env');

$openaiConfig = [
    'api_key' => env_value('OPENAI_API_KEY'),
    'realtime_model' => env_value('OPENAI_REALTIME_MODEL', 'gpt-realtime'),
    'realtime_voice' => env_value('OPENAI_REALTIME_VOICE', 'marin'),
    'realtime_transcribe_model' => env_value('OPENAI_REALTIME_TRANSCRIBE_MODEL', 'gpt-4o-mini-transcribe'),
    'timeout' => (int) env_value('OPENAI_REALTIME_TIMEOUT', 30),
];

if (empty($openaiConfig['api_key'])) {
    error_log('OPENAI_API_KEY não configurada no .env');
}
