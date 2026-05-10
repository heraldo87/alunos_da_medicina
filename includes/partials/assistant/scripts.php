<?php
declare(strict_types=1);
?>

<script>
document.querySelectorAll('.ai-suggestion').forEach((button) => {
    button.addEventListener('click', () => {
        const textarea = document.querySelector('#message');

        if (textarea) {
            textarea.value = button.dataset.message || '';
            textarea.focus();
        }
    });
});
</script>
