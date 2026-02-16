<?php
session_start();
require_once 'db_connection.php';

// 1. Validação do ID
$id_topico = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_topico) {
    header("Location: forum.php");
    exit;
}

try {
    // 2. Buscar Dados do Tópico + Autor
    $sqlTopico = "SELECT t.*, u.nome, u.avatar 
                  FROM forum_topicos t 
                  JOIN usuarios u ON t.user_id = u.id 
                  WHERE t.id = :id";
    $stmt = $pdo->prepare($sqlTopico);
    $stmt->bindValue(':id', $id_topico);
    $stmt->execute();
    $topico = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$topico) {
        die("Tópico não encontrado.");
    }

    // 3. Buscar Mensagens (Comentários)
    // Ordenamos por data para manter a cronologia
    $sqlMsgs = "SELECT m.*, u.nome, u.avatar 
                FROM forum_mensagens m 
                JOIN usuarios u ON m.user_id = u.id 
                WHERE m.topico_id = :id 
                ORDER BY m.data_criacao ASC";
    $stmtMsg = $pdo->prepare($sqlMsgs);
    $stmtMsg->bindValue(':id', $id_topico);
    $stmtMsg->execute();
    $todasMensagens = $stmtMsg->fetchAll(PDO::FETCH_ASSOC);

    // 4. Organizar Mensagens (Hierarquia Pai -> Filho)
    $comentariosRaiz = [];
    $respostas = [];

    foreach ($todasMensagens as $msg) {
        if ($msg['parent_id'] === null) {
            $comentariosRaiz[] = $msg;
        } else {
            $respostas[$msg['parent_id']][] = $msg;
        }
    }

} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($topico['titulo']) ?> - Fórum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800">

    <div class="max-w-4xl mx-auto px-4 py-8">
        
        <a href="forum.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6 font-medium transition">
            ← Voltar para o Fórum
        </a>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-100 border-green-500 border-l-4 text-green-700 p-4 mb-6 rounded shadow-sm">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($topico['titulo']) ?></h1>
            
            <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-100">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold">
                    <?= strtoupper(substr($topico['nome'], 0, 1)) ?>
                </div>
                <div>
                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($topico['nome']) ?></p>
                    <p class="text-xs text-gray-500"><?= date('d/m/Y \à\s H:i', strtotime($topico['data_criacao'])) ?></p>
                </div>
                <span class="ml-auto bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs uppercase font-bold tracking-wider">
                    <?= $topico['status'] ?>
                </span>
            </div>

            <div class="prose max-w-none text-gray-700 leading-relaxed">
                <?= nl2br(htmlspecialchars($topico['conteudo'])) ?>
            </div>
        </div>

        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            Comentários <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-sm"><?= count($todasMensagens) ?></span>
        </h3>

        <div class="space-y-6">
            
            <?php foreach ($comentariosRaiz as $msg): ?>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" x-data="{ replying: false }">
                    
                    <div class="flex gap-4">
                        <div class="shrink-0 w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center font-bold text-gray-600">
                            <?= strtoupper(substr($msg['nome'], 0, 1)) ?>
                        </div>
                        <div class="w-full">
                            <div class="flex justify-between items-start">
                                <span class="font-bold text-gray-900"><?= htmlspecialchars($msg['nome']) ?></span>
                                <span class="text-xs text-gray-400"><?= date('d/m H:i', strtotime($msg['data_criacao'])) ?></span>
                            </div>
                            <p class="mt-2 text-gray-700"><?= nl2br(htmlspecialchars($msg['mensagem'])) ?></p>
                            
                            <button @click="replying = !replying" class="mt-3 text-sm text-indigo-600 font-medium hover:underline flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Responder
                            </button>
                        </div>
                    </div>

                    <div x-show="replying" class="mt-4 ml-14 pl-4 border-l-2 border-indigo-100" x-transition>
                        <form action="php/action_forum_responder.php" method="POST">
                            <input type="hidden" name="topico_id" value="<?= $id_topico ?>">
                            <input type="hidden" name="parent_id" value="<?= $msg['id'] ?>">
                            <textarea name="mensagem" required rows="2" class="w-full p-3 bg-gray-50 rounded-lg border border-gray-200 focus:outline-none focus:border-indigo-500 text-sm" placeholder="Responda a <?= htmlspecialchars($msg['nome']) ?>..."></textarea>
                            <div class="mt-2 flex justify-end gap-2">
                                <button type="button" @click="replying = false" class="text-xs text-gray-500 hover:text-gray-700 px-3 py-1">Cancelar</button>
                                <button type="submit" class="bg-indigo-600 text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Enviar Resposta</button>
                            </div>
                        </form>
                    </div>

                    <?php if (isset($respostas[$msg['id']])): ?>
                        <div class="mt-4 ml-4 md:ml-14 space-y-4 border-l-2 border-gray-100 pl-4">
                            <?php foreach ($respostas[$msg['id']] as $resposta): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center text-xs font-bold text-white">
                                            <?= strtoupper(substr($resposta['nome'], 0, 1)) ?>
                                        </div>
                                        <span class="font-bold text-sm text-gray-800"><?= htmlspecialchars($resposta['nome']) ?></span>
                                        <span class="text-xs text-gray-400">• <?= date('d/m H:i', strtotime($resposta['data_criacao'])) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-700 pl-8"><?= nl2br(htmlspecialchars($resposta['mensagem'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-12 bg-gray-100 p-6 rounded-xl">
            <h4 class="font-bold text-gray-700 mb-4">Deixe seu comentário</h4>
            <form action="php/action_forum_responder.php" method="POST">
                <input type="hidden" name="topico_id" value="<?= $id_topico ?>">
                <textarea name="mensagem" required rows="4" class="w-full p-4 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" placeholder="Escreva sua opinião, dúvida ou sugestão..."></textarea>
                <div class="mt-4 text-right">
                    <button type="submit" class="bg-indigo-600 text-white font-bold px-8 py-3 rounded-xl hover:bg-indigo-700 shadow-lg transition transform hover:-translate-y-0.5">
                        Publicar Comentário
                    </button>
                </div>
            </form>
        </div>

    </div>

</body>
</html>