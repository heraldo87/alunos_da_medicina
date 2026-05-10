<?php
$brand = [
    'name' => 'Alunos da Medicina',
    'short_name' => 'ADM',
    'slogan' => 'Estude melhor. Evolua com inteligência.',
    'description' => 'Uma plataforma inteligente para estudantes de medicina organizarem seus estudos, participarem de grupos por disciplina e aprenderem com apoio da inteligência artificial.',
    'colors' => [
        'primary' => '#0F3D5E',
        'secondary' => '#18A999',
        'background' => '#F8FAFC',
        'surface' => '#FFFFFF',
        'text' => '#334155',
        'muted' => '#64748B',
        'soft_blue' => '#E0F2FE',
        'border' => '#E2E8F0'
    ]
];

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($brand['name']); ?> | <?= e($brand['slogan']); ?></title>

    <meta name="description" content="<?= e($brand['description']); ?>">
<link rel="stylesheet" href="/assets/css/branding.css">
</head>
<body>

<header class="header">
    <div class="container nav">
        <a href="/" class="brand" aria-label="<?= e($brand['name']); ?>">
            <span class="brand-logo">
                <svg width="28" height="28" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                    <path d="M10 16C18 14 25 16 32 22C39 16 46 14 54 16V50C46 48 39 50 32 56C25 50 18 48 10 50V16Z" stroke="white" stroke-width="4" stroke-linejoin="round"/>
                    <path d="M32 22V56" stroke="white" stroke-width="4" stroke-linecap="round"/>
                    <path d="M32 28V42" stroke="white" stroke-width="4" stroke-linecap="round"/>
                    <path d="M25 35H39" stroke="white" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="17" cy="12" r="3" fill="white"/>
                    <circle cx="47" cy="12" r="3" fill="white"/>
                </svg>
            </span>
            <span><?= e($brand['name']); ?></span>
        </a>

        <nav class="nav-actions">
            <a href="/login.php" class="btn btn-outline">Entrar</a>
            <a href="/cadastro.php" class="btn btn-primary">Criar conta</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <div class="badge">Medicina + Organização + Inteligência Artificial</div>

                <h1>Sua jornada na medicina mais organizada e inteligente.</h1>

                <p>
                    Acesse grupos por disciplina, organize seus estudos, acompanhe sua evolução
                    e use inteligência artificial para aprender medicina com mais clareza.
                </p>

                <div class="hero-actions">
                    <a href="/cadastro.php" class="btn btn-primary">Começar agora</a>
                    <a href="#como-funciona" class="btn btn-outline">Ver como funciona</a>
                </div>
            </div>

            <div class="preview-card" aria-label="Prévia do dashboard">
                <div class="dashboard-top">
                    <div class="dashboard-title">Dashboard acadêmico</div>
                    <div class="status">MVP inicial</div>
                </div>

                <div class="stats">
                    <div class="stat">
                        <strong>6</strong>
                        <span>Disciplinas ativas</span>
                    </div>

                    <div class="stat">
                        <strong>4</strong>
                        <span>Grupos participando</span>
                    </div>

                    <div class="stat">
                        <strong>28</strong>
                        <span>Dúvidas com IA</span>
                    </div>

                    <div class="stat">
                        <strong>82%</strong>
                        <span>Rotina concluída</span>
                    </div>
                </div>

                <div class="ai-box">
                    <h3>Assistente de estudos</h3>
                    <p>
                        “Explique potencial de membrana de forma simples para um aluno do primeiro ano.”
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Uma plataforma feita para a rotina médica acadêmica</h2>
                <p>
                    O objetivo é reduzir a desorganização dos estudos e criar um ambiente
                    centralizado para alunos, disciplinas, grupos e ferramentas inteligentes.
                </p>
            </div>

            <div class="cards">
                <article class="card">
                    <div class="icon">📚</div>
                    <h3>Grupos por disciplina</h3>
                    <p>Ambientes organizados por matéria e semestre, como Anatomia 2026.01.</p>
                </article>

                <article class="card">
                    <div class="icon">🧠</div>
                    <h3>IA para estudos</h3>
                    <p>Explicações didáticas, revisão de conteúdos, resumos e apoio para dúvidas.</p>
                </article>

                <article class="card">
                    <div class="icon">📊</div>
                    <h3>Evolução acadêmica</h3>
                    <p>Acompanhamento de acessos, participação, atividades e rotina de estudos.</p>
                </article>

                <article class="card">
                    <div class="icon">👤</div>
                    <h3>Perfil do aluno</h3>
                    <p>Dados pessoais, foto, configurações de conta e histórico de participação.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section" id="como-funciona">
        <div class="container">
            <div class="section-header">
                <h2>Como funciona</h2>
                <p>Fluxo inicial pensado para um MVP simples, rápido e funcional.</p>
            </div>

            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Crie sua conta</h3>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Acesse o dashboard</h3>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Entre nos grupos</h3>
                </div>

                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Estude com IA</h3>
                </div>

                <div class="step">
                    <div class="step-number">5</div>
                    <h3>Acompanhe sua evolução</h3>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="cta">
                <div>
                    <h2><?= e($brand['slogan']); ?></h2>
                    <p><?= e($brand['description']); ?></p>
                </div>

                <a href="/cadastro.php" class="btn">Criar conta gratuita</a>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container footer-content">
        <span>&copy; <?= date('Y'); ?> <?= e($brand['name']); ?>. Todos os direitos reservados.</span>
        <span><?= e($brand['slogan']); ?></span>
    </div>
</footer>

</body>
</html>
