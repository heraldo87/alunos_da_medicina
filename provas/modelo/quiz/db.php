<?php
declare(strict_types=1);

function quiz_pdo(array $provaConfig): PDO
{
    $databaseFile = dirname(__DIR__, 3) . '/config/database.php';

    if (is_file($databaseFile)) {
        $loaded = require $databaseFile;

        if ($loaded instanceof PDO) {
            return $loaded;
        }

        foreach (['pdo', 'db', 'conn', 'connection'] as $varName) {
            if (isset($$varName) && $$varName instanceof PDO) {
                return $$varName;
            }

            if (isset($GLOBALS[$varName]) && $GLOBALS[$varName] instanceof PDO) {
                return $GLOBALS[$varName];
            }
        }
    }

    $host = getenv('DB_HOST') ?: (string) ($provaConfig['db_host'] ?? '127.0.0.1');
    $port = getenv('DB_PORT') ?: (string) ($provaConfig['db_port'] ?? '3306');
    $name = getenv('DB_DATABASE') ?: (string) ($provaConfig['db_database'] ?? '');
    $user = getenv('DB_USERNAME') ?: (string) ($provaConfig['db_username'] ?? '');
    $pass = getenv('DB_PASSWORD') ?: (string) ($provaConfig['db_password'] ?? '');

    if ($name === '' || $user === '') {
        throw new RuntimeException('Configuração do banco não encontrada. Verifique config/database.php ou as variáveis DB_DATABASE e DB_USERNAME.');
    }

    return new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

function quiz_count_questions(PDO $pdo, string $quizCodigo, string $grupoCodigo): array
{
    $sql = "
        SELECT
            q.idioma,
            q.dificuldade,
            COUNT(*) AS total
        FROM quiz_questoes q
        WHERE q.quiz_codigo = :quiz_codigo
          AND q.grupo_codigo = :grupo_codigo
          AND q.status = 'ativa'
          AND (
                SELECT COUNT(*)
                FROM quiz_alternativas a
                WHERE a.questao_id = q.id
          ) = 4
          AND (
                SELECT COALESCE(SUM(a.correta), 0)
                FROM quiz_alternativas a
                WHERE a.questao_id = q.id
          ) = 1
        GROUP BY q.idioma, q.dificuldade
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':quiz_codigo' => $quizCodigo,
        ':grupo_codigo' => $grupoCodigo,
    ]);

    $result = [
        'total' => 0,
        'PT' => [
            'moderada' => 0,
            'dificil' => 0,
            'total' => 0,
        ],
        'ES' => [
            'moderada' => 0,
            'dificil' => 0,
            'total' => 0,
        ],
    ];

    foreach ($stmt->fetchAll() as $row) {
        $idioma = (string) $row['idioma'];
        $dificuldade = (string) $row['dificuldade'];
        $total = (int) $row['total'];

        if (!isset($result[$idioma][$dificuldade])) {
            continue;
        }

        $result[$idioma][$dificuldade] = $total;
        $result[$idioma]['total'] += $total;
        $result['total'] += $total;
    }

    return $result;
}
