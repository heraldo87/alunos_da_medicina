<?php
declare(strict_types=1);

session_start();

function redirectWithError(string $message): void
{
    $_SESSION['flash_error'] = $message;
    header('Location: /cadastro.php');
    exit;
}

function redirectWithSuccess(string $message): void
{
    $_SESSION['flash_success'] = $message;
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Método inválido.');
}

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    empty($csrfToken) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    redirectWithError('Falha de segurança. Atualize a página e tente novamente.');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$institution = 'UPDS - Universidad Privada Domingo Savio';

$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['password_confirmation'] ?? '';
$terms = $_POST['terms'] ?? null;

if ($name === '') {
    redirectWithError('Informe seu nome completo.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Informe um e-mail válido.');
}

if (strlen($password) < 8) {
    redirectWithError('A senha precisa ter pelo menos 8 caracteres.');
}

if ($password !== $passwordConfirmation) {
    redirectWithError('A confirmação de senha não confere.');
}

if ($terms !== '1') {
    redirectWithError('Você precisa aceitar os Termos de Uso e a Política de Privacidade.');
}

/*
|--------------------------------------------------------------------------
| IMPORTANTE
|--------------------------------------------------------------------------
| Nunca enviamos a senha em texto puro para o n8n.
| O n8n receberá apenas dados administrativos do cadastro.
| A senha será tratada no backend do sistema quando integrarmos ao banco.
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$webhookUrl = 'https://n8n.alunosdamedicina.com/webhook/16b6bfe4-fbe5-4f56-9841-aa894cae4ba1';

$payload = [
    'evento' => 'novo_cadastro_aluno',
    'origem' => 'alunosdamedicina.com',
    'rota_origem' => '/auth/register.php',
    'ambiente' => 'desenvolvimento',

    'aluno' => [
        'nome' => $name,
        'email' => $email,
        'telefone' => $phone,
        'instituicao' => $institution,
        'status_inicial' => 'pending',
        'role_inicial' => 'student',
        'termos_aceitos' => true,
    ],

    'credenciais' => [
        'password_hash' => $passwordHash,
    ],

    'seguranca' => [
        'senha_recebida' => true,
        'senha_texto_puro_enviada_para_n8n' => false,
        'senha_hash_enviada_para_n8n' => true,
        'senha_hash_gerada' => $passwordHash !== false,
    ],

    'metadata' => [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'data_hora' => date('Y-m-d H:i:s'),
    ],
];

$ch = curl_init($webhookUrl);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 20,
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($error !== '') {
    redirectWithError('Não foi possível comunicar com o n8n: ' . $error);
}

if ($httpCode < 200 || $httpCode >= 300) {
    redirectWithError('O n8n respondeu com erro HTTP: ' . $httpCode);
}

unset($_SESSION['csrf_token']);

redirectWithSuccess('Cadastro recebido com sucesso. Aguarde a validação da sua conta.');
