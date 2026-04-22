<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$appName = 'AdConnect';
$appDescription = 'A user-centered advertising and client-matching web platform';
$appEnv = 'development';

$dbConfig = [
    'host' => 'localhost',
    'database' => 'adconnect',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$segments = explode('/', trim($scriptName, '/'));
$baseParts = [];

foreach ($segments as $segment) {
    $baseParts[] = $segment;
    if (strtolower($segment) === 'adconnect') {
        break;
    }
}

$appBasePath = '/' . trim(implode('/', $baseParts), '/');
if (stripos($appBasePath, 'adconnect') === false) {
    $appBasePath = '/adconnect';
}

$simulatedRole = strtolower((string) ($_GET['role'] ?? $_SESSION['role'] ?? 'guest'));
$allowedRoles = ['guest', 'client', 'business', 'admin'];
if (!in_array($simulatedRole, $allowedRoles, true)) {
    $simulatedRole = 'guest';
}
$_SESSION['role'] = $simulatedRole;

function url(string $path = ''): string
{
    global $appBasePath;

    $normalizedPath = ltrim($path, '/');
    if ($normalizedPath === '') {
        return $appBasePath;
    }

    return $appBasePath . '/' . $normalizedPath;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sanitize_input(string $value): string
{
    return trim(strip_tags($value));
}

function has_role(string $role): bool
{
    global $simulatedRole;

    return $simulatedRole === strtolower($role);
}

function db(): ?PDO
{
    global $dbConfig;

    static $pdo = null;
    static $attempted = false;

    if ($attempted) {
        return $pdo;
    }

    $attempted = true;

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        (string) $dbConfig['host'],
        (string) $dbConfig['database'],
        (string) $dbConfig['charset']
    );

    try {
        $pdo = new PDO(
            $dsn,
            (string) $dbConfig['user'],
            (string) $dbConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $exception) {
        $pdo = null;
    }

    return $pdo;
}

function db_available(): bool
{
    return db() instanceof PDO;
}

function db_all(string $sql, array $params = []): array
{
    $connection = db();
    if (!$connection) {
        return [];
    }

    try {
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        $rows = $statement->fetchAll();

        return is_array($rows) ? $rows : [];
    } catch (PDOException $exception) {
        return [];
    }
}

function db_one(string $sql, array $params = []): ?array
{
    $rows = db_all($sql, $params);

    return $rows[0] ?? null;
}

function db_value(string $sql, array $params = []): mixed
{
    $row = db_one($sql, $params);
    if (!$row) {
        return null;
    }

    $values = array_values($row);

    return $values[0] ?? null;
}

function db_count(string $sql, array $params = []): int
{
    return (int) (db_value($sql, $params) ?? 0);
}

function db_execute(string $sql, array $params = []): bool
{
    $connection = db();
    if (!$connection) {
        return false;
    }

    try {
        $statement = $connection->prepare($sql);

        return $statement->execute($params);
    } catch (PDOException $exception) {
        return false;
    }
}

function query_int(string $key): ?int
{
    if (!isset($_GET[$key])) {
        return null;
    }

    $rawValue = (string) $_GET[$key];
    if (!preg_match('/^\d+$/', $rawValue)) {
        return null;
    }

    $value = (int) $rawValue;

    return $value > 0 ? $value : null;
}

function money(float|int|null $amount): string
{
    return 'PHP ' . number_format((float) ($amount ?? 0), 2);
}

function money_compact(float|int|null $amount): string
{
    $value = (float) ($amount ?? 0);

    if ($value >= 1000000) {
        return 'PHP ' . number_format($value / 1000000, 1) . 'M';
    }

    if ($value >= 1000) {
        return 'PHP ' . number_format($value / 1000, 0) . 'K';
    }

    return 'PHP ' . number_format($value, 0);
}

function format_date_label(?string $datetime): string
{
    if (!$datetime) {
        return 'N/A';
    }

    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return 'N/A';
    }

    return date('M j, Y', $timestamp);
}

function relative_time(?string $datetime): string
{
    if (!$datetime) {
        return 'N/A';
    }

    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return 'N/A';
    }

    $delta = time() - $timestamp;
    if ($delta < 60) {
        return 'Just now';
    }
    if ($delta < 3600) {
        $minutes = (int) floor($delta / 60);

        return $minutes . 'm ago';
    }
    if ($delta < 86400) {
        $hours = (int) floor($delta / 3600);

        return $hours . 'h ago';
    }

    $days = (int) floor($delta / 86400);
    if ($days < 7) {
        return $days . 'd ago';
    }

    return format_date_label($datetime);
}

function badge_class_for_status(string $status): string
{
    $normalized = strtolower(trim($status));
    $success = ['live', 'active', 'approved', 'resolved', 'replied', 'open', 'verified', 'strong'];
    $warning = ['review', 'pending', 'investigating', 'monitor'];

    if (in_array($normalized, $success, true)) {
        return 'badge-success';
    }

    if (in_array($normalized, $warning, true)) {
        return 'badge-warning';
    }

    return 'badge-neutral';
}

function pct(int|float $value): int
{
    $normalized = (int) round((float) $value);

    return max(0, min(100, $normalized));
}

require_once __DIR__ . '/data.php';
