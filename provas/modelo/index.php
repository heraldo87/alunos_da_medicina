<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

$conteudos = prova_scan_files('conteudo', $provaConfig);
$resumos = prova_scan_files('resumo', $provaConfig);
$podcasts = prova_scan_files('podcast', $provaConfig);

$aulasUrl = trim((string) ($provaConfig['aulas_url'] ?? 'aulas/index.php'));
if ($aulasUrl === '') {
    $aulasUrl = 'aulas/index.php';
}
$aulasHref = preg_match('/^https?:\/\//i', $aulasUrl) ? $aulasUrl : prova_url($aulasUrl);

$quizUrl = trim((string) ($provaConfig['quiz_url'] ?? 'quiz.php'));
if ($quizUrl === '') {
    $quizUrl = 'quiz.php';
}
$quizHref = preg_match('/^https?:\/\//i', $quizUrl) ? $quizUrl : prova_url($quizUrl);

$whatsappUrl = 'https://wa.me/' . preg_replace('/\D+/', '', (string) $provaConfig['whatsapp_numero'])
    . '?text=' . rawurlencode((string) $provaConfig['whatsapp_mensagem']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($provaConfig['titulo']) ?> | Alunos da Medicina</title>
    <meta name="description" content="<?= e($provaConfig['subtitulo']) ?>">
    <link rel="stylesheet" href="/assets/css/branding.css">
    <link rel="stylesheet" href="<?= e(prova_url('assets/prova.css')) ?>?v=3">
</head>
<body data-track-url="<?= e(prova_url('track.php')) ?>">
    <header class="header prova-header" id="topo">
        <div class="container nav">
            <a class="brand" href="/" aria-label="Alunos da Medicina">
                <span class="brand-logo">ADM</span>
                <span>
                    <span class="brand-name">Alunos da Medicina</span>
                    <span class="brand-slogan">Revisão de prova</span>
                </span>
            </a>

            <nav class="prova-nav" aria-label="Navegação da revisão">
                <a href="#conteudos">Conteúdos</a>
                <a href="#resumos">Resumos</a>
                <a href="#podcasts">Podcasts</a>
                <a href="#aulas">Aulas</a>
                <a href="#quiz">Quiz</a>
                <a href="#contato">Contato</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="prova-hero">
            <div class="container prova-hero-grid">
                <div>
                    <span class="badge">Matéria atual: <?= e($provaConfig['materia']) ?></span>
                    <h1 class="heading-xl prova-title"><?= e($provaConfig['titulo']) ?></h1>
                    <p class="text-lead"><?= e($provaConfig['subtitulo']) ?></p>

                    <div class="prova-meta">
                        <span><i class="prova-meta-mark" aria-hidden="true"></i><?= e($provaConfig['materia']) ?></span>
                        <span><i class="prova-meta-mark" aria-hidden="true"></i><?= e($provaConfig['data_prova']) ?></span>
                        <span><i class="prova-meta-mark" aria-hidden="true"></i><?= e($provaConfig['professor']) ?></span>
                    </div>

                    <div class="hero-actions">
                        <a class="btn btn-primary btn-lg" href="#conteudos" data-track="clique_hero_conteudos">Começar revisão</a>
                        <a class="btn btn-outline btn-lg" href="<?= e($aulasHref) ?>" data-track="clique_hero_aulas">Acessar aulas</a>
                    </div>
                </div>

                <aside class="card card-lg prova-hero-card" aria-label="Resumo da página">
                    <h2 class="heading-md">O que tem aqui?</h2>
                    <div class="prova-stats">
                        <div><strong><?= count($conteudos) ?></strong><span>conteúdos</span></div>
                        <div><strong><?= count($resumos) ?></strong><span>resumos</span></div>
                        <div><strong><?= count($podcasts) ?></strong><span>podcasts</span></div>
                        <div><strong>Link</strong><span>aulas guiadas</span></div>
                    </div>
                    <p class="text-muted">Use esta página como uma trilha rápida: leia o conteúdo, revise o resumo, escute o podcast, acesse as aulas guiadas e depois entre no quiz.</p>
                </aside>
            </div>
        </section>

        <section id="conteudos" class="section">
            <div class="container">
                <div class="section-header">
                    <span class="badge">1ª etapa</span>
                    <h2 class="heading-lg">Conteúdos da prova</h2>
                    <p class="text-lead">Arquivos lidos automaticamente da pasta <strong>conteudo</strong>.</p>
                </div>

                <?php if ($conteudos): ?>
                    <div class="prova-file-grid">
                        <?php foreach ($conteudos as $file): ?>
                            <article class="card card-hover prova-file-card">
                                <div class="prova-file-icon" aria-hidden="true"><span class="prova-line-icon prova-line-icon-file"></span></div>
                                <div>
                                    <h3 class="heading-sm"><?= e($file['title']) ?></h3>
                                    <p class="text-muted"><?= e($file['extension']) ?> • <?= e($file['size']) ?> • atualizado em <?= e($file['modified_at']) ?></p>
                                </div>
                                <div class="prova-file-actions">
                                    <a class="btn btn-primary btn-sm" href="<?= e($file['open_url']) ?>" target="_blank" rel="noopener">Abrir</a>
                                    <a class="btn btn-outline btn-sm" href="<?= e($file['download_url']) ?>">Baixar</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card prova-empty">Coloque arquivos em <strong>/provas/modelo/conteudo/</strong> para aparecerem aqui.</div>
                <?php endif; ?>
            </div>
        </section>

        <section id="resumos" class="section prova-soft-section">
            <div class="container">
                <div class="section-header">
                    <span class="badge">2ª etapa</span>
                    <h2 class="heading-lg">Resumos para revisão rápida</h2>
                    <p class="text-lead">Arquivos lidos automaticamente da pasta <strong>resumo</strong>.</p>
                </div>

                <?php if ($resumos): ?>
                    <div class="prova-file-grid">
                        <?php foreach ($resumos as $file): ?>
                            <article class="card card-hover prova-file-card">
                                <div class="prova-file-icon" aria-hidden="true"><span class="prova-line-icon prova-line-icon-summary"></span></div>
                                <div>
                                    <h3 class="heading-sm"><?= e($file['title']) ?></h3>
                                    <p class="text-muted"><?= e($file['extension']) ?> • <?= e($file['size']) ?> • atualizado em <?= e($file['modified_at']) ?></p>
                                </div>
                                <div class="prova-file-actions">
                                    <a class="btn btn-primary btn-sm" href="<?= e($file['open_url']) ?>" target="_blank" rel="noopener">Abrir</a>
                                    <a class="btn btn-outline btn-sm" href="<?= e($file['download_url']) ?>">Baixar</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card prova-empty">Coloque arquivos em <strong>/provas/modelo/resumo/</strong> para aparecerem aqui.</div>
                <?php endif; ?>
            </div>
        </section>

        <section id="podcasts" class="section">
            <div class="container">
                <div class="section-header">
                    <span class="badge">3ª etapa</span>
                    <h2 class="heading-lg">Podcasts da matéria</h2>
                    <p class="text-lead">Áudios lidos automaticamente da pasta <strong>podcast</strong>, com player e opção de download.</p>
                </div>

                <?php if ($podcasts): ?>
                    <div class="prova-podcast-grid">
                        <?php foreach ($podcasts as $file): ?>
                            <article class="card card-hover prova-podcast-card">
                                <div class="prova-file-icon" aria-hidden="true"><span class="prova-line-icon prova-line-icon-audio"></span></div>
                                <div>
                                    <h3 class="heading-sm"><?= e($file['title']) ?></h3>
                                    <p class="text-muted"><?= e($file['extension']) ?> • <?= e($file['size']) ?></p>
                                </div>
                                <audio controls preload="none" data-audio-file="<?= e($file['filename']) ?>">
                                    <source src="<?= e($file['play_url']) ?>" type="audio/<?= strtolower(e($file['extension'])) ?>">
                                    Seu navegador não conseguiu tocar este áudio.
                                </audio>
                                <a class="btn btn-outline btn-sm" href="<?= e($file['download_url']) ?>">Baixar podcast</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card prova-empty">Coloque arquivos de áudio em <strong>/provas/modelo/podcast/</strong> para aparecerem aqui.</div>
                <?php endif; ?>
            </div>
        </section>

        <section id="aulas" class="section prova-soft-section">
            <div class="container">
                <div class="card card-lg prova-action-card prova-aulas-card">
                    <div>
                        <span class="badge">4ª etapa</span>
                        <h2 class="heading-lg">Aulas interativas</h2>
                        <p class="text-lead">As aulas abrem em uma página própria, em ordem cronológica. Cada aula pode ter áudio e, ao final, um chat contextualizado com o conteúdo lecionado.</p>
                    </div>
                    <a class="btn btn-primary btn-lg" href="<?= e($aulasHref) ?>" data-track="clique_aulas_link">Acessar aulas</a>
                </div>
            </div>
        </section>

        <section id="quiz" class="section">
            <div class="container">
                <div class="card card-lg prova-action-card">
                    <div>
                        <span class="badge">5ª etapa</span>
                        <h2 class="heading-lg">Quiz da prova</h2>
                        <p class="text-lead">O quiz será aberto em uma página própria. Assim a página principal continua limpa e os alunos entram no simulado quando estiverem prontos.</p>
                    </div>
                    <a class="btn btn-primary btn-lg" href="<?= e($quizHref) ?>" data-track="clique_quiz_link">Acessar quiz</a>
                </div>
            </div>
        </section>

        <section id="contato" class="section prova-contact-section">
            <div class="container">
                <div class="card card-lg prova-contact-card">
                    <div>
                        <span class="badge">Quer estudar com mais direção?</span>
                        <h2 class="heading-lg">Entre em contato pelo WhatsApp</h2>
                        <p class="text-lead">Use esta área para converter alunos interessados e medir quantos vieram pela página da prova.</p>
                    </div>
                    <a class="btn btn-secondary btn-lg" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener" data-track="clique_whatsapp">Falar no WhatsApp</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="prova-footer">
        <div class="container flex-between">
            <p>© <?= date('Y') ?> Alunos da Medicina</p>
            <a href="#topo">Voltar ao topo</a>
        </div>
    </footer>

    <script>
        const trackUrl = document.body.dataset.trackUrl;

        function trackEvent(eventType, area = null, fileName = null, extra = {}) {
            if (!trackUrl || !navigator.sendBeacon && !window.fetch) return;

            const payload = JSON.stringify({ event_type: eventType, area, file_name: fileName, extra });

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

        trackEvent('visitou_pagina', 'home');

        document.querySelectorAll('[data-track]').forEach((item) => {
            item.addEventListener('click', () => {
                trackEvent(item.dataset.track, item.getAttribute('href') || 'interface');
            });
        });

        document.querySelectorAll('audio[data-audio-file]').forEach((audio) => {
            let tracked = false;
            audio.addEventListener('play', () => {
                if (tracked) return;
                tracked = true;
                trackEvent('tocou_podcast', 'podcast', audio.dataset.audioFile);
            });
        });
    </script>
</body>
</html>
