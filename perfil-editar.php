<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login();

$pageTitle = 'Editar perfil | ' . $brand['name'];
$pageDescription = 'Editar dados do perfil do aluno.';

$appPageBadge = 'Perfil do aluno';
$appPageHeading = 'Editar perfil';
$appPageDescription = 'Atualize seus dados básicos de contato.';

require __DIR__ . '/includes/layouts/app-start.php';

require __DIR__ . '/includes/partials/app-page-header.php';
?>

<section class="card card-md">
    <form class="form" action="/auth/update-profile.php" method="POST">
        <?= csrf_field(); ?>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="label" for="name">Nome completo</label>
                <input
                    class="input"
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e($user['name'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="label" for="phone">Telefone / WhatsApp</label>
                <input
                    class="input"
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?= e($user['phone'] ?? ''); ?>"
                    placeholder="(00) 00000-0000"
                >
                <small class="form-help">
                    Use um número com WhatsApp para receber atualizações, lembretes e conteúdos.
                </small>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="label" for="email">E-mail</label>
                <input
                    class="input"
                    type="email"
                    id="email"
                    value="<?= e($user['email'] ?? ''); ?>"
                    readonly
                >
                <small class="form-help">
                    A alteração de e-mail será liberada em uma próxima etapa.
                </small>
            </div>

            <div class="form-group">
                <label class="label" for="institution">Instituição de ensino</label>
                <input
                    class="input"
                    type="text"
                    id="institution"
                    value="<?= e($user['institution'] ?? $brand['institution']); ?>"
                    readonly
                >
                <small class="form-help">
                    Nesta versão, somente alunos da UPDS são admitidos.
                </small>
            </div>
        </div>

        <div class="profile-actions mt-3">
            <button type="submit" class="btn btn-primary">
                Salvar alterações
            </button>

            <a href="/perfil.php" class="btn btn-outline">
                Cancelar
            </a>
        </div>
    </form>
</section>

<?php
require __DIR__ . '/includes/layouts/app-end.php';
