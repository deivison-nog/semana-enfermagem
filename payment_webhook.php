<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mercadopago.php';

function webhookInput(): array
{
    $raw = file_get_contents('php://input');
    if (!is_string($raw) || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

header('Content-Type: application/json; charset=utf-8');

$input = webhookInput();

$topicRaw = $_GET['topic'] ?? $_GET['type'] ?? ($input['type'] ?? null);
$topic = is_string($topicRaw) ? strtolower(trim($topicRaw)) : null;
$resourceId = $_GET['id'] ?? ($_GET['data_id'] ?? ($input['data']['id'] ?? null));

$paymentData = null;

if (!in_array($topic, ['payment', 'merchant_order'], true)) {
    echo json_encode(['ok' => true]);
    exit;
}

if ($topic === 'payment' && (is_string($resourceId) || is_int($resourceId))) {
    $paymentData = mercadopagoFetchPayment((string) $resourceId);
}

if ($topic === 'merchant_order' && (is_string($resourceId) || is_int($resourceId))) {
    $paymentData = mercadopagoFetchPaymentByMerchantOrder((string) $resourceId);
}

if (!is_array($paymentData)) {
    echo json_encode(['ok' => true]);
    exit;
}

$externalReference = $paymentData['external_reference'] ?? null;
$gatewayStatus = $paymentData['status'] ?? null;
$paymentId = isset($paymentData['id']) ? (string) $paymentData['id'] : null;

if (!is_string($externalReference) || $externalReference === '') {
    echo json_encode(['ok' => true]);
    exit;
}

$internalStatus = mercadopagoMapStatusToInternal(is_string($gatewayStatus) ? $gatewayStatus : null);
$paymentDate = $internalStatus === 'approved' ? mercadopagoExtractPaymentDate($paymentData) : null;

try {
    updateRegistrationPaymentStatus($externalReference, $internalStatus, $paymentId, $paymentDate);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['ok' => false]);
    exit;
}

echo json_encode(['ok' => true]);
