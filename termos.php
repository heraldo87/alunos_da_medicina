<?php
$brand = [
    'name' => 'Alunos da Medicina',
    'slogan' => 'Estude melhor. Evolua com inteligência.',
    'description' => 'Termos de Uso da plataforma Alunos da Medicina.'
];

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$updatedAt = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Termos de Uso | <?= e($brand['name']); ?></title>

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

            <span class="brand-name"><?= e($brand['name']); ?></span>
        </a>

        <nav class="nav-actions">
            <a href="/login.php" class="btn btn-outline">Entrar</a>
            <a href="/cadastro.php" class="btn btn-primary">Criar conta</a>
        </nav>
    </div>
</header>

<main class="section">
    <div class="container legal-layout">

        <aside class="legal-sidebar">
            <div class="card">
                <h2 class="heading-sm mb-2">Documentos</h2>

                <nav class="legal-menu">
                    <a href="/termos.php" class="legal-menu-link active">Termos de Uso</a>
                    <a href="/privacidade.php" class="legal-menu-link">Política de Privacidade</a>
                    <a href="/cadastro.php" class="legal-menu-link">Criar conta</a>
                </nav>
            </div>
        </aside>

        <article class="legal-content card card-lg">

            <div class="mb-4">
                <span class="badge mb-2">Documento institucional</span>

                <h1 class="heading-lg">Termos de Uso</h1>

                <p class="text-muted mt-2">
                    Última atualização: <?= e($updatedAt); ?>
                </p>
            </div>

            <div class="alert alert-info mb-4">
                Este documento é uma versão inicial para orientar o uso da plataforma.
                Antes do lançamento público, recomenda-se revisão jurídica especializada.
            </div>

            <section class="legal-section">
                <h2>1. Aceitação dos termos</h2>

                <p>
                    Ao acessar ou utilizar a plataforma <strong><?= e($brand['name']); ?></strong>,
                    o usuário declara que leu, compreendeu e concorda com estes Termos de Uso.
                    Caso não concorde com qualquer condição aqui descrita, o usuário não deverá
                    utilizar a plataforma.
                </p>
            </section>

            <section class="legal-section">
                <h2>2. Objetivo da plataforma</h2>

                <p>
                    A plataforma <strong><?= e($brand['name']); ?></strong> tem finalidade educacional
                    e acadêmica, voltada ao apoio de estudantes de medicina na organização dos estudos,
                    participação em grupos por disciplina, acompanhamento de evolução acadêmica e uso de
                    ferramentas digitais de apoio ao aprendizado.
                </p>

                <p>
                    A plataforma poderá oferecer recursos como cadastro de usuário, perfil acadêmico,
                    grupos de estudo, materiais, dashboard de acompanhamento e ferramentas baseadas em
                    inteligência artificial.
                </p>
            </section>

            <section class="legal-section">
                <h2>3. Cadastro e conta do usuário</h2>

                <p>
                    Para utilizar determinadas funcionalidades, o usuário poderá precisar criar uma conta,
                    fornecendo informações verdadeiras, completas e atualizadas.
                </p>

                <p>
                    O usuário é responsável por manter a confidencialidade de sua senha e por todas as
                    atividades realizadas em sua conta. Em caso de suspeita de acesso indevido, o usuário
                    deverá alterar sua senha e comunicar a administração da plataforma.
                </p>
            </section>

            <section class="legal-section">
                <h2>4. Responsabilidades do usuário</h2>

                <p>Ao utilizar a plataforma, o usuário compromete-se a:</p>

                <ul>
                    <li>fornecer informações corretas no cadastro;</li>
                    <li>não compartilhar sua senha com terceiros;</li>
                    <li>não utilizar a plataforma para fins ilegais, ofensivos ou fraudulentos;</li>
                    <li>respeitar outros usuários, professores, monitores e administradores;</li>
                    <li>não publicar conteúdos discriminatórios, violentos, abusivos ou que violem direitos de terceiros;</li>
                    <li>não tentar invadir, danificar, copiar indevidamente ou comprometer a segurança do sistema.</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>5. Uso acadêmico e limitações médicas</h2>

                <p>
                    Os conteúdos, respostas, materiais e recursos disponíveis na plataforma possuem caráter
                    exclusivamente educacional. Eles não substituem aulas oficiais, livros-texto, orientação
                    de professores, supervisão clínica ou avaliação profissional.
                </p>

                <p>
                    Informações geradas por ferramentas de inteligência artificial podem conter erros,
                    omissões ou interpretações incompletas. O usuário deve sempre revisar criticamente
                    o conteúdo, comparar com fontes acadêmicas confiáveis e seguir as orientações de sua
                    instituição de ensino.
                </p>

                <p>
                    A plataforma não deve ser utilizada para diagnóstico, prescrição, conduta clínica,
                    atendimento de pacientes ou tomada de decisão médica real.
                </p>
            </section>

            <section class="legal-section">
                <h2>6. Grupos, mensagens e conteúdos enviados</h2>

                <p>
                    O usuário poderá participar de grupos, enviar mensagens, publicar dúvidas, compartilhar
                    materiais ou interagir com outros participantes, conforme as funcionalidades disponíveis.
                </p>

                <p>
                    O usuário declara possuir autorização para compartilhar qualquer conteúdo enviado à
                    plataforma e se responsabiliza por não violar direitos autorais, sigilo acadêmico,
                    privacidade de terceiros ou normas institucionais.
                </p>
            </section>

            <section class="legal-section">
                <h2>7. Propriedade intelectual</h2>

                <p>
                    A identidade visual, estrutura da plataforma, código, marca, textos institucionais,
                    interfaces e demais elementos próprios do <strong><?= e($brand['name']); ?></strong>
                    pertencem aos seus respectivos titulares.
                </p>

                <p>
                    O usuário não poderá copiar, vender, distribuir, modificar ou explorar comercialmente
                    qualquer parte da plataforma sem autorização prévia e expressa.
                </p>
            </section>

            <section class="legal-section">
                <h2>8. Proteção de dados e privacidade</h2>

                <p>
                    O tratamento de dados pessoais será realizado conforme a Política de Privacidade da
                    plataforma. Ao utilizar o sistema, o usuário reconhece que seus dados poderão ser usados
                    para cadastro, autenticação, segurança, melhoria da experiência, acompanhamento acadêmico
                    e comunicação relacionada à plataforma.
                </p>

                <p>
                    A Política de Privacidade deverá ser consultada em documento próprio:
                    <a href="/privacidade.php" class="auth-link">Política de Privacidade</a>.
                </p>
            </section>

            <section class="legal-section">
                <h2>9. Monitoramento de uso e aderência</h2>

                <p>
                    A plataforma poderá registrar eventos de uso, como login, acesso a páginas, participação
                    em grupos, interações com ferramentas de estudo e uso de recursos de inteligência artificial.
                    Esses registros poderão ser utilizados para segurança, melhoria do sistema e análise de
                    engajamento acadêmico.
                </p>
            </section>

            <section class="legal-section">
                <h2>10. Suspensão ou encerramento de conta</h2>

                <p>
                    A administração da plataforma poderá suspender ou encerrar o acesso de usuários que violem
                    estes Termos de Uso, pratiquem condutas abusivas, tentem comprometer a segurança do sistema
                    ou utilizem a plataforma de forma inadequada.
                </p>
            </section>

            <section class="legal-section">
                <h2>11. Disponibilidade da plataforma</h2>

                <p>
                    A plataforma poderá passar por manutenções, atualizações, instabilidades técnicas ou
                    interrupções temporárias. Embora sejam adotadas medidas para manter o sistema disponível,
                    não há garantia de funcionamento contínuo, ininterrupto ou livre de falhas.
                </p>
            </section>

            <section class="legal-section">
                <h2>12. Alterações nos termos</h2>

                <p>
                    Estes Termos de Uso poderão ser atualizados periodicamente para refletir melhorias,
                    mudanças legais, ajustes de funcionalidades ou novas condições de uso. A versão mais
                    recente estará sempre disponível nesta página.
                </p>
            </section>

            <section class="legal-section">
                <h2>13. Contato</h2>

                <p>
                    Em caso de dúvidas sobre estes Termos de Uso, o usuário poderá entrar em contato com
                    a administração da plataforma pelos canais oficiais que serão disponibilizados no site.
                </p>
            </section>

            <div class="legal-actions">
                <a href="/cadastro.php" class="btn btn-primary">Voltar ao cadastro</a>
                <a href="/" class="btn btn-outline">Voltar ao início</a>
            </div>

        </article>
    </div>
</main>

<footer class="footer">
    <div class="container footer-content">
        <span>&copy; <?= date('Y'); ?> <?= e($brand['name']); ?>. Todos os direitos reservados.</span>
        <span><?= e($brand['slogan']); ?></span>
    </div>
</footer>

</body>
</html>
