<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function renderPaymentPage(string $title, string $message, string $badgeClass): void
{
    $paymentId = $_GET['payment_id'] ?? null;
    $status = $_GET['status'] ?? null;
    $externalReference = $_GET['external_reference'] ?? null;

    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($title) ?></title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body class="status-body">
        <main class="status-card">
            <span class="status-badge <?= h($badgeClass) ?>"><?= h($title) ?></span>
            <h1><?= h($title) ?></h1>
            <p><?= h($message) ?></p>

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
    </body>
    </html>
    <?php
}
