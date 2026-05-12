<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/helpers.php';
require __DIR__ . '/db.php';

$token = preg_replace('/[^a-f0-9]/', '', (string) ($_POST['t'] ?? ''));
$ordem = max(1, (int) ($_POST['n'] ?? 1));
$questaoId = (int) ($_POST['questao_id'] ?? 0);
$alternativaId = (int) ($_POST['alternativa_id'] ?? 0);
$inicio = (int) ($_POST['inicio'] ?? time());
$tempo = max(0, time() - $inicio);

try {
    $pdo = quiz_pdo($provaConfig);

    $tentativaStmt = $pdo->prepare("
        SELECT *
        FROM quiz_tentativas
        WHERE public_token = :token
        LIMIT 1
    ");
    $tentativaStmt->execute([':token' => $token]);
    $tentativa = $tentativaStmt->fetch();

    if (!$tentativa) {
        throw new RuntimeException('Tentativa não encontrada.');
    }

    $tentativaId = (int) $tentativa['id'];

    $pertenceStmt = $pdo->prepare("
        SELECT id
        FROM quiz_tentativa_questoes
        WHERE tentativa_id = :tentativa_id
          AND questao_id = :questao_id
          AND ordem = :ordem
        LIMIT 1
    ");
    $pertenceStmt->execute([
        ':tentativa_id' => $tentativaId,
        ':questao_id' => $questaoId,
        ':ordem' => $ordem,
    ]);

    if (!$pertenceStmt->fetch()) {
        throw new RuntimeException('Questão inválida para esta tentativa.');
    }

    $altStmt = $pdo->prepare("
        SELECT correta
        FROM quiz_alternativas
        WHERE id = :alternativa_id
          AND questao_id = :questao_id
        LIMIT 1
    ");
    $altStmt->execute([
        ':alternativa_id' => $alternativaId,
        ':questao_id' => $questaoId,
    ]);

    $alternativa = $altStmt->fetch();

    if (!$alternativa) {
        throw new RuntimeException('Alternativa inválida.');
    }

    $correta = (int) $alternativa['correta'] === 1 ? 1 : 0;

    $respostaStmt = $pdo->prepare("
        INSERT INTO quiz_respostas (
            tentativa_id,
            questao_id,
            alternativa_id,
            correta,
            tempo_segundos
        ) VALUES (
            :tentativa_id,
            :questao_id,
            :alternativa_id,
            :correta,
            :tempo_segundos
        )
        ON DUPLICATE KEY UPDATE
            alternativa_id = VALUES(alternativa_id),
            correta = VALUES(correta),
            tempo_segundos = VALUES(tempo_segundos)
    ");

    $respostaStmt->execute([
        ':tentativa_id' => $tentativaId,
        ':questao_id' => $questaoId,
        ':alternativa_id' => $alternativaId,
        ':correta' => $correta,
        ':tempo_segundos' => $tempo,
    ]);

    $scoreStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS respondidas,
            COALESCE(SUM(correta), 0) AS acertos
        FROM quiz_respostas
        WHERE tentativa_id = :tentativa_id
    ");
    $scoreStmt->execute([':tentativa_id' => $tentativaId]);
    $score = $scoreStmt->fetch();

    $respondidas = (int) $score['respondidas'];
    $acertos = (int) $score['acertos'];
    $erros = max(0, $respondidas - $acertos);

    $updateStmt = $pdo->prepare("
        UPDATE quiz_tentativas
        SET total_acertos = :acertos,
            total_erros = :erros
        WHERE id = :tentativa_id
    ");
    $updateStmt->execute([
        ':acertos' => $acertos,
        ':erros' => $erros,
        ':tentativa_id' => $tentativaId,
    ]);

    $totalQuestoes = (int) $tentativa['total_questoes'];

    if ($ordem >= $totalQuestoes) {
        header('Location: resultado.php?t=' . rawurlencode($token));
        exit;
    }

    header('Location: pergunta.php?t=' . rawurlencode($token) . '&n=' . ($ordem + 1));
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Erro ao salvar resposta</h1>';
    echo '<p>' . e($e->getMessage()) . '</p>';
    echo '<p><a href="index.php">Voltar para configuração</a></p>';
}
