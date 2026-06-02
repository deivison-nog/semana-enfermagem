<?php

declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function old(string $field, string $default = ''): string
{
    return $_SESSION['old'][$field] ?? $default;
}

function setFormState(array $old, array $errors): void
{
    $_SESSION['old'] = $old;
    $_SESSION['errors'] = $errors;
}

function pullErrors(): array
{
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);

    return is_array($errors) ? $errors : [];
}

function pullSuccessMessage(): ?string
{
    $message = $_SESSION['success'] ?? null;
    unset($_SESSION['success']);

    return is_string($message) ? $message : null;
}

function clearOldInputs(): void
{
    unset($_SESSION['old']);
}

function saveRegistration(array $registration): void
{
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $line = json_encode($registration, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($line === false) {
        return;
    }

    file_put_contents($dir . '/registrations.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function allowedCategories(): array
{
    return [
        'Enfermeiro(a)',
        'Técnico(a) de Enfermagem',
        'Auxiliar de Enfermagem',
        'Acadêmico(a)',
    ];
}
