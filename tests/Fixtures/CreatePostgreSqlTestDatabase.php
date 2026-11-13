<?php

declare(strict_types=1);

$databaseUrl = (string) (getenv('DATABASE_URL') ?: '');
$url = $databaseUrl !== '' ? parse_url($databaseUrl) : false;

$host = $url !== false ? (string) ($url['host'] ?? '127.0.0.1') : (string) (getenv('DB_HOST') ?: '127.0.0.1');
$port = $url !== false ? (string) ($url['port'] ?? '5432') : (string) (getenv('DB_PORT') ?: '5432');
$database = $url !== false
    ? trim((string) ($url['path'] ?? ''), '/')
    : (string) (getenv('DB_DATABASE') ?: 'bha_help_ladder_test');
$username = $url !== false
    ? rawurldecode((string) ($url['user'] ?? 'postgres'))
    : (string) (getenv('DB_USERNAME') ?: 'postgres');
$password = $url !== false
    ? rawurldecode((string) ($url['pass'] ?? ''))
    : (string) (getenv('DB_PASSWORD') ?: '');

$query = [];
if ($url !== false && isset($url['query'])) {
    parse_str($url['query'], $query);
}

$sslmode = (string) ($query['sslmode'] ?? (getenv('DB_SSLMODE') ?: 'prefer'));
$adminDatabase = (string) (getenv('PG_ADMIN_DATABASE') ?: 'postgres');

if (! preg_match('/^[A-Za-z0-9_]+_test$/', $database)) {
    fwrite(STDERR, "Refusing to create a database whose name does not end in _test.\n");
    exit(1);
}

$dsn = "pgsql:host={$host};port={$port};dbname={$adminDatabase};sslmode={$sslmode}";

try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $exception) {
    fwrite(STDERR, "Unable to connect to PostgreSQL to create the isolated test database: {$exception->getMessage()}\n");
    exit(1);
}

$exists = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :database');
$exists->execute(['database' => $database]);

if (! $exists->fetchColumn()) {
    $identifier = '"'.str_replace('"', '""', $database).'"';
    $pdo->exec("CREATE DATABASE {$identifier} ENCODING 'UTF8'");
    fwrite(STDOUT, "Created isolated PostgreSQL test database {$database}.\n");
} else {
    fwrite(STDOUT, "Isolated PostgreSQL test database {$database} already exists.\n");
}
