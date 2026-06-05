<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/mercadopago.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nome = mb_strtoupper(trim((string) ($_POST['nome'] ?? '')), 'UTF-8');
$cpfRaw = preg_replace('/\D+/', '', (string) ($_POST['cpf'] ?? ''));
$cpf = is_string($cpfRaw) ? $cpfRaw : '';
$email = trim((string) ($_POST['email'] ?? ''));
$telefoneRaw = preg_replace('/\D+/', '', (string) ($_POST['telefone'] ?? ''));
$telefone = is_string($telefoneRaw) ? $telefoneRaw : '';
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

if (!isset($errors['cpf']) && hasApprovedRegistrationByCpf($cpf)) {
    $errors['checkout'] = 'O CPF informado já está com a inscrição aprovada!';
}

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors['email'] = 'Informe um e-mail válido.';
}

if (strlen($telefone) < 10 || strlen($telefone) > 12) {
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

try {
    $externalReference = 'SE-' . bin2hex(random_bytes(12));
} catch (Throwable $exception) {
    setFormState($old, ['checkout' => 'Não foi possível iniciar o pagamento agora. Tente novamente.']);
    header('Location: index.php#inscricao');
    exit;
}
$baseUrl = appBaseUrl();

$notificationUrl = mercadopagoNotificationUrl() ?? ($baseUrl . '/payment_webhook.php');
$pixPayment = mercadopagoCreatePixPayment(
    $externalReference,
    'Inscrição - Semana da Enfermagem 2026',
    EVENT_PRICE,
    $email,
    $nome,
    $notificationUrl
);

if (!is_array($pixPayment)) {
    setFormState($old, ['checkout' => 'Não foi possível gerar o Pix agora. Tente novamente em instantes.']);
    header('Location: index.php#inscricao');
    exit;
}

$paymentId = $pixPayment['payment_id'] ?? null;
if (!is_string($paymentId) || $paymentId === '') {
    setFormState($old, ['checkout' => 'Resposta inválida do Mercado Pago ao gerar o Pix.']);
    header('Location: index.php#inscricao');
    exit;
}

try {
    createRegistration([
        'external_reference' => $externalReference,
        'nome' => $nome,
        'cpf' => $cpf,
        'email' => $email,
        'telefone' => $telefone,
        'categoria' => $categoria,
        'amount' => EVENT_PRICE,
        'currency' => EVENT_CURRENCY,
        'payment_status' => mercadopagoMapStatusToInternal($pixPayment['status'] ?? null),
        'payment_id' => $paymentId,
    ]);
} catch (Throwable $exception) {
    setFormState($old, ['checkout' => 'Não foi possível concluir sua inscrição agora. Tente novamente em instantes.']);
    header('Location: index.php#inscricao');
    exit;
}

clearOldInputs();
$_SESSION['success'] = 'Inscrição validada. Você será direcionado ao pagamento Pix.';
$_SESSION['pix_checkout'][$externalReference] = [
    'payment_id' => $paymentId,
    'qr_code' => $pixPayment['qr_code'] ?? null,
    'qr_code_base64' => $pixPayment['qr_code_base64'] ?? null,
];

header('Location: payment_pix.php?ref=' . urlencode($externalReference));
exit;
