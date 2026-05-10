<?php
declare(strict_types=1);

$roleLabel = role_label($user['role'] ?? 'student');
$statusLabel = status_label($user['status'] ?? 'pending');

$institution = $user['institution'] ?? ($brand['institution'] ?? 'UPDS - Universidad Privada Domingo Savio');
$phone = $user['phone'] ?? '';
?>

<section class="profile-hero">
    <div class="profile-avatar-large">
        <?= e(first_letter((string) ($user['name'] ?? 'Aluno'))); ?>
    </div>

    <div>
        <span class="badge badge-success"><?= e($statusLabel); ?></span>

        <h2><?= e($user['name'] ?? 'Aluno'); ?></h2>

        <p>
            Perfil acadêmico vinculado à plataforma <?= e($brand['name']); ?>.
            Nesta primeira versão, seus dados principais são exibidos para conferência.
        </p>
    </div>
</section>

<section class="profile-grid mt-4">

    <article class="card card-md">
        <div class="card-icon">
            <?= sidebar_icon_svg('user'); ?>
        </div>

        <h2 class="heading-md">Dados pessoais</h2>

        <div class="profile-detail-list mt-3">
            <div>
                <span>Nome completo</span>
                <strong><?= e($user['name'] ?? '-'); ?></strong>
            </div>

            <div>
                <span>E-mail</span>
                <strong><?= e($user['email'] ?? '-'); ?></strong>
            </div>

            <div>
                <span>Telefone / WhatsApp</span>
                <strong><?= e($phone !== '' ? $phone : 'Não informado'); ?></strong>
            </div>

            <div>
                <span>Instituição de ensino</span>
                <strong><?= e($institution); ?></strong>
            </div>
        </div>
    </article>

    <article class="card card-md">
        <div class="card-icon">
            <svg viewBox="0 0 24 24" class="inline-line-icon" aria-hidden="true">
                <path d="M12 3l8 4v5c0 5-3.4 8.7-8 9-4.6-.3-8-4-8-9V7l8-4z"></path>
                <path d="M9 12l2 2 4-5"></path>
            </svg>
        </div>

        <h2 class="heading-md">Dados de acesso</h2>

        <div class="profile-detail-list mt-3">
            <div>
                <span>Perfil</span>
                <strong><?= e($roleLabel); ?></strong>
            </div>

            <div>
                <span>Status da conta</span>
                <strong><?= e($statusLabel); ?></strong>
            </div>

            <div>
                <span>ID do usuário</span>
                <strong>#<?= e($user['id'] ?? '-'); ?></strong>
            </div>

            <div>
                <span>Senha</span>
                <strong>Protegida por criptografia</strong>
            </div>
        </div>
    </article>

</section>

<section class="card card-md mt-4">
    <div class="profile-actions-header">
        <div>
            <h2 class="heading-md">Ações da conta</h2>
            <p class="text-muted mt-1">
                Em breve, você poderá editar seus dados, alterar foto de perfil e trocar sua senha diretamente por aqui.
            </p>
        </div>
    </div>

    <div class="profile-actions mt-3">
        <a class="btn btn-primary" href="/perfil-editar.php">
            Editar perfil
        </a>

        <a class="btn btn-outline" href="/senha-alterar.php">
            Alterar senha
        </a>
    </div>
</section>
