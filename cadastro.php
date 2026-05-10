<?php
session_start();

$brand = [
    'name' => 'Alunos da Medicina',
    'slogan' => 'Estude melhor. Evolua com inteligência.',
    'description' => 'Crie sua conta para organizar seus estudos, participar de grupos por disciplina e usar ferramentas inteligentes de apoio acadêmico.'
];

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar conta | <?= e($brand['name']); ?></title>

    <meta name="description" content="<?= e($brand['description']); ?>">

    <link rel="stylesheet" href="/assets/css/branding.css">
</head>
<body>

<main class="auth-page">
    <section class="auth-card auth-card-wide">

        <div class="auth-header">
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

            <h1 class="heading-md">Criar sua conta</h1>

            <p class="text-muted">
                Comece sua jornada acadêmica com mais organização, grupos de estudo e apoio inteligente.
            </p>
        </div>

        <?php if ($flashSuccess): ?>
            <div class="alert alert-success mb-2">
                <?= e($flashSuccess); ?>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger mb-2">
                <?= e($flashError); ?>
            </div>
        <?php endif; ?>

        <form class="form" action="/auth/register.php" method="POST" autocomplete="on">

            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="label" for="name">Nome completo</label>
                    <input
                        class="input"
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Seu nome completo"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="label" for="email">E-mail</label>
                    <input
                        class="input"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="seunome@email.com"
                        autocomplete="email"
                        required
                    >
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="label" for="phone">Telefone</label>
                    <input
                        class="input"
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="(00) 00000-0000"
                        autocomplete="tel"
                    >
                    <small class="form-help">Para receber atualizações, lembretes e conteúdos, use um número com WhatsApp.</small>
                </div>

                <div class="form-group">
                    <label class="label" for="institution">Instituição de ensino</label>
                    <input
                        class="input"
                        type="text"
                        id="institution"
                        name="institution"
                        value="UPDS - Universidad Privada Domingo Savio"
                        autocomplete="organization"
                        readonly
                        required
                    >
                    <small class="form-help">
                        Nesta versão inicial, serão admitidos apenas alunos da UPDS - Universidad Privada Domingo Savio.
                    </small>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="label" for="password">Senha</label>
                    <input
                        class="input"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Crie uma senha"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                    <small class="form-help">Use pelo menos 8 caracteres.</small>
                </div>

                <div class="form-group">
                    <label class="label" for="password_confirmation">Confirmar senha</label>
                    <input
                        class="input"
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Repita sua senha"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>
            </div>

            <label class="checkbox-line">
                <input type="checkbox" name="terms" value="1" required>
                <span>
                    Li e aceito os
                    <a href="/termos.php" class="auth-link">Termos de Uso</a>
                    e a
                    <a href="/privacidade.php" class="auth-link">Política de Privacidade</a>.
                </span>
            </label>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                Criar minha conta
            </button>
        </form>

        <div class="auth-footer">
            Já tem conta?
            <a href="/login.php" class="auth-link">
                Entrar na plataforma
            </a>
        </div>

    </section>
</main>

</body>
</html>
