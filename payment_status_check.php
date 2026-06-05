<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$ref = trim((string) ($_GET['ref'] ?? ''));
$paymentId = trim((string) ($_GET['payment_id'] ?? ''));

if ($ref === '' && $paymentId === '') {
    echo json_encode(['status' => null]);
    exit;
}

try {
    $status = $ref !== ''
        ? fetchRegistrationPaymentStatus($ref)
        : fetchRegistrationPaymentStatusByPaymentId($paymentId);
    echo json_encode(['status' => $status]);
} catch (Throwable $exception) {
    echo json_encode(['status' => null]);
}
