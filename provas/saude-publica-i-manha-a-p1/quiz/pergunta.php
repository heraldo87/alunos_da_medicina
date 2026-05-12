<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/helpers.php';
require __DIR__ . '/db.php';

$token = preg_replace('/[^a-f0-9]/', '', (string) ($_GET['t'] ?? ''));
$ordem = max(1, (int) ($_GET['n'] ?? 1));

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

    $totalStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM quiz_tentativa_questoes
        WHERE tentativa_id = :tentativa_id
    ");
    $totalStmt->execute([':tentativa_id' => $tentativaId]);
    $totalQuestoes = (int) $totalStmt->fetch()['total'];

    if ($ordem > $totalQuestoes) {
        header('Location: resultado.php?t=' . rawurlencode($token));
        exit;
    }

    $questaoStmt = $pdo->prepare("
        SELECT
            tq.ordem,
            q.id,
            q.codigo,
            q.enunciado,
            q.explicacao_geral,
            q.idioma,
            q.dificuldade
        FROM quiz_tentativa_questoes tq
        INNER JOIN quiz_questoes q ON q.id = tq.questao_id
        WHERE tq.tentativa_id = :tentativa_id
          AND tq.ordem = :ordem
        LIMIT 1
    ");

    $questaoStmt->execute([
        ':tentativa_id' => $tentativaId,
        ':ordem' => $ordem,
    ]);

    $questao = $questaoStmt->fetch();

    if (!$questao) {
        throw new RuntimeException('Questão não encontrada.');
    }

    $respondidaStmt = $pdo->prepare("
        SELECT id
        FROM quiz_respostas
        WHERE tentativa_id = :tentativa_id
          AND questao_id = :questao_id
        LIMIT 1
    ");

    $respondidaStmt->execute([
        ':tentativa_id' => $tentativaId,
        ':questao_id' => (int) $questao['id'],
    ]);

    if ($respondidaStmt->fetch()) {
        header('Location: pergunta.php?t=' . rawurlencode($token) . '&n=' . ($ordem + 1));
        exit;
    }

    $altStmt = $pdo->prepare("
        SELECT
            id,
            texto
        FROM quiz_alternativas
        WHERE questao_id = :questao_id
        ORDER BY SHA2(CONCAT(:shuffle_seed, '-', id), 256), id ASC
    ");

    $altStmt->execute([
        ':questao_id' => (int) $questao['id'],
        ':shuffle_seed' => $token . '-' . (string) $questao['id'],
    ]);
    $alternativas = $altStmt->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Erro ao carregar questão</h1>';
    echo '<p>' . e($e->getMessage()) . '</p>';
    echo '<p><a href="index.php">Voltar para configuração</a></p>';
    exit;
}

$percentual = $totalQuestoes > 0 ? (int) round(($ordem / $totalQuestoes) * 100) : 0;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Questão <?= e((string) $ordem) ?> | Quiz | Alunos da Medicina</title>

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
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: radial-gradient(circle at top right, rgba(24,169,153,.14), transparent 34%), var(--adm-bg);
            color: var(--adm-text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; text-decoration: none; }

        .container {
            width: min(860px, calc(100% - 36px));
            margin: 0 auto;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255,255,255,.94);
            border-bottom: 1px solid var(--adm-border);
            backdrop-filter: blur(14px);
        }

        .topbar-inner {
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 900;
            color: var(--adm-primary);
        }

        .logo {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: var(--adm-primary);
            color: #fff;
            font-size: .72rem;
        }

        .quiz-wrap {
            padding: 46px 0 70px;
        }

        .progress-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            color: var(--adm-muted);
            font-weight: 900;
        }

        .progress-track {
            height: 10px;
            border-radius: 999px;
            overflow: hidden;
            background: #e2e8f0;
            margin-bottom: 26px;
        }

        .progress-bar {
            height: 100%;
            width: <?= e((string) $percentual) ?>%;
            background: linear-gradient(90deg, var(--adm-primary), var(--adm-secondary));
        }

        .card {
            background: var(--adm-surface);
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            box-shadow: 0 18px 44px rgba(15,23,42,.08);
            padding: clamp(24px, 5vw, 38px);
        }

        .badge {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(24,169,153,.12);
            color: var(--adm-primary);
            font-size: .82rem;
            font-weight: 900;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            color: var(--adm-primary);
            font-size: clamp(1.55rem, 4vw, 2.4rem);
            line-height: 1.25;
            letter-spacing: -.03em;
        }

        .options {
            display: grid;
            gap: 12px;
            margin-top: 26px;
        }

        .option {
            display: block;
            padding: 18px;
            border: 1px solid var(--adm-border);
            border-radius: 18px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .option:hover {
            border-color: rgba(24,169,153,.55);
        }

        .option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .option span {
            display: block;
            color: var(--adm-text);
            line-height: 1.58;
            font-weight: 700;
        }

        .option:has(input:checked) {
            border-color: var(--adm-secondary);
            background: rgba(24,169,153,.08);
            box-shadow: 0 0 0 4px rgba(24,169,153,.10);
        }

        .actions {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 14px 22px;
            border-radius: 15px;
            border: 1px solid transparent;
            cursor: pointer;
            font: inherit;
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

        @media (max-width: 640px) {
            .topbar-inner,
            .actions {
                align-items: stretch;
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand" href="<?= e(prova_url('index.php')) ?>">
                <span class="logo">ADM</span>
                <span>Alunos da Medicina</span>
            </a>

            <a href="index.php" style="font-weight:800;color:var(--adm-muted);">Configurar quiz</a>
        </div>
    </header>

    <main class="quiz-wrap">
        <div class="container">
            <div class="progress-head">
                <span>Questão <?= e((string) $ordem) ?> de <?= e((string) $totalQuestoes) ?></span>
                <span><?= e((string) $percentual) ?>%</span>
            </div>

            <div class="progress-track">
                <div class="progress-bar"></div>
            </div>

            <form class="card" action="responder.php" method="post">
                <input type="hidden" name="t" value="<?= e($token) ?>">
                <input type="hidden" name="n" value="<?= e((string) $ordem) ?>">
                <input type="hidden" name="questao_id" value="<?= e((string) $questao['id']) ?>">
                <input type="hidden" name="inicio" value="<?= e((string) time()) ?>">

                <span class="badge">
                    <?= e($questao['dificuldade'] === 'dificil' ? 'Difícil' : 'Moderada') ?>
                    ·
                    <?= e($questao['idioma']) ?>
                </span>

                <h1><?= e((string) $questao['enunciado']) ?></h1>

                <div class="options">
                    <?php foreach ($alternativas as $alternativa): ?>
                        <label class="option">
                            <input
                                type="radio"
                                name="alternativa_id"
                                value="<?= e((string) $alternativa['id']) ?>"
                                required
                            >
                            <span><?= e((string) $alternativa['texto']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="actions">
                    <a class="btn btn-outline" href="index.php">Sair do quiz</a>

                    <button class="btn btn-primary" type="submit">
                        <?= $ordem >= $totalQuestoes ? 'Finalizar' : 'Responder e avançar' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
