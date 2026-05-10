<?php
declare(strict_types=1);

function ai_get_active_conversations(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.title,
            c.status,
            c.created_at,
            c.updated_at,
            (
                SELECT m.content
                FROM ai_messages m
                WHERE m.conversation_id = c.id
                ORDER BY m.id DESC
                LIMIT 1
            ) AS last_message
        FROM ai_conversations c
        WHERE c.user_id = :user_id
        AND c.status = 'active'
        ORDER BY c.updated_at DESC, c.id DESC
    ");

    $stmt->execute([
        ':user_id' => $userId,
    ]);

    return $stmt->fetchAll();
}

function ai_get_active_conversation(PDO $pdo, int $userId, int $conversationId): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, title, status, created_at, updated_at
        FROM ai_conversations
        WHERE id = :id
        AND user_id = :user_id
        AND status = 'active'
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $conversationId,
        ':user_id' => $userId,
    ]);

    $conversation = $stmt->fetch();

    return $conversation ?: null;
}

function ai_get_messages(PDO $pdo, int $conversationId, int $limit = 200): array
{
    $limit = max(1, min($limit, 500));

    $stmt = $pdo->prepare("
        SELECT role, content, model, error_message, created_at
        FROM ai_messages
        WHERE conversation_id = :conversation_id
        ORDER BY id ASC
        LIMIT {$limit}
    ");

    $stmt->execute([
        ':conversation_id' => $conversationId,
    ]);

    return $stmt->fetchAll();
}

function ai_first_active_conversation_id(array $conversations): ?int
{
    if (empty($conversations)) {
        return null;
    }

    return (int) $conversations[0]['id'];
}

function ai_excerpt(?string $text, int $limit = 80): string
{
    $text = trim((string) $text);

    if ($text === '') {
        return 'Conversa vazia';
    }

    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $limit, 'UTF-8');
    }

    return substr($text, 0, $limit);
}
