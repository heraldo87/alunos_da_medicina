<?php
session_start();

// 1. Configuração e Conexão (Ajuste o caminho do seu arquivo de conexão)
// Supondo que você tenha um arquivo db.php ou conexao.php na raiz ou pasta config
require_once '../config.php'; 

// 2. Verificação de Segurança
if (!isset($_SESSION['user_id'])) {
    die("Erro: Você precisa estar logado para criar um tópico.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../forum.php");
    exit;
}

// 3. Recebimento e Sanitização de Dados
$titulo = trim($_POST['titulo'] ?? '');
$conteudo = trim($_POST['conteudo'] ?? '');
$user_id = $_SESSION['user_id'];

// Validação básica
if (empty($titulo) || empty($conteudo)) {
    $_SESSION['flash_error'] = "Título e conteúdo são obrigatórios!";
    header("Location: ../forum.php"); // Ou página de criar
    exit;
}

try {
    // 4. Inserção no Banco de Dados (PDO com Prepared Statement)
    $sql = "INSERT INTO forum_topicos (user_id, titulo, conteudo, status, data_criacao) 
            VALUES (:user_id, :titulo, :conteudo, 'aberto', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
    $stmt->bindParam(':conteudo', $conteudo, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        // Sucesso: Redireciona para o fórum com mensagem de sucesso
        $_SESSION['flash_success'] = "Tópico criado com sucesso!";
        // Opcional: Redirecionar direto para o tópico criado usando $pdo->lastInsertId()
        header("Location: ../forum.php");
        exit;
    } else {
        throw new Exception("Erro ao salvar no banco.");
    }

} catch (Exception $e) {
    // Log do erro (interno) e mensagem amigável (usuário)
    error_log($e->getMessage());
    $_SESSION['flash_error'] = "Ocorreu um erro ao tentar publicar. Tente novamente.";
    header("Location: ../forum.php");
    exit;
}
?>