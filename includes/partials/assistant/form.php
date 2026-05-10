<?php
declare(strict_types=1);
?>

<form class="ai-chat-form" action="/ai/send-message.php" method="POST">
    <?= csrf_field(); ?>

    <?php if ($currentConversation): ?>
        <input
            type="hidden"
            name="conversation_id"
            value="<?= e($currentConversation['id']); ?>"
        >
    <?php endif; ?>

    <label class="label" for="message">
        Sua pergunta
    </label>

    <textarea
        class="textarea ai-textarea"
        id="message"
        name="message"
        rows="4"
        maxlength="4000"
        placeholder="Digite sua dúvida de estudo aqui..."
        required
    ></textarea>

    <div class="ai-form-footer">
        <span class="form-help">
            Evite inserir dados pessoais ou dados de pacientes.
        </span>

        <button type="submit" class="btn btn-primary">
            Enviar pergunta
        </button>
    </div>
</form>
