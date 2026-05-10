<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$user = require_admin_panel_access();

$pageTitle = 'Aprovar alunos | ' . $brand['name'];
$pageDescription = 'Aprovação administrativa de alunos cadastrados.';

$appPageBadge = 'Administração';
$appPageHeading = 'Aprovação de alunos';
$appPageDescription = 'Analise os cadastros pendentes e aprove apenas alunos autorizados da instituição.';

try {
    $stmt = $pdo->query("
        SELECT
            id,
            name,
            email,
            phone,
            institution,
            role,
            status,
            created_at
        FROM users
        WHERE status = 'pending'
        ORDER BY created_at ASC
    ");

    $pendingStudents = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erro ao buscar alunos pendentes: ' . $e->getMessage());
    $pendingStudents = [];
    $_SESSION['flash_error'] = 'Não foi possível carregar os alunos pendentes.';
}

require __DIR__ . '/../includes/layouts/app-start.php';

require __DIR__ . '/../includes/partials/app-page-header.php';
?>

<section class="card card-md">
    <div class="admin-section-header">
        <div>
            <h2 class="heading-md">Cadastros pendentes</h2>
            <p class="text-muted mt-1">
                Os alunos abaixo ainda não conseguem acessar a plataforma até serem aprovados.
            </p>
        </div>

        <span class="badge">
            <?= count($pendingStudents); ?> pendente<?= count($pendingStudents) === 1 ? '' : 's'; ?>
        </span>
    </div>

    <?php if (empty($pendingStudents)): ?>
        <div class="empty-state mt-3">
            <strong>Nenhum cadastro pendente.</strong>
            <span>Quando novos alunos se cadastrarem, eles aparecerão nesta área.</span>
        </div>
    <?php else: ?>

        <div class="table-wrapper mt-3">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Contato</th>
                        <th>Instituição</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($pendingStudents as $student): ?>
                        <tr>
                            <td>
                                <strong><?= e($student['name']); ?></strong>
                                <span class="admin-table-muted">ID #<?= e($student['id']); ?></span>
                            </td>

                            <td>
                                <strong><?= e($student['email']); ?></strong>
                                <span class="admin-table-muted">
                                    <?= e($student['phone'] ?: 'Telefone não informado'); ?>
                                </span>
                            </td>

                            <td>
                                <?= e($student['institution']); ?>
                            </td>

                            <td>
                                <span class="badge badge-warning">
                                    Aguardando aprovação
                                </span>
                            </td>

                            <td>
                                <?= e(date('d/m/Y H:i', strtotime((string) $student['created_at']))); ?>
                            </td>

                            <td>
                                <div class="admin-actions">
                                    <form action="/admin/actions/atualizar-status-aluno.php" method="POST">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="user_id" value="<?= e($student['id']); ?>">
                                        <input type="hidden" name="action" value="approve">

                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            Aprovar
                                        </button>
                                    </form>

                                    <form action="/admin/actions/atualizar-status-aluno.php" method="POST">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="user_id" value="<?= e($student['id']); ?>">
                                        <input type="hidden" name="action" value="block">

                                        <button type="submit" class="btn btn-outline btn-sm">
                                            Bloquear
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</section>

<?php
require __DIR__ . '/../includes/layouts/app-end.php';
