<?php
declare(strict_types=1);
?>

<div class="ai-chat-title">
    <div class="ai-chat-title-main">
        <h2>
            <?= e($currentConversation['title'] ?? 'Nova conversa'); ?>
        </h2>

        <p>
            Cada conversa mantém seu próprio contexto para facilitar seus estudos por assunto.
        </p>
    </div>

    <?php if ($currentConversation): ?>
        <div class="ai-chat-title-actions">
            <form class="ai-title-form" action="/ai/update-conversation-title.php" method="POST">
                <?= csrf_field(); ?>

                <input
                    type="hidden"
                    name="conversation_id"
                    value="<?= e($currentConversation['id']); ?>"
                >

                <input
                    class="input ai-title-input"
                    type="text"
                    name="title"
                    value="<?= e($currentConversation['title']); ?>"
                    maxlength="90"
                    required
                >

                <button type="submit" class="btn btn-outline btn-sm">
                    Salvar título
                </button>
            </form>

            <form
                action="/ai/archive-conversation.php"
                method="POST"
                onsubmit="return confirm('Tem certeza que deseja remover esta conversa da sua lista? O histórico continuará preservado para análise interna da plataforma.');"
            >
                <?= csrf_field(); ?>

                <input
                    type="hidden"
                    name="conversation_id"
                    value="<?= e($currentConversation['id']); ?>"
                >

                <button type="submit" class="btn btn-danger btn-sm">
                    Remover da lista
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>
