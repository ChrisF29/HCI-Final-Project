<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_authenticated()) {
    echo json_encode([
        'ok' => false,
        'messages' => [],
        'message' => 'Sign in to view messages.',
    ]);
    exit;
}

$inquiryId = query_int('inquiry_id');
if ($inquiryId === null || $inquiryId <= 0) {
    echo json_encode([
        'ok' => false,
        'messages' => [],
        'message' => 'Conversation not found.',
    ]);
    exit;
}

$role = strtolower((string) ($_SESSION['role'] ?? 'guest'));
$userId = current_user_id();
$rows = [];

if ($role === 'client' && $userId !== null) {
    $rows = fetch_conversation_messages_for_client($userId, $inquiryId, 200);
} elseif ($role === 'business' && $userId !== null) {
    $businessId = active_business_profile_id();
    if ($businessId !== null) {
        $rows = fetch_conversation_messages_for_business($businessId, $inquiryId, 200);
    }
} else {
    echo json_encode([
        'ok' => false,
        'messages' => [],
        'message' => 'Conversation not available for this account.',
    ]);
    exit;
}

$messages = [];
foreach ($rows as $row) {
    $senderId = (int) ($row['sender_user_id'] ?? 0);
    $isSent = $userId !== null && $senderId === $userId;

    $senderLabel = 'User';
    if ($role === 'client') {
        $senderLabel = $isSent ? 'You' : (string) ($row['business_name'] ?? 'Business');
    } elseif ($role === 'business') {
        $senderLabel = $isSent ? 'You' : (string) ($row['client_name'] ?? 'Client');
    }

    $createdAt = (string) ($row['created_at'] ?? '');

    $messages[] = [
        'id' => (int) ($row['id'] ?? 0),
        'body' => (string) ($row['body'] ?? ''),
        'senderLabel' => $senderLabel,
        'isSent' => $isSent,
        'createdAt' => $createdAt,
        'relativeTime' => relative_time($createdAt),
    ];
}

echo json_encode([
    'ok' => true,
    'messages' => $messages,
]);
