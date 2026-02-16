<?php
session_start();
// CORREÇÃO: Apontando para o arquivo de configuração real dentro da pasta php/
require_once 'php/config.php'; 

// Busca os tópicos com o nome do autor (JOIN)
try {
    $sql = "SELECT t.*, u.nome as autor_nome, u.avatar 
            FROM forum_topicos t 
            JOIN usuarios u ON t.user_id = u.id 
            ORDER BY t.data_criacao DESC";
    $stmt = $pdo->query($sql);
    $topicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Como o config.php já trata erros de conexão, aqui tratamos erros de consulta
    $erro = "Erro ao carregar tópicos.";
    $topicos = []; // Garante que a variável exista mesmo com erro
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fórum - MedInFocus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800" x-data="{ openModal: false }">

    <div class="max-w-4xl mx-auto px-4 py-8">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-indigo-700">Fórum da Turma</h1>
                <p class="text-gray-500">Compartilhe dúvidas e conhecimento.</p>
            </div>
            <button @click="openModal = true" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-full shadow-lg transition flex items-center gap-2">
                <span>+</span> Novo Tópico
            </button>
        </div>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <?php if (count($topicos) > 0): ?>
                <?php foreach ($topicos as $topico): ?>
                    <a href="forum_visualizar.php?id=<?= $topico['id'] ?>" class="block group">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-200 transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900 group-hover:text-indigo-600">
                                        <?= htmlspecialchars($topico['titulo']) ?>
                                    </h2>
                                    <p class="text-gray-500 text-sm mt-1 line-clamp-2">
                                        <?= htmlspecialchars(substr($topico['conteudo'], 0, 150)) ?>...
                                    </p>
                                </div>
                                <span class="bg-indigo-50 text-indigo-700 text-xs px-3 py-1 rounded-full font-medium">
                                    <?= $topico['status'] ?>
                                </span>
                            </div>
                            <div class="mt-4 flex items-center gap-4 text-sm text-gray-400">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-xs font-bold text-gray-500">
                                        <?= strtoupper(substr($topico['autor_nome'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($topico['autor_nome']) ?></span>
                                </div>
                                <span>•</span>
                                <span><?= date('d/m/Y H:i', strtotime($topico['data_criacao'])) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
                    <p class="text-gray-500">Nenhum tópico criado ainda. Seja o primeiro!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         style="display: none;">
        
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-8 relative" @click.away="openModal = false">
            <button @click="openModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>
            
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Criar Novo Tópico</h2>
            
            <form action="php/action_criar_topico.php" method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Título</label>
                    <input type="text" name="titulo" required 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Ex: Dúvida sobre Anatomia II...">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Conteúdo</label>
                    <textarea name="conteudo" rows="5" required 
                              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Descreva sua dúvida ou discussão..."></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="openModal = false" class="px-6 py-2 text-gray-500 hover:bg-gray-100 rounded-lg transition">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-md transition">
                        Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>