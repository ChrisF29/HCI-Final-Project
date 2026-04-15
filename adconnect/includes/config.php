<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$appName = 'AdConnect';
$appDescription = 'A user-centered advertising and client-matching web platform';
$appEnv = 'development';

// Placeholder values for future MySQL connection logic.
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

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Placeholder to signal where strict server-side sanitization should happen.
function sanitize_input_placeholder(string $value): string
{
    return trim($value);
}

function has_role(string $role): bool
{
    global $simulatedRole;

    return $simulatedRole === strtolower($role);
}
