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

function mercadopagoApiPost(string $endpoint, array $payload, array $extraHeaders = []): ?array
{
    $accessToken = mercadopagoAccessToken();
    if ($accessToken === null) {
        return null;
    }

    $ch = curl_init('https://api.mercadopago.com' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array_merge([
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ], $extraHeaders),
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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

        function mercadopagoMapStatusToInternal(?string $status): string
        {
            if (!is_string($status) || $status === '') {
                return 'pending';
            }

            return match ($status) {
                'approved' => 'approved',
                'authorized', 'pending', 'in_process', 'in_mediation' => 'pending',
                default => 'failed',
            };
        }

        function mercadopagoExtractPixData(array $paymentData): ?array
        {
            $transactionData = $paymentData['point_of_interaction']['transaction_data'] ?? null;
            if (!is_array($transactionData)) {
                return null;
            }

            $qrCode = $transactionData['qr_code'] ?? null;
            $qrCodeBase64 = $transactionData['qr_code_base64'] ?? null;

            if (!is_string($qrCode) || $qrCode === '' || !is_string($qrCodeBase64) || $qrCodeBase64 === '') {
                return null;
            }

            return [
                'qr_code' => $qrCode,
                'qr_code_base64' => $qrCodeBase64,
            ];
        }

        function mercadopagoCreatePixPayment(
            string $externalReference,
            string $description,
            float $amount,
            string $payerEmail,
            string $payerName,
            string $notificationUrl
        ): ?array {
            if ($externalReference === '' || $payerEmail === '' || $payerName === '' || $notificationUrl === '') {
                return null;
            }

            $payload = [
                'transaction_amount' => $amount,
                'description' => $description,
                'payment_method_id' => 'pix',
                'external_reference' => $externalReference,
                'notification_url' => $notificationUrl,
                'payer' => [
                    'email' => $payerEmail,
                    'first_name' => $payerName,
                ],
            ];

            try {
                $idempotencyKey = $externalReference . '-' . bin2hex(random_bytes(4));
            } catch (Throwable $exception) {
                $idempotencyKey = $externalReference . '-' . str_replace('.', '', uniqid('', true));
            }

            $response = mercadopagoApiPost('/v1/payments', $payload, [
                'X-Idempotency-Key: ' . $idempotencyKey,
            ]);

            if (!is_array($response)) {
                return null;
            }

            $paymentId = $response['id'] ?? null;
            $status = $response['status'] ?? null;
            $pixData = mercadopagoExtractPixData($response);

            if ((!is_string($paymentId) && !is_int($paymentId)) || !is_array($pixData)) {
                return null;
            }

            return [
                'payment_id' => (string) $paymentId,
                'status' => is_string($status) ? $status : 'pending',
                'qr_code' => $pixData['qr_code'],
                'qr_code_base64' => $pixData['qr_code_base64'],
            ];
        }

        $timestamp = strtotime($candidate);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }
    }

    return null;
}
