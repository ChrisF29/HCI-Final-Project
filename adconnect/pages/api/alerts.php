<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_authenticated()) {
    echo json_encode([
        'ok' => false,
        'alerts' => [],
        'message' => 'Sign in to see personalized alerts.',
    ]);
    exit;
}

$role = strtolower((string) ($_SESSION['role'] ?? 'guest'));
$userId = current_user_id();
$alerts = fetch_alerts_for_role($role, $userId, 8);

echo json_encode([
    'ok' => true,
    'alerts' => $alerts,
]);
