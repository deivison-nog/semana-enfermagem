<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function attemptAdminLogin(string $username, string $password): bool
{
    $validUser = adminUsername();
    $validPasswordHash = adminPasswordHash();

    if ($validUser === '' || $validPasswordHash === '') {
        return false;
    }

    $userMatch = hash_equals($validUser, $username);
    $passMatch = password_verify($password, $validPasswordHash);

    if (!$userMatch || !$passMatch) {
        return false;
    }

    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $validUser;

    return true;
}

function requireAdminLogin(): void
{
    if (isAdminLoggedIn()) {
        return;
    }

    header('Location: admin_login.php');
    exit;
}

function logoutAdmin(): void
{
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_username']);
}
