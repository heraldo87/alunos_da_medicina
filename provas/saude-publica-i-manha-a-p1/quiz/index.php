<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/helpers.php';
require __DIR__ . '/db.php';

$quizCodigo = (string) ($provaConfig['quiz_codigo'] ?? 'QUIZ-EMBRIO-MODELO-P1');
$grupoCodigo = (string) ($provaConfig['quiz_grupo_codigo'] ?? 'EMBRIO-MODELO');
$idiomaPadrao = (string) ($provaConfig['quiz_idioma_padrao'] ?? 'PT');
$dificuldadePadrao = (string) ($provaConfig['quiz_dificuldade_padrao'] ?? 'moderada');

if (!in_array($idiomaPadrao, ['PT', 'ES'], true)) {
    $idiomaPadrao = 'PT';
}

if (!in_array($dificuldadePadrao, ['moderada', 'dificil'], true)) {
    $dificuldadePadrao = 'moderada';
}

$erroBanco = null;

$contagem = [
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

try {
    $pdo = quiz_pdo($provaConfig);
    $contagem = quiz_count_questions($pdo, $quizCodigo, $grupoCodigo);
} catch (Throwable $e) {
    $erroBanco = $e->getMessage();
}

$totalQuestoes = (int) $contagem['total'];
$totalPt = (int) $contagem['PT']['total'];
$totalEs = (int) $contagem['ES']['total'];

$totalModeradasPt = (int) $contagem['PT']['moderada'];
$totalDificeisPt = (int) $contagem['PT']['dificil'];

$totalModeradasEs = (int) $contagem['ES']['moderada'];
$totalDificeisEs = (int) $contagem['ES']['dificil'];

$disponivelInicial = (int) ($contagem[$idiomaPadrao][$dificuldadePadrao] ?? 0);
$limiteOpcoes = max(1, min(50, $disponivelInicial));

prova_track_event($provaConfig, 'visitou_quiz_inicio', 'quiz', null, [
    'quiz_codigo' => $quizCodigo,
    'grupo_codigo' => $grupoCodigo,
    'total_questoes' => $totalQuestoes,
    'total_pt' => $totalPt,
    'total_es' => $totalEs,
]);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar Quiz | <?= e($provaConfig['materia']) ?> | Alunos da Medicina</title>
    <meta name="description" content="Escolha a quantidade de questões, idioma e dificuldade antes de começar o quiz.">

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
            --adm-danger: #b42318;
            --adm-danger-soft: #fff1f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top right, rgba(24,169,153,.15), transparent 34%),
                var(--adm-bg);
            color: var(--adm-text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            width: min(980px, calc(100% - 36px));
            margin: 0 auto;
        }

        .quiz-topbar {
            background: rgba(255,255,255,.94);
            border-bottom: 1px solid var(--adm-border);
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(14px);
        }

        .quiz-topbar-inner {
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .quiz-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 900;
            color: var(--adm-primary);
        }

        .quiz-logo {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: var(--adm-primary);
            color: #fff;
            font-size: .72rem;
            letter-spacing: .04em;
        }

        .quiz-back {
            color: var(--adm-muted);
            font-weight: 800;
        }

        .quiz-hero {
            padding: 56px 0 28px;
        }

        .quiz-badge {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(24,169,153,.12);
            color: var(--adm-primary);
            font-size: .82rem;
            font-weight: 900;
            margin-bottom: 18px;
        }

        .quiz-title {
            margin: 0;
            color: var(--adm-primary);
            font-size: clamp(2.1rem, 5vw, 4rem);
            line-height: 1.04;
            letter-spacing: -.04em;
        }

        .quiz-desc {
            margin: 18px 0 0;
            color: var(--adm-muted);
            font-size: 1.1rem;
            line-height: 1.65;
            max-width: 760px;
        }

        .quiz-grid {
            display: grid;
            grid-template-columns: .9fr 1.1fr;
            gap: 24px;
            align-items: start;
            padding: 22px 0 70px;
        }

        .quiz-card {
            background: var(--adm-surface);
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            box-shadow: 0 18px 44px rgba(15,23,42,.08);
            padding: clamp(22px, 4vw, 34px);
        }

        .quiz-card h2 {
            margin: 0 0 14px;
            color: var(--adm-primary);
            font-size: clamp(1.35rem, 3vw, 2rem);
            letter-spacing: -.03em;
        }

        .quiz-stats {
            display: grid;
            gap: 12px;
        }

        .quiz-stat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px;
            border-radius: 18px;
            background: var(--adm-soft);
        }

        .quiz-stat span {
            color: var(--adm-muted);
            font-weight: 800;
        }

        .quiz-stat strong {
            color: var(--adm-primary);
            font-size: 1.35rem;
        }

        .quiz-form {
            display: grid;
            gap: 20px;
        }

        .quiz-field {
            display: grid;
            gap: 9px;
        }

        .quiz-field label {
            color: var(--adm-primary);
            font-weight: 900;
        }

        .quiz-help {
            margin: 0;
            color: var(--adm-muted);
            font-size: .94rem;
            line-height: 1.5;
        }

        .quiz-select {
            width: 100%;
            min-height: 52px;
            padding: 12px 14px;
            border: 1px solid var(--adm-border);
            border-radius: 16px;
            background: #fff;
            color: var(--adm-text);
            font: inherit;
            font-weight: 750;
            outline: none;
        }

        .quiz-select:focus {
            border-color: var(--adm-secondary);
            box-shadow: 0 0 0 4px rgba(24,169,153,.12);
        }

        .quiz-choice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .quiz-radio {
            position: relative;
        }

        .quiz-radio input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .quiz-radio span {
            display: grid;
            gap: 5px;
            min-height: 92px;
            padding: 16px;
            border: 1px solid var(--adm-border);
            border-radius: 18px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .quiz-radio strong {
            color: var(--adm-primary);
        }

        .quiz-radio small {
            color: var(--adm-muted);
            line-height: 1.45;
        }

        .quiz-radio input:checked + span {
            border-color: var(--adm-secondary);
            background: rgba(24,169,153,.08);
            box-shadow: 0 0 0 4px rgba(24,169,153,.10);
        }

        .quiz-alert {
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(24,169,153,.08);
            color: var(--adm-muted);
            line-height: 1.6;
        }

        .quiz-alert strong {
            color: var(--adm-primary);
        }

        .quiz-alert-error {
            background: var(--adm-danger-soft);
            color: var(--adm-danger);
        }

        .quiz-alert-error strong {
            color: var(--adm-danger);
        }

        .quiz-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
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

        .btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        @media (max-width: 820px) {
            .quiz-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .quiz-topbar-inner,
            .quiz-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .quiz-choice-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body
    data-track-url="<?= e(prova_url('track.php')) ?>"
    data-counts='<?= e(json_encode($contagem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'
>
    <header class="quiz-topbar">
        <div class="container quiz-topbar-inner">
            <a class="quiz-brand" href="<?= e(prova_url('index.php')) ?>">
                <span class="quiz-logo">ADM</span>
                <span>Alunos da Medicina</span>
            </a>

            <a class="quiz-back" href="<?= e(prova_url('index.php')) ?>">Voltar para revisão</a>
        </div>
    </header>

    <main>
        <section class="quiz-hero">
            <div class="container">
                <span class="quiz-badge">Quiz de <?= e($provaConfig['materia']) ?></span>

                <h1 class="quiz-title">Configure seu quiz</h1>

                <p class="quiz-desc">
                    Escolha o idioma, a dificuldade e a quantidade de questões.
                    O sistema vai usar o banco vinculado ao código <strong><?= e($quizCodigo) ?></strong>.
                </p>
            </div>
        </section>

        <section class="container quiz-grid">
            <aside class="quiz-card">
                <h2>Banco de questões</h2>

                <div class="quiz-stats">
                    <div class="quiz-stat">
                        <span>Total válido</span>
                        <strong><?= e((string) $totalQuestoes) ?></strong>
                    </div>

                    <div class="quiz-stat">
                        <span>Português</span>
                        <strong><?= e((string) $totalPt) ?></strong>
                    </div>

                    <div class="quiz-stat">
                        <span>Espanhol</span>
                        <strong><?= e((string) $totalEs) ?></strong>
                    </div>

                    <div class="quiz-stat">
                        <span>PT moderadas</span>
                        <strong><?= e((string) $totalModeradasPt) ?></strong>
                    </div>

                    <div class="quiz-stat">
                        <span>PT difíceis</span>
                        <strong><?= e((string) $totalDificeisPt) ?></strong>
                    </div>

                    <div class="quiz-stat">
                        <span>ES moderadas</span>
                        <strong><?= e((string) $totalModeradasEs) ?></strong>
                    </div>

                    <div class="quiz-stat">
                        <span>ES difíceis</span>
                        <strong><?= e((string) $totalDificeisEs) ?></strong>
                    </div>
                </div>

                <?php if ($erroBanco): ?>
                    <div class="quiz-alert quiz-alert-error" style="margin-top: 16px;">
                        <strong>Erro ao ler o banco:</strong>
                        <?= e($erroBanco) ?>
                    </div>
                <?php endif; ?>
            </aside>

            <article class="quiz-card">
                <h2>Preferências do quiz</h2>

                <form class="quiz-form" action="iniciar.php" method="get" id="quizStartForm">
                    <input type="hidden" name="quiz_codigo" value="<?= e($quizCodigo) ?>">
                    <input type="hidden" name="grupo_codigo" value="<?= e($grupoCodigo) ?>">

                    <div class="quiz-field">
                        <label>Idioma</label>

                        <div class="quiz-choice-grid">
                            <label class="quiz-radio">
                                <input type="radio" name="idioma" value="PT" <?= $idiomaPadrao === 'PT' ? 'checked' : '' ?>>
                                <span>
                                    <strong>Português</strong>
                                    <small>Questões e explicações em português.</small>
                                </span>
                            </label>

                            <label class="quiz-radio">
                                <input type="radio" name="idioma" value="ES" <?= $idiomaPadrao === 'ES' ? 'checked' : '' ?>>
                                <span>
                                    <strong>Espanhol</strong>
                                    <small>Questões e explicações em espanhol.</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="quiz-field">
                        <label>Dificuldade</label>

                        <div class="quiz-choice-grid">
                            <label class="quiz-radio">
                                <input type="radio" name="dificuldade" value="moderada" <?= $dificuldadePadrao === 'moderada' ? 'checked' : '' ?>>
                                <span>
                                    <strong>Moderada</strong>
                                    <small>Questões para revisar conceitos centrais da matéria.</small>
                                </span>
                            </label>

                            <label class="quiz-radio">
                                <input type="radio" name="dificuldade" value="dificil" <?= $dificuldadePadrao === 'dificil' ? 'checked' : '' ?>>
                                <span>
                                    <strong>Difícil</strong>
                                    <small>Questões mais exigentes, com maior integração entre conceitos.</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="quiz-field">
                        <label for="quantidade">Quantidade de questões</label>

                        <select class="quiz-select" id="quantidade" name="quantidade" required>
                            <?php for ($i = 1; $i <= $limiteOpcoes; $i++): ?>
                                <option value="<?= e((string) $i) ?>">
                                    <?= e((string) $i) ?> questão<?= $i > 1 ? 'ões' : '' ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <p class="quiz-help" id="quantidadeHelp">
                            Escolha uma quantidade compatível com o banco de questões disponível.
                        </p>
                    </div>

                    <div class="quiz-alert">
                        <strong>Próxima etapa:</strong>
                        ao clicar em começar, vamos enviar idioma, dificuldade e quantidade para a página do quiz.
                    </div>

                    <div class="quiz-actions">
                        <a class="btn btn-outline" href="<?= e(prova_url('index.php')) ?>">
                            Cancelar
                        </a>

                        <button class="btn btn-primary" type="submit" id="startButton">
                            Começar quiz
                        </button>
                    </div>
                </form>
            </article>
        </section>
    </main>

    <script>
        const counts = JSON.parse(document.body.dataset.counts || '{"PT":{"moderada":0,"dificil":0},"ES":{"moderada":0,"dificil":0}}');
        const quantidadeSelect = document.getElementById('quantidade');
        const quantidadeHelp = document.getElementById('quantidadeHelp');
        const form = document.getElementById('quizStartForm');
        const startButton = document.getElementById('startButton');
        const trackUrl = document.body.dataset.trackUrl;

        function trackEvent(eventType, extra = {}) {
            if (!trackUrl || (!navigator.sendBeacon && !window.fetch)) return;

            const payload = JSON.stringify({
                event_type: eventType,
                area: 'quiz',
                file_name: null,
                extra
            });

            if (navigator.sendBeacon) {
                navigator.sendBeacon(trackUrl, new Blob([payload], { type: 'application/json' }));
                return;
            }

            fetch(trackUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: payload,
                keepalive: true
            }).catch(() => {});
        }

        function getSelectedIdioma() {
            const selected = document.querySelector('input[name="idioma"]:checked');
            return selected ? selected.value : 'PT';
        }

        function getSelectedDifficulty() {
            const selected = document.querySelector('input[name="dificuldade"]:checked');
            return selected ? selected.value : 'moderada';
        }

        function getAvailable(idioma, dificuldade) {
            return Number((counts[idioma] && counts[idioma][dificuldade]) ? counts[idioma][dificuldade] : 0);
        }

        function updateQuantityOptions() {
            const idioma = getSelectedIdioma();
            const dificuldade = getSelectedDifficulty();
            const available = getAvailable(idioma, dificuldade);
            const currentValue = Number(quantidadeSelect.value || 1);

            quantidadeSelect.innerHTML = '';

            const max = Math.max(1, available);

            for (let i = 1; i <= max; i++) {
                const option = document.createElement('option');
                option.value = String(i);
                option.textContent = i + (i === 1 ? ' questão' : ' questões');
                quantidadeSelect.appendChild(option);
            }

            quantidadeSelect.value = String(Math.min(currentValue, max));

            const idiomaLabel = idioma === 'ES' ? 'espanhol' : 'português';
            const dificuldadeLabel = dificuldade === 'dificil' ? 'difícil' : 'moderada';

            if (available > 0) {
                quantidadeHelp.textContent = `Existem ${available} questões disponíveis em ${idiomaLabel}, dificuldade ${dificuldadeLabel}.`;
                startButton.disabled = false;
                quantidadeSelect.disabled = false;
            } else {
                quantidadeHelp.textContent = `Ainda não existem questões cadastradas em ${idiomaLabel}, dificuldade ${dificuldadeLabel}.`;
                startButton.disabled = true;
                quantidadeSelect.disabled = true;
            }
        }

        document.querySelectorAll('input[name="idioma"], input[name="dificuldade"]').forEach((input) => {
            input.addEventListener('change', updateQuantityOptions);
        });

        form.addEventListener('submit', () => {
            trackEvent('comecou_configuracao_quiz', {
                quantidade: quantidadeSelect.value,
                dificuldade: getSelectedDifficulty(),
                idioma: getSelectedIdioma()
            });
        });

        updateQuantityOptions();
    </script>
</body>
</html>
