<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function mercadopagoApiGet(string $endpoint): ?array
{
    $accessToken = mercadopagoAccessToken();
    if ($accessToken === null) {
        return null;
    }

    $ch = curl_init('https://api.mercadopago.com' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
        ],
        CURLOPT_TIMEOUT => MERCADOPAGO_API_TIMEOUT,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode >= 400) {
        return null;
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
}

function mercadopagoFetchPayment(string $paymentId): ?array
{
    if ($paymentId === '') {
        return null;
    }

    return mercadopagoApiGet('/v1/payments/' . rawurlencode($paymentId));
}

function mercadopagoFetchPaymentByMerchantOrder(string $merchantOrderId): ?array
{
    if ($merchantOrderId === '') {
        return null;
    }

    $order = mercadopagoApiGet('/merchant_orders/' . rawurlencode($merchantOrderId));
    if (!is_array($order)) {
        return null;
    }

    $payments = $order['payments'] ?? null;
    if (!is_array($payments) || $payments === []) {
        return null;
    }

    $lastPayment = end($payments);
    if (!is_array($lastPayment)) {
        return null;
    }

    $paymentId = $lastPayment['id'] ?? null;
    if (!is_string($paymentId) && !is_int($paymentId)) {
        return null;
    }

    return mercadopagoFetchPayment((string) $paymentId);
}

function mercadopagoExtractPaymentDate(array $paymentData): ?string
{
    $candidates = [
        $paymentData['date_approved'] ?? null,
        $paymentData['date_last_updated'] ?? null,
        $paymentData['date_created'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        if (!is_string($candidate) || $candidate === '') {
            continue;
        }

        $timestamp = strtotime($candidate);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }
    }

    return null;
}

