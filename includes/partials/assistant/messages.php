<?php
declare(strict_types=1);
?>

<div class="ai-chat-history">

    <?php if (empty($messages)): ?>
        <div class="ai-empty-state">
            <div class="ai-empty-icon">
                <?= sidebar_icon_svg('ai'); ?>
            </div>

            <h2>Comece uma conversa</h2>

            <p>
                Faça uma pergunta sobre seus estudos de medicina. Por exemplo:
            </p>

            <div class="ai-suggestion-list">
                <button type="button" class="ai-suggestion" data-message="Explique potencial de membrana para um aluno do primeiro ano de medicina.">
                    Potencial de membrana
                </button>

                <button type="button" class="ai-suggestion" data-message="Explique a circulação pulmonar e sistêmica de forma simples.">
                    Circulação pulmonar e sistêmica
                </button>

                <button type="button" class="ai-suggestion" data-message="Qual a diferença entre mitose e meiose de forma didática?">
                    Mitose e meiose
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <?php
                $isUser = $message['role'] === 'user';
                $messageClass = $isUser ? 'ai-message-user' : 'ai-message-assistant';
                $label = $isUser ? 'Você' : 'Assistente de Estudos';
            ?>

            <article class="ai-message <?= e($messageClass); ?>">
                <div class="ai-message-label">
                    <?= e($label); ?>
                </div>

                <div class="ai-message-content">
                    <?= nl2br(e($message['content'])); ?>
                </div>

                <div class="ai-message-time">
                    <?= e(date('d/m/Y H:i', strtotime((string) $message['created_at']))); ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
