<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login();

$pageTitle = 'Alterar senha | ' . $brand['name'];
$pageDescription = 'Alterar senha de acesso do aluno.';

$appPageBadge = 'Segurança da conta';
$appPageHeading = 'Alterar senha';
$appPageDescription = 'Escolha uma nova senha segura para acessar sua conta.';

require __DIR__ . '/includes/layouts/app-start.php';

require __DIR__ . '/includes/partials/app-page-header.php';
?>

<section class="card card-md">
    <form class="form" action="/auth/update-password.php" method="POST">
        <?= csrf_field(); ?>

        <div class="form-group">
            <label class="label" for="current_password">Senha atual</label>
            <input
                class="input"
                type="password"
                id="current_password"
                name="current_password"
                autocomplete="current-password"
                required
            >
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="label" for="new_password">Nova senha</label>
                <input
                    class="input"
                    type="password"
                    id="new_password"
                    name="new_password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                <small class="form-help">Use pelo menos 8 caracteres.</small>
            </div>

            <div class="form-group">
                <label class="label" for="new_password_confirmation">Confirmar nova senha</label>
                <input
                    class="input"
                    type="password"
                    id="new_password_confirmation"
                    name="new_password_confirmation"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </div>
        </div>

        <div class="profile-actions mt-3">
            <button type="submit" class="btn btn-primary">
                Alterar senha
            </button>

            <a href="/perfil.php" class="btn btn-outline">
                Cancelar
            </a>
        </div>
    </form>
</section>

<?php
require __DIR__ . '/includes/layouts/app-end.php';
