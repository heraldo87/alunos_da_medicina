<?php
session_start();

// 1. Conexão
require_once '../db_connection.php'; 

// 2. Segurança: Apenas logados
if (!isset($_SESSION['user_id'])) {
    die("Erro: Você precisa estar logado para responder.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../forum.php");
    exit;
}

// 3. Recebimento de Dados
$user_id   = $_SESSION['user_id'];
$topico_id = filter_input(INPUT_POST, 'topico_id', FILTER_VALIDATE_INT);
$mensagem  = trim($_POST['mensagem'] ?? '');

// Lógica do "Reply" (Resposta aninhada)
// Se vier vazio ou não for número, definimos como NULL (comentário raiz)
$parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT);
if (!$parent_id) {
    $parent_id = null; 
}

// 4. Validação
if (!$topico_id || empty($mensagem)) {
    $_SESSION['flash_error'] = "A mensagem não pode estar vazia.";
    // Tenta voltar para o tópico se tiver ID, senão vai para a lista
    if ($topico_id) {
        header("Location: ../forum_visualizar.php?id=$topico_id");
    } else {
        header("Location: ../forum.php");
    }
    exit;
}

try {
    // 5. Inserção Inteligente
    $sql = "INSERT INTO forum_mensagens (topico_id, user_id, parent_id, mensagem, data_criacao) 
            VALUES (:topico_id, :user_id, :parent_id, :mensagem, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':topico_id', $topico_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id',   $user_id,   PDO::PARAM_INT);
    $stmt->bindValue(':parent_id', $parent_id, PDO::PARAM_INT); // PDO trata null corretamente aqui
    $stmt->bindValue(':mensagem',  $mensagem,  PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "Mensagem enviada!";
    } else {
        throw new Exception("Erro ao salvar mensagem.");
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['flash_error'] = "Erro ao enviar mensagem.";
}

// 6. Redirecionamento: Volta para o tópico para ver a mensagem
header("Location: ../forum_visualizar.php?id=$topico_id");
exit;
?>