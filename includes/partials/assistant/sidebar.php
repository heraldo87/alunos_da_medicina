<?php
declare(strict_types=1);
?>

<aside class="ai-conversations-panel">
    <div class="ai-conversations-header">
        <h2>Conversas</h2>

        <form action="/ai/new-conversation.php" method="POST">
            <?= csrf_field(); ?>

            <button type="submit" class="btn btn-primary btn-sm">
                Nova conversa
            </button>
        </form>
    </div>

    <?php if (empty($conversations)): ?>
        <div class="ai-conversation-empty">
            Nenhuma conversa ainda.
        </div>
    <?php else: ?>
        <nav class="ai-conversation-list" aria-label="Conversas do Assistente de Estudos">
            <?php foreach ($conversations as $conversation): ?>
                <?php
                    $isActiveConversation = $currentConversation
                        && (int) $currentConversation['id'] === (int) $conversation['id'];
                ?>

                <a
                    class="ai-conversation-link <?= $isActiveConversation ? 'active' : ''; ?>"
                    href="/assistente.php?conversation_id=<?= e($conversation['id']); ?>"
                >
                    <strong><?= e($conversation['title']); ?></strong>
                    <span><?= e(ai_excerpt($conversation['last_message'] ?? null)); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</aside>
