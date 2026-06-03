<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/helpers.php';
require __DIR__ . '/admin_auth.php';

if (isAdminLoggedIn()) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = null;

if (adminUsername() === '' || adminPasswordHash() === '') {
    $error = 'Configure ADMIN_USERNAME e ADMIN_PASSWORD_HASH no arquivo .env para habilitar o acesso.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === null) {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (attemptAdminLogin($username, $password)) {
        header('Location: admin_dashboard.php');
        exit;
    }

    $error = 'Usuário ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do administrador</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="status-body">
<main class="status-card">
    <h1>Área administrativa</h1>
    <p>Faça login para visualizar os inscritos.</p>

    <?php if ($error !== null): ?>
        <p class="error-block"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post" class="card admin-login-form">
        <label>
            Usuário
            <input type="text" name="username" required>
        </label>

        <label>
            Senha
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
</main>
</body>
</html>
