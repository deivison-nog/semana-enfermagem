<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

function renderPaymentPage(
    string $title,
    string $message,
    string $badgeClass,
    string $internalStatus,
    ?int $redirectSeconds = null,
    ?string $redirectUrl = null
): void
{
    $paymentId = $_GET['payment_id'] ?? null;
    $status = $_GET['status'] ?? null;
    $externalReference = $_GET['external_reference'] ?? null;

    $statusUpdateError = null;

    if (is_string($externalReference) && $externalReference !== '') {
        try {
            $paymentDate = $internalStatus === 'approved' ? date('Y-m-d H:i:s') : null;
            $safePaymentId = is_string($paymentId) ? $paymentId : null;
            updateRegistrationPaymentStatus($externalReference, $internalStatus, $safePaymentId, $paymentDate);
        } catch (Throwable $exception) {
            $statusUpdateError = 'Não foi possível atualizar o status no banco de dados.';
        }
    }

    $shouldRedirect = $redirectSeconds !== null
        && $redirectSeconds > 0
        && is_string($redirectUrl)
        && $redirectUrl !== '';

    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($title) ?></title>
        <?php if ($shouldRedirect): ?>
            <meta http-equiv="refresh" content="<?= h((string) $redirectSeconds . ';url=' . $redirectUrl) ?>">
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
                    window.location.href = <?= json_encode($redirectUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                }, <?= (int) $redirectSeconds * 1000 ?>);
            </script>
        <?php endif; ?>
    </body>
    </html>
    <?php
}
