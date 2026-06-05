<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mercadopago.php';

$externalReference = trim((string) ($_GET['ref'] ?? ''));

if ($externalReference === '') {
    header('Location: index.php#inscricao');
    exit;
}

try {
    $registration = fetchRegistrationByExternalReference($externalReference);
} catch (Throwable $exception) {
    $registration = null;
}

if (!is_array($registration)) {
    header('Location: index.php#inscricao');
    exit;
}

$paymentStatus = (string) ($registration['payment_status'] ?? 'pending');
$paymentId = (string) ($registration['payment_id'] ?? '');

if ($paymentStatus === 'approved') {
    header('Location: payment_success.php?external_reference=' . urlencode($externalReference));
    exit;
}

if ($paymentStatus === 'failed') {
    header('Location: payment_failure.php?external_reference=' . urlencode($externalReference));
    exit;
}

$checkoutSession = $_SESSION['pix_checkout'][$externalReference] ?? [];
$qrCode = is_array($checkoutSession) ? ($checkoutSession['qr_code'] ?? null) : null;
$qrCodeBase64 = is_array($checkoutSession) ? ($checkoutSession['qr_code_base64'] ?? null) : null;

if ((!is_string($qrCode) || $qrCode === '' || !is_string($qrCodeBase64) || $qrCodeBase64 === '') && $paymentId !== '') {
    $paymentData = mercadopagoFetchPayment($paymentId);
    if (is_array($paymentData)) {
        $pixData = mercadopagoExtractPixData($paymentData);
        if (is_array($pixData)) {
            $qrCode = $pixData['qr_code'];
            $qrCodeBase64 = $pixData['qr_code_base64'];
            $_SESSION['pix_checkout'][$externalReference] = [
                'payment_id' => $paymentId,
                'qr_code' => $qrCode,
                'qr_code_base64' => $qrCodeBase64,
            ];
        }

        $latestStatus = mercadopagoMapStatusToInternal(is_string($paymentData['status'] ?? null) ? $paymentData['status'] : null);
        if ($latestStatus !== $paymentStatus) {
            updateRegistrationPaymentStatus(
                $externalReference,
                $latestStatus,
                $paymentId !== '' ? $paymentId : null,
                $latestStatus === 'approved' ? mercadopagoExtractPaymentDate($paymentData) : null
            );
            $paymentStatus = $latestStatus;
        }
    }
}

if ($paymentStatus === 'approved') {
    header('Location: payment_success.php?external_reference=' . urlencode($externalReference));
    exit;
}

if ($paymentStatus === 'failed') {
    header('Location: payment_failure.php?external_reference=' . urlencode($externalReference));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Pix</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="status-body">
<main class="status-card pix-card">
    <span class="status-badge pending">Aguardando pagamento</span>
    <h1>Pague com Pix</h1>
    <p>Escaneie o QR Code no app do seu banco ou copie o código Pix abaixo.</p>
    <p><strong>Valor:</strong> R$ <?= h(number_format((float) ($registration['amount'] ?? 0), 2, ',', '.')) ?></p>

    <?php if (is_string($qrCodeBase64) && $qrCodeBase64 !== ''): ?>
        <img class="pix-qr-image" src="data:image/png;base64,<?= h($qrCodeBase64) ?>" alt="QR Code Pix">
    <?php else: ?>
        <p class="error-block">Não foi possível carregar o QR Code agora. Recarregue a página em alguns segundos.</p>
    <?php endif; ?>

    <?php if (is_string($qrCode) && $qrCode !== ''): ?>
        <label class="pix-code-label" for="pix-code">Código Pix (copia e cola)</label>
        <textarea id="pix-code" class="pix-code" readonly><?= h($qrCode) ?></textarea>
        <button type="button" class="btn btn-secondary" id="copy-pix">Copiar código</button>
    <?php endif; ?>

    <p class="muted">Após a confirmação, você será redirecionado automaticamente.</p>
    <a class="btn btn-primary" href="index.php">Voltar para o site</a>
</main>

<script>
    (function () {
        var copyButton = document.getElementById('copy-pix');
        var pixCode = document.getElementById('pix-code');

        if (copyButton && pixCode) {
            copyButton.addEventListener('click', function () {
                navigator.clipboard.writeText(pixCode.value).then(function () {
                    copyButton.textContent = 'Código copiado!';
                }).catch(function () {
                    copyButton.textContent = 'Copie manualmente';
                });
            });
        }

        function pollStatus() {
            fetch('payment_status_check.php?ref=' + encodeURIComponent(<?= json_encode($externalReference, JSON_UNESCAPED_UNICODE) ?>) + '&payment_id=' + encodeURIComponent(<?= json_encode($paymentId, JSON_UNESCAPED_UNICODE) ?>))
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.status === 'approved') {
                        window.location.href = 'payment_success.php?external_reference=' + encodeURIComponent(<?= json_encode($externalReference, JSON_UNESCAPED_UNICODE) ?>);
                        return;
                    }

                    if (data.status === 'failed') {
                        window.location.href = 'payment_failure.php?external_reference=' + encodeURIComponent(<?= json_encode($externalReference, JSON_UNESCAPED_UNICODE) ?>);
                        return;
                    }

                    setTimeout(pollStatus, 5000);
                })
                .catch(function () {
                    setTimeout(pollStatus, 5000);
                });
        }

        setTimeout(pollStatus, 5000);
    })();
</script>
</body>
</html>
