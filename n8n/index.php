<?php
http_response_code(403);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'sucesso' => false,
    'mensagem' => 'Acesso direto à pasta n8n não permitido.'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
