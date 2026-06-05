<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/helpers.php';
require __DIR__ . '/admin_auth.php';
require __DIR__ . '/db.php';

requireAdminLogin();

$registrations = [];
$error = null;
$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = trim((string) ($_GET['payment_status'] ?? ''));
$allowedFilterFields = ['cpf', 'email', 'telefone', 'payment_status'];
$fieldsParam = $_GET['fields'] ?? $allowedFilterFields;
$selectedFields = is_array($fieldsParam)
    ? array_values(array_intersect($allowedFilterFields, array_map('strval', $fieldsParam)))
    : $allowedFilterFields;

if ($selectedFields === []) {
    $selectedFields = $allowedFilterFields;
}
$availableStatuses = [];

try {
    $registrations = fetchRegistrations();

    foreach ($registrations as $registration) {
        $status = (string) ($registration['payment_status'] ?? '');
        if ($status !== '') {
            $availableStatuses[$status] = $status;
        }
    }

    ksort($availableStatuses);
    $availableStatuses = array_values($availableStatuses);

    if ($statusFilter !== '') {
        $registrations = array_values(array_filter(
            $registrations,
            static fn (array $registration): bool => (string) ($registration['payment_status'] ?? '') === $statusFilter
        ));
    }

    if ($search !== '') {
        $registrations = array_values(array_filter(
            $registrations,
            static function (array $registration) use ($selectedFields, $search): bool {
                foreach ($selectedFields as $field) {
                    if (stripos((string) ($registration[$field] ?? ''), $search) !== false) {
                        return true;
                    }
                }

                return false;
            }
        ));
    }
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
        <form class="card admin-filter-card no-print" method="get" action="admin_dashboard.php">
            <div class="admin-filter-grid">
                <label>
                    Buscar
                    <input type="text" name="q" value="<?= h($search) ?>" placeholder="Digite para filtrar">
                </label>
                <label>
                    Status de pagamento
                    <select name="payment_status">
                        <option value="">Todos</option>
                        <?php foreach ($availableStatuses as $status): ?>
                            <option value="<?= h($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= h($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <fieldset class="admin-filter-fields">
                <legend>Campos do filtro</legend>
                <label><input type="checkbox" name="fields[]" value="cpf" <?= in_array('cpf', $selectedFields, true) ? 'checked' : '' ?>> CPF</label>
                <label><input type="checkbox" name="fields[]" value="email" <?= in_array('email', $selectedFields, true) ? 'checked' : '' ?>> E-mail</label>
                <label><input type="checkbox" name="fields[]" value="telefone" <?= in_array('telefone', $selectedFields, true) ? 'checked' : '' ?>> Telefone</label>
                <label><input type="checkbox" name="fields[]" value="payment_status" <?= in_array('payment_status', $selectedFields, true) ? 'checked' : '' ?>> Status do pagamento</label>
            </fieldset>

            <div class="admin-filter-actions">
                <button class="btn btn-primary" type="submit">Filtrar</button>
                <a class="btn btn-secondary" href="admin_dashboard.php">Limpar</a>
                <button class="btn btn-primary" type="button" onclick="window.print()">Imprimir</button>
            </div>
        </form>

        <div class="card table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Categoria</th>
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
                        <td colspan="10">Nenhum inscrito encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registrations as $registration): ?>
                        <tr>
                            <td><?= h((string) $registration['nome']) ?></td>
                            <td><?= h((string) $registration['cpf']) ?></td>
                            <td><?= h((string) $registration['email']) ?></td>
                            <td><?= h((string) $registration['telefone']) ?></td>
                            <td><?= h((string) $registration['categoria']) ?></td>
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
