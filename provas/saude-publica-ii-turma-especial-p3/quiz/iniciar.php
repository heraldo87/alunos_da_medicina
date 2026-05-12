<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/helpers.php';
require __DIR__ . '/db.php';

function quiz_token(): string
{
    try {
        return bin2hex(random_bytes(24));
    } catch (Throwable) {
        return sha1(uniqid('', true));
    }
}

function quiz_visitor_hash_local(array $config): string
{
    $salt = (string) ($config['analytics_salt'] ?? 'adm');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return hash('sha256', date('Y-m-d') . '|' . $ip . '|' . $ua . '|' . $salt);
}

$quizCodigo = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($_GET['quiz_codigo'] ?? ($provaConfig['quiz_codigo'] ?? '')));
$grupoCodigo = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($_GET['grupo_codigo'] ?? ($provaConfig['quiz_grupo_codigo'] ?? '')));

$idioma = strtoupper((string) ($_GET['idioma'] ?? ($provaConfig['quiz_idioma_padrao'] ?? 'PT')));
$dificuldade = (string) ($_GET['dificuldade'] ?? ($provaConfig['quiz_dificuldade_padrao'] ?? 'moderada'));
$quantidade = (int) ($_GET['quantidade'] ?? ($provaConfig['quiz_quantidade_padrao'] ?? 5));

if (!in_array($idioma, ['PT', 'ES'], true)) {
    $idioma = 'PT';
}

if (!in_array($dificuldade, ['moderada', 'dificil'], true)) {
    $dificuldade = 'moderada';
}

$quantidade = max(1, min(50, $quantidade));

try {
    $pdo = quiz_pdo($provaConfig);

    $sql = "
        SELECT q.id
        FROM quiz_questoes q
        WHERE q.quiz_codigo = :quiz_codigo
          AND q.grupo_codigo = :grupo_codigo
          AND q.idioma = :idioma
          AND q.dificuldade = :dificuldade
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
        ORDER BY RAND()
        LIMIT {$quantidade}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':quiz_codigo' => $quizCodigo,
        ':grupo_codigo' => $grupoCodigo,
        ':idioma' => $idioma,
        ':dificuldade' => $dificuldade,
    ]);

    $questoes = $stmt->fetchAll();

    if (!$questoes) {
        throw new RuntimeException('Nenhuma questão encontrada para esta configuração.');
    }

    $token = quiz_token();
    $visitorHash = quiz_visitor_hash_local($provaConfig);
    $totalEntregue = count($questoes);

    $pdo->beginTransaction();

    $tentativaStmt = $pdo->prepare("
        INSERT INTO quiz_tentativas (
            public_token,
            quiz_codigo,
            grupo_codigo,
            quantidade,
            dificuldade,
            idioma,
            visitor_hash,
            total_questoes,
            total_acertos,
            total_erros
        ) VALUES (
            :public_token,
            :quiz_codigo,
            :grupo_codigo,
            :quantidade,
            :dificuldade,
            :idioma,
            :visitor_hash,
            :total_questoes,
            0,
            0
        )
    ");

    $tentativaStmt->execute([
        ':public_token' => $token,
        ':quiz_codigo' => $quizCodigo,
        ':grupo_codigo' => $grupoCodigo,
        ':quantidade' => $quantidade,
        ':dificuldade' => $dificuldade,
        ':idioma' => $idioma,
        ':visitor_hash' => $visitorHash,
        ':total_questoes' => $totalEntregue,
    ]);

    $tentativaId = (int) $pdo->lastInsertId();

    $ordem = 1;
    $itemStmt = $pdo->prepare("
        INSERT INTO quiz_tentativa_questoes (
            tentativa_id,
            questao_id,
            ordem
        ) VALUES (
            :tentativa_id,
            :questao_id,
            :ordem
        )
    ");

    foreach ($questoes as $questao) {
        $itemStmt->execute([
            ':tentativa_id' => $tentativaId,
            ':questao_id' => (int) $questao['id'],
            ':ordem' => $ordem,
        ]);

        $ordem++;
    }

    $pdo->commit();

    prova_track_event($provaConfig, 'iniciou_quiz', 'quiz', null, [
        'quiz_codigo' => $quizCodigo,
        'grupo_codigo' => $grupoCodigo,
        'idioma' => $idioma,
        'dificuldade' => $dificuldade,
        'quantidade_solicitada' => $quantidade,
        'quantidade_entregue' => $totalEntregue,
    ]);

    header('Location: pergunta.php?t=' . rawurlencode($token) . '&n=1');
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Erro ao iniciar quiz</h1>';
    echo '<p>' . e($e->getMessage()) . '</p>';
    echo '<p><a href="index.php">Voltar para configuração</a></p>';
}
