<?php
session_start();

$brand = [
    'name' => 'Alunos da Medicina',
    'slogan' => 'Estude melhor. Evolua com inteligência.',
    'description' => 'Acesse sua conta para organizar seus estudos, participar de grupos por disciplina e usar ferramentas inteligentes de apoio acadêmico.'
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

    <title>Entrar | <?= e($brand['name']); ?></title>

    <meta name="description" content="<?= e($brand['description']); ?>">

    <link rel="stylesheet" href="/assets/css/branding.css">
</head>
<body>

<main class="auth-page">
    <section class="auth-card">

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

            <h1 class="heading-md">Entrar na plataforma</h1>

            <p class="text-muted">
                Acesse seu ambiente acadêmico e continue sua jornada de estudos.
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

        <form class="form" action="/auth/login.php" method="POST" autocomplete="on">

            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">

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

            <div class="form-group">
                <label class="label" for="password">Senha</label>
                <input
                    class="input"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Digite sua senha"
                    autocomplete="current-password"
                    required
                >
            </div>

            <div class="auth-options">
                <label class="checkbox-line">
                    <input type="checkbox" name="remember" value="1">
                    <span>Lembrar meu acesso</span>
                </label>

                <a href="/recuperar-senha.php" class="auth-link">
                    Esqueci minha senha
                </a>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                Entrar
            </button>
        </form>

        <div class="auth-footer">
            Ainda não tem conta?
            <a href="/cadastro.php" class="auth-link">
                Criar conta agora
            </a>
        </div>

    </section>
</main>

</body>
</html>
