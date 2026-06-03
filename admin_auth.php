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
    $validPass = adminPassword();

    $userMatch = hash_equals($validUser, $username);
    $passMatch = hash_equals($validPass, $password);

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
