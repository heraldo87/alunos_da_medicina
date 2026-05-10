<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/services/ai-conversations.php';

$user = require_login();

$pageTitle = 'Assistente de Estudos | ' . $brand['name'];
$pageDescription = 'Assistente de estudos com IA para alunos de medicina.';

$appPageBadge = 'Assistente de Estudos';
$appPageHeading = 'Assistente de Estudos';
$appPageDescription = 'Organize suas dúvidas por assunto e continue conversas anteriores quando quiser.';

$userId = (int) $user['id'];
$selectedConversationId = filter_input(INPUT_GET, 'conversation_id', FILTER_VALIDATE_INT);

$conversations = [];
$messages = [];
$currentConversation = null;

try {
    $conversations = ai_get_active_conversations($pdo, $userId);

    if (!$selectedConversationId) {
        $selectedConversationId = ai_first_active_conversation_id($conversations);
    }

    if ($selectedConversationId) {
        $currentConversation = ai_get_active_conversation(
            $pdo,
            $userId,
            (int) $selectedConversationId
        );

        if ($currentConversation) {
            $messages = ai_get_messages(
                $pdo,
                (int) $currentConversation['id']
            );
        }
    }
} catch (PDOException $e) {
    error_log('Erro ao carregar conversas do assistente: ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Não foi possível carregar suas conversas.';
}

require __DIR__ . '/includes/layouts/app-start.php';

require __DIR__ . '/includes/partials/app-page-header.php';

require __DIR__ . '/includes/partials/assistant/flash.php';

require __DIR__ . '/includes/partials/assistant/disclaimer.php';
?>

<section class="ai-workspace mt-3">

    <?php require __DIR__ . '/includes/partials/assistant/sidebar.php'; ?>

    <div class="ai-chat-area">

        <?php require __DIR__ . '/includes/partials/assistant/chat-header.php'; ?>

        <?php require __DIR__ . '/includes/partials/assistant/messages.php'; ?>

        <?php require __DIR__ . '/includes/partials/assistant/form.php'; ?>

    </div>
</section>

<?php
require __DIR__ . '/includes/partials/assistant/scripts.php';

require __DIR__ . '/includes/layouts/app-end.php';
