<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mercadopago.php';

function renderPaymentPage(
    string $title,
    string $message,
    string $badgeClass,
    string $internalStatus,
    ?int $redirectSeconds = null,
    ?string $redirectUrl = null
): void
{
    $paymentId = $_GET['payment_id'] ?? ($_GET['collection_id'] ?? null);
    $status = $_GET['status'] ?? ($_GET['collection_status'] ?? null);
    $externalReference = $_GET['external_reference'] ?? null;
    $merchantOrderId = $_GET['merchant_order_id'] ?? null;

    $paymentData = null;
    if (is_string($paymentId) && $paymentId !== '') {
        $paymentData = mercadopagoFetchPayment($paymentId);
    } elseif (is_string($merchantOrderId) && $merchantOrderId !== '') {
        $paymentData = mercadopagoFetchPaymentByMerchantOrder($merchantOrderId);
    }

    if (is_array($paymentData)) {
        if ((!is_string($paymentId) || $paymentId === '') && isset($paymentData['id'])) {
            $paymentId = (string) $paymentData['id'];
        }

        if ((!is_string($status) || $status === '') && isset($paymentData['status']) && is_string($paymentData['status'])) {
            $status = $paymentData['status'];
        }

        if (
            (!is_string($externalReference) || $externalReference === '')
            && isset($paymentData['external_reference'])
            && is_string($paymentData['external_reference'])
        ) {
            $externalReference = $paymentData['external_reference'];
        }
    }

    $statusUpdateError = null;

    if (is_string($externalReference) && $externalReference !== '') {
        try {
            $paymentDate = $internalStatus === 'approved' && is_array($paymentData)
                ? mercadopagoExtractPaymentDate($paymentData)
                : ($internalStatus === 'approved' ? date('Y-m-d H:i:s') : null);
            $safePaymentId = is_string($paymentId) ? $paymentId : null;
            updateRegistrationPaymentStatus($externalReference, $internalStatus, $safePaymentId, $paymentDate);
        } catch (Throwable $exception) {
            $statusUpdateError = 'Não foi possível atualizar o status no banco de dados.';
        }
    }

    $safeRedirectUrl = null;

    if (is_string($redirectUrl) && $redirectUrl !== '') {
        $parsedRedirectUrl = parse_url($redirectUrl);
        if (
            $parsedRedirectUrl !== false
            && !isset($parsedRedirectUrl['scheme'])
            && !isset($parsedRedirectUrl['host'])
            && !str_starts_with($redirectUrl, '//')
            && !str_contains($redirectUrl, "\n")
            && !str_contains($redirectUrl, "\r")
        ) {
            $safeRedirectUrl = $redirectUrl;
        }
    }

    $shouldRedirect = $redirectSeconds !== null
        && $redirectSeconds > 0
        && $safeRedirectUrl !== null;

    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($title) ?></title>
        <?php if ($shouldRedirect): ?>
            <meta http-equiv="refresh" content="<?= h((string) $redirectSeconds . ';url=' . $safeRedirectUrl) ?>">
        <?php endif; ?>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body class="status-body">
        <main class="status-card">
            <span class="status-badge <?= h($badgeClass) ?>"><?= h($title) ?></span>
            <h1><?= h($title) ?></h1>
            <p><?= h($message) ?></p>
            <?php if ($shouldRedirect): ?>
                <p class="muted">Você será redirecionado para a página inicial em <?= h((string) $redirectSeconds) ?> segundos.</p>
            <?php endif; ?>

            <?php if ($statusUpdateError !== null): ?>
                <p class="error-block"><?= h($statusUpdateError) ?></p>
            <?php endif; ?>

            <ul>
                <?php if (is_string($paymentId) && $paymentId !== ''): ?>
                    <li><strong>ID do pagamento:</strong> <?= h($paymentId) ?></li>
                <?php endif; ?>
                <?php if (is_string($status) && $status !== ''): ?>
                    <li><strong>Status:</strong> <?= h($status) ?></li>
                <?php endif; ?>
                <?php if (is_string($externalReference) && $externalReference !== ''): ?>
                    <li><strong>Referência:</strong> <?= h($externalReference) ?></li>
                <?php endif; ?>
            </ul>

            <a class="btn btn-primary" href="index.php">Voltar para o site</a>
        </main>
        <?php if ($shouldRedirect): ?>
            <script>
                setTimeout(function () {
                    window.location.href = <?= json_encode($safeRedirectUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                }, <?= (int) $redirectSeconds * 1000 ?>);
            </script>
        <?php endif; ?>
        <?php if ($internalStatus === 'pending' && is_string($externalReference) && $externalReference !== ''): ?>
            <script>
                (function poll() {
                    setTimeout(function () {
                        fetch('payment_status_check.php?ref=' + encodeURIComponent(<?= json_encode($externalReference, JSON_UNESCAPED_UNICODE) ?>))
                            .then(function (r) { return r.json(); })
                            .then(function (d) {
                                if (d.status === 'approved') {
                                    window.location.href = 'payment_success.php?external_reference=' + encodeURIComponent(<?= json_encode($externalReference, JSON_UNESCAPED_UNICODE) ?>);
                                } else if (d.status === 'failed') {
                                    window.location.href = 'payment_failure.php?external_reference=' + encodeURIComponent(<?= json_encode($externalReference, JSON_UNESCAPED_UNICODE) ?>);
                                } else {
                                    poll();
                                }
                            })
                            .catch(function () { poll(); });
                    }, 5000);
                })();
            </script>
        <?php endif; ?>
    </body>
    </html>
    <?php
}
