<?php
declare(strict_types=1);

$databaseConfig = [
    'host' => '181.215.135.63',
    'port' => '3306',
    'database' => 'alunosdamedicina',
    'username' => 'alunosdamedicina',
    'password' => 'iiYAsaKsADRJAfKP',
    'charset' => 'utf8mb4',
];

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $databaseConfig['host'],
    $databaseConfig['port'],
    $databaseConfig['database'],
    $databaseConfig['charset']
);

try {
    $pdo = new PDO(
        $dsn,
        $databaseConfig['username'],
        $databaseConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Erro de conexão com banco: ' . $e->getMessage());

    http_response_code(500);
    exit('Erro interno de conexão.');
}
