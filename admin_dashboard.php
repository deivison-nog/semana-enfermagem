<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/helpers.php';
require __DIR__ . '/admin_auth.php';
require __DIR__ . '/db.php';

requireAdminLogin();

$registrations = [];
$error = null;

try {
    $registrations = fetchRegistrations();
} catch (Throwable $exception) {
    $error = 'Não foi possível carregar os inscritos. Verifique a conexão com o banco de dados.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do administrador</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="hero admin-hero">
    <div class="container">
        <nav class="topnav">
            <a href="index.php">Semana da Enfermagem 2026</a>
            <div>
                <a href="admin_logout.php">Sair</a>
            </div>
        </nav>
        <h1>Painel de inscritos</h1>
        <p class="subtitle">Visualização completa dos dados de inscrição e pagamento.</p>
    </div>
</header>

<main class="container section">
    <?php if ($error !== null): ?>
        <p class="error-block"><?= h($error) ?></p>
    <?php else: ?>
        <div class="card table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                        <th>Status pagamento</th>
                        <th>ID pagamento</th>
                        <th>Data pagamento</th>
                        <th>Referência</th>
                        <th>Data inscrição</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($registrations === []): ?>
                    <tr>
                        <td colspan="12">Nenhum inscrito encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registrations as $registration): ?>
                        <tr>
                            <td><?= h((string) $registration['id']) ?></td>
                            <td><?= h((string) $registration['nome']) ?></td>
                            <td><?= h((string) $registration['cpf']) ?></td>
                            <td><?= h((string) $registration['email']) ?></td>
                            <td><?= h((string) $registration['telefone']) ?></td>
                            <td><?= h((string) $registration['categoria']) ?></td>
                            <td>R$ <?= h(number_format((float) $registration['amount'], 2, ',', '.')) ?></td>
                            <td><?= h((string) $registration['payment_status']) ?></td>
                            <td><?= h((string) ($registration['payment_id'] ?? '')) ?></td>
                            <td><?= h((string) ($registration['payment_date'] ?? '')) ?></td>
                            <td><?= h((string) $registration['external_reference']) ?></td>
                            <td><?= h((string) $registration['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
