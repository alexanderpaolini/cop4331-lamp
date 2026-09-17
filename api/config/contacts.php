<?php
declare(strict_types=1);

function loadContactsEnv(): void
{
    $path = dirname(__DIR__, 2) . '/.env';

    if (!is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");

        if (getenv($name) === false) {
            putenv("{$name}={$value}");
        }
    }
}

loadContactsEnv();

function apiResponse(int $status, array $body): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body);
    exit;
}

function requirePost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        apiResponse(405, ['error' => 'Method not allowed']);
    }
}

function jsonBody(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        apiResponse(400, ['error' => 'A JSON request body is required']);
    }

    try {
        $body = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        apiResponse(400, ['error' => 'Invalid JSON request body']);
    }

    if (!is_array($body)) {
        apiResponse(400, ['error' => 'The JSON request body must be an object']);
    }

    return $body;
}

function contactsDb(): PDO
{
    static $db = null;

    if ($db instanceof PDO) {
        return $db;
    }

    $host = getenv('CONTACTS_DB_HOST') ?: 'localhost';
    $port = getenv('CONTACTS_DB_PORT') ?: '3306';
    $name = getenv('CONTACTS_DB_NAME') ?: 'ContactsAppDB';
    $user = getenv('CONTACTS_DB_USER') ?: '';
    $pass = getenv('CONTACTS_DB_PASSWORD') ?: '';

    try {
        $db = new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $exception) {
        apiResponse(500, ['error' => 'Database connection failed']);
    }

    return $db;
}
