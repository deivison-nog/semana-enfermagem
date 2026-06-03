<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', dbHost(), dbPort(), dbName());

    try {
        $pdo = new PDO($dsn, dbUser(), dbPass(), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        throw new RuntimeException('Não foi possível conectar ao banco de dados. Verifique DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS no arquivo .env.', 0, $exception);
    }

    return $pdo;
}

function createRegistration(array $data): void
{
    $sql = 'INSERT INTO registrations (
                external_reference,
                nome,
                cpf,
                email,
                telefone,
                categoria,
                amount,
                currency,
                payment_status,
                created_at,
                updated_at
            ) VALUES (
                :external_reference,
                :nome,
                :cpf,
                :email,
                :telefone,
                :categoria,
                :amount,
                :currency,
                :payment_status,
                NOW(),
                NOW()
            )';

    $stmt = db()->prepare($sql);
    $stmt->execute([
        ':external_reference' => $data['external_reference'],
        ':nome' => $data['nome'],
        ':cpf' => $data['cpf'],
        ':email' => $data['email'],
        ':telefone' => $data['telefone'],
        ':categoria' => $data['categoria'],
        ':amount' => $data['amount'],
        ':currency' => $data['currency'],
        ':payment_status' => $data['payment_status'],
    ]);
}

function updateRegistrationPaymentStatus(string $externalReference, string $status, ?string $paymentId, ?string $paymentDate): void
{
    $sql = 'UPDATE registrations
            SET payment_status = :payment_status,
                payment_id = CASE WHEN :payment_id = "" THEN payment_id ELSE :payment_id END,
                payment_date = :payment_date,
                updated_at = NOW()
            WHERE external_reference = :external_reference';

    $stmt = db()->prepare($sql);
    $stmt->execute([
        ':payment_status' => $status,
        ':payment_id' => $paymentId ?? '',
        ':payment_date' => $paymentDate,
        ':external_reference' => $externalReference,
    ]);
}

function fetchRegistrations(): array
{
    $sql = 'SELECT id,
                   external_reference,
                   nome,
                   cpf,
                   email,
                   telefone,
                   categoria,
                   amount,
                   currency,
                   payment_status,
                   payment_id,
                   payment_date,
                   created_at,
                   updated_at
            FROM registrations
            ORDER BY created_at DESC';

    return db()->query($sql)->fetchAll();
}
