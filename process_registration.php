<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nome = trim((string) ($_POST['nome'] ?? ''));
$cpf = preg_replace('/\D+/', '', (string) ($_POST['cpf'] ?? '')) ?? '';
$email = trim((string) ($_POST['email'] ?? ''));
$telefone = preg_replace('/\D+/', '', (string) ($_POST['telefone'] ?? '')) ?? '';
$categoria = trim((string) ($_POST['categoria'] ?? ''));

$old = [
    'nome' => $nome,
    'cpf' => $cpf,
    'email' => $email,
    'telefone' => $telefone,
    'categoria' => $categoria,
];

$errors = [];

if (mb_strlen($nome) < 3) {
    $errors['nome'] = 'Informe seu nome completo.';
}

if (!preg_match('/^\d{11}$/', $cpf)) {
    $errors['cpf'] = 'Informe um CPF válido com 11 dígitos.';
}

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors['email'] = 'Informe um e-mail válido.';
}

if (strlen($telefone) < 10 || strlen($telefone) > 11) {
    $errors['telefone'] = 'Informe um telefone válido com DDD.';
}

if (!in_array($categoria, allowedCategories(), true)) {
    $errors['categoria'] = 'Selecione uma categoria válida.';
}

$accessToken = mercadopagoAccessToken();
if ($accessToken === null) {
    $errors['checkout'] = 'Configuração de pagamento ausente. Defina MERCADOPAGO_ACCESS_TOKEN no arquivo .env.';
}

if ($errors !== []) {
    setFormState($old, $errors);
    header('Location: index.php#inscricao');
    exit;
}

$externalReference = 'SE-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
$baseUrl = appBaseUrl();

$payload = [
    'items' => [[
        'id' => $externalReference,
        'title' => 'Inscrição - Semana da Enfermagem 2026',
        'description' => 'Semana da Enfermagem – Município de Colares 2026',
        'quantity' => 1,
        'currency_id' => EVENT_CURRENCY,
        'unit_price' => EVENT_PRICE,
    ]],
    'payer' => [
        'name' => $nome,
        'email' => $email,
    ],
    'back_urls' => [
        'success' => $baseUrl . '/payment_success.php',
        'pending' => $baseUrl . '/payment_pending.php',
        'failure' => $baseUrl . '/payment_failure.php',
    ],
    'auto_return' => 'approved',
    'statement_descriptor' => 'SEMANA ENFERMAGEM',
    'external_reference' => $externalReference,
    'metadata' => [
        'cpf' => $cpf,
        'telefone' => $telefone,
        'categoria' => $categoria,
    ],
];

$notificationUrl = mercadopagoNotificationUrl();
if ($notificationUrl !== null) {
    $payload['notification_url'] = $notificationUrl;
}

$ch = curl_init('https://api.mercadopago.com/checkout/preferences');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 20,
]);

$response = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode >= 400 || $curlError !== '') {
    setFormState($old, ['checkout' => 'Não foi possível iniciar o pagamento agora. Tente novamente em instantes.']);
    header('Location: index.php#inscricao');
    exit;
}

$data = json_decode($response, true);
$checkoutUrl = is_array($data) ? ($data['init_point'] ?? null) : null;

if (!is_string($checkoutUrl) || $checkoutUrl === '') {
    setFormState($old, ['checkout' => 'Resposta inválida do Mercado Pago. Verifique as credenciais e tente novamente.']);
    header('Location: index.php#inscricao');
    exit;
}

saveRegistration([
    'created_at' => date(DATE_ATOM),
    'external_reference' => $externalReference,
    'nome' => $nome,
    'cpf' => $cpf,
    'email' => $email,
    'telefone' => $telefone,
    'categoria' => $categoria,
    'status' => 'checkout_iniciado',
]);

clearOldInputs();
$_SESSION['success'] = 'Inscrição validada. Você será redirecionado para concluir o pagamento.';

header('Location: ' . $checkoutUrl);
exit;
