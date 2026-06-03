<?php

declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if (str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        $length = strlen($value);
        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

const EVENT_PRICE = 10.00;
const EVENT_CURRENCY = 'BRL';
const MERCADOPAGO_API_TIMEOUT = 20;

function appBaseUrl(): string
{
    return rtrim((string) env('APP_URL', 'http://localhost:8000'), '/');
}

function mercadopagoAccessToken(): ?string
{
    return env('MERCADOPAGO_ACCESS_TOKEN');
}

function mercadopagoNotificationUrl(): ?string
{
    return env('MERCADOPAGO_NOTIFICATION_URL');
}

function dbHost(): string
{
    return (string) env('DB_HOST', '127.0.0.1');
}

function dbPort(): string
{
    return (string) env('DB_PORT', '3306');
}

function dbName(): string
{
    return (string) env('DB_NAME', 'semana_enfermagem');
}

function dbUser(): string
{
    return (string) env('DB_USER', 'root');
}

function dbPass(): string
{
    return (string) env('DB_PASS', '');
}

function adminUsername(): string
{
    return (string) env('ADMIN_USERNAME', 'admin');
}

function adminPassword(): string
{
    return (string) env('ADMIN_PASSWORD', 'admin123');
}
