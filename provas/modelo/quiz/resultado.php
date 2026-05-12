<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/helpers.php';
require __DIR__ . '/db.php';

$token = preg_replace('/[^a-f0-9]/', '', (string) ($_GET['t'] ?? ''));

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

    $pdo->prepare("
        UPDATE quiz_tentativas
        SET finished_at = COALESCE(finished_at, CURRENT_TIMESTAMP)
        WHERE id = :id
    ")->execute([':id' => $tentativaId]);

    $questoesStmt = $pdo->prepare("
        SELECT
            tq.ordem,
            q.id,
            q.enunciado,
            q.explicacao_geral,
            r.alternativa_id AS escolhida_id,
            r.correta AS acertou
        FROM quiz_tentativa_questoes tq
        INNER JOIN quiz_questoes q ON q.id = tq.questao_id
        LEFT JOIN quiz_respostas r ON r.questao_id = q.id AND r.tentativa_id = tq.tentativa_id
        WHERE tq.tentativa_id = :tentativa_id
        ORDER BY tq.ordem ASC
    ");
    $questoesStmt->execute([':tentativa_id' => $tentativaId]);
    $questoes = $questoesStmt->fetchAll();

    $ids = array_map(static fn(array $q): int => (int) $q['id'], $questoes);
    $alternativasPorQuestao = [];

    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $altStmt = $pdo->prepare("
            SELECT
                id,
                questao_id,
                texto,
                correta,
                explicacao
            FROM quiz_alternativas
            WHERE questao_id IN ({$placeholders})
            ORDER BY questao_id ASC, ordem ASC
        ");
        $altStmt->execute($ids);

        foreach ($altStmt->fetchAll() as $alternativa) {
            $alternativasPorQuestao[(int) $alternativa['questao_id']][] = $alternativa;
        }
    }

    $total = count($questoes);
    $acertos = 0;

    foreach ($questoes as $q) {
        if ((int) ($q['acertou'] ?? 0) === 1) {
            $acertos++;
        }
    }

    $percentual = $total > 0 ? (int) round(($acertos / $total) * 100) : 0;

    prova_track_event($provaConfig, 'finalizou_quiz', 'quiz', null, [
        'quiz_codigo' => (string) $tentativa['quiz_codigo'],
        'grupo_codigo' => (string) $tentativa['grupo_codigo'],
        'idioma' => (string) $tentativa['idioma'],
        'dificuldade' => (string) $tentativa['dificuldade'],
        'total' => $total,
        'acertos' => $acertos,
        'percentual' => $percentual,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Erro ao carregar resultado</h1>';
    echo '<p>' . e($e->getMessage()) . '</p>';
    echo '<p><a href="index.php">Voltar para configuração</a></p>';
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado do Quiz | Alunos da Medicina</title>

    <style>
        :root {
            --adm-primary: #0f3d5c;
            --adm-secondary: #18a999;
            --adm-bg: #f6f8fb;
            --adm-surface: #ffffff;
            --adm-soft: #f1f5f9;
            --adm-text: #102033;
            --adm-muted: #64748b;
            --adm-border: #dbe4ee;
            --adm-radius: 24px;
            --adm-success: #067647;
            --adm-success-soft: #ecfdf3;
            --adm-danger: #b42318;
            --adm-danger-soft: #fff1f0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--adm-bg);
            color: var(--adm-text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; text-decoration: none; }

        .container {
            width: min(920px, calc(100% - 36px));
            margin: 0 auto;
        }

        .hero {
            padding: 54px 0 26px;
            background: radial-gradient(circle at top right, rgba(24,169,153,.15), transparent 34%), #fff;
            border-bottom: 1px solid var(--adm-border);
        }

        .badge {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(24,169,153,.12);
            color: var(--adm-primary);
            font-weight: 900;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            color: var(--adm-primary);
            font-size: clamp(2rem, 5vw, 3.6rem);
            line-height: 1.05;
        }

        .lead {
            color: var(--adm-muted);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .result {
            margin-top: 22px;
            padding: 24px;
            border-radius: var(--adm-radius);
            background: var(--adm-primary);
            color: #fff;
        }

        .content {
            padding: 28px 0 70px;
        }

        .card {
            background: var(--adm-surface);
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            box-shadow: 0 18px 44px rgba(15,23,42,.08);
            padding: clamp(22px, 4vw, 34px);
            margin-bottom: 20px;
        }

        .question-title {
            margin: 0 0 18px;
            color: var(--adm-primary);
            font-size: 1.35rem;
            line-height: 1.35;
        }

        .option {
            padding: 16px;
            border: 1px solid var(--adm-border);
            border-radius: 16px;
            background: #fff;
            margin-top: 10px;
        }

        .option.correct {
            border-color: var(--adm-success);
            background: var(--adm-success-soft);
        }

        .option.wrong {
            border-color: var(--adm-danger);
            background: var(--adm-danger-soft);
        }

        .option strong {
            display: block;
            margin-bottom: 6px;
        }

        .option p {
            margin: 6px 0 0;
            color: var(--adm-muted);
            line-height: 1.55;
        }

        .general {
            margin-top: 16px;
            padding: 16px;
            border-radius: 16px;
            background: rgba(24,169,153,.08);
            color: var(--adm-muted);
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 14px 22px;
            border-radius: 15px;
            border: 1px solid transparent;
            font-weight: 900;
        }

        .btn-primary {
            background: var(--adm-primary);
            color: #fff;
        }

        .btn-outline {
            background: #fff;
            color: var(--adm-primary);
            border-color: var(--adm-border);
        }
    </style>
</head>
<body>
    <section class="hero">
        <div class="container">
            <span class="badge">Resultado do quiz</span>

            <h1>Você acertou <?= e((string) $acertos) ?> de <?= e((string) $total) ?></h1>

            <p class="lead">
                Aproveitamento: <?= e((string) $percentual) ?>%.
                Confira abaixo as explicações das alternativas.
            </p>

            <div class="result">
                <strong>
                    <?= $percentual >= 70 ? 'Bom desempenho!' : 'Continue revisando.' ?>
                </strong>
                <br>
                O objetivo do quiz é mostrar onde estão suas dúvidas antes da prova.
            </div>

            <div class="actions">
                <a class="btn btn-primary" href="index.php">Fazer outro quiz</a>
                <a class="btn btn-outline" href="<?= e(prova_url('index.php')) ?>">Voltar para revisão</a>
            </div>
        </div>
    </section>

    <main class="content">
        <div class="container">
            <?php foreach ($questoes as $questao): ?>
                <?php
                    $questaoId = (int) $questao['id'];
                    $escolhidaId = (int) ($questao['escolhida_id'] ?? 0);
                    $alternativas = $alternativasPorQuestao[$questaoId] ?? [];
                ?>

                <article class="card">
                    <h2 class="question-title">
                        Questão <?= e((string) $questao['ordem']) ?> — <?= e((string) $questao['enunciado']) ?>
                    </h2>

                    <?php foreach ($alternativas as $alternativa): ?>
                        <?php
                            $isCorrect = (int) $alternativa['correta'] === 1;
                            $isChosen = (int) $alternativa['id'] === $escolhidaId;
                            $class = $isCorrect ? 'correct' : ($isChosen ? 'wrong' : '');
                        ?>

                        <div class="option <?= e($class) ?>">
                            <strong>
                                <?php if ($isCorrect): ?>
                                    Resposta correta
                                <?php elseif ($isChosen): ?>
                                    Sua resposta
                                <?php else: ?>
                                    Alternativa
                                <?php endif; ?>
                            </strong>

                            <?= e((string) $alternativa['texto']) ?>

                            <p><?= e((string) $alternativa['explicacao']) ?></p>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!empty($questao['explicacao_geral'])): ?>
                        <div class="general">
                            <strong>Explicação geral:</strong>
                            <?= e((string) $questao['explicacao_geral']) ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
