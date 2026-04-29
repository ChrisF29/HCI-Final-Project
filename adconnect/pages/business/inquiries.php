<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$replyError = '';
$replyStatus = (string) ($_GET['reply'] ?? '');
$replyForm = [
    'inquiry_id' => '',
    'reply_subject' => '',
    'reply_message' => '',
];

$showChatModal = false;

$pageTitle = 'Inquiries';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'inquiries';
$businessId = active_business_profile_id();
$businessUserId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $replyForm['inquiry_id'] = trim((string) ($_POST['inquiry_id'] ?? ''));
    $replyForm['reply_subject'] = trim((string) ($_POST['reply_subject'] ?? ''));
    $replyForm['reply_message'] = trim((string) ($_POST['reply_message'] ?? ''));

    $senderUserId = current_user_id();
    $inquiryId = ctype_digit($replyForm['inquiry_id']) ? (int) $replyForm['inquiry_id'] : 0;

    if ($businessId === null) {
        $replyError = 'Business profile was not found.';
    } elseif ($inquiryId <= 0 || $replyForm['reply_message'] === '') {
        $replyError = 'Please write a reply message before sending.';
    } elseif ($senderUserId === null) {
        $replyError = 'You must be signed in to reply to inquiries.';
    } elseif (!db_available()) {
        $replyError = 'Database is unavailable right now. Please try again.';
    } else {
        $inquiryRow = db_one(
            'SELECT client_user_id, latest_subject, campaign_need FROM inquiries WHERE id = :id AND business_id = :business_id LIMIT 1',
            ['id' => $inquiryId, 'business_id' => $businessId]
        );

        $recipientUserId = (int) ($inquiryRow['client_user_id'] ?? 0);

        if ($recipientUserId <= 0) {
            $replyError = 'Selected inquiry was not found.';
        } else {
            $subject = trim((string) $replyForm['reply_subject']);
            if ($subject === '') {
                $subject = trim((string) ($inquiryRow['latest_subject'] ?? ''));
            }
            if ($subject === '') {
                $subject = trim((string) ($inquiryRow['campaign_need'] ?? ''));
            }
            if ($subject === '') {
                $subject = 'Message update';
            }

            $connection = db();
            if (!$connection) {
                $replyError = 'Unable to send reply right now.';
            } else {
                try {
                    $connection->beginTransaction();

                    $messageStatement = $connection->prepare(
                        'INSERT INTO messages (
                            inquiry_id,
                            sender_user_id,
                            recipient_user_id,
                            subject,
                            body,
                            message_status
                        ) VALUES (
                            :inquiry_id,
                            :sender_user_id,
                            :recipient_user_id,
                            :subject,
                            :body,
                            :message_status
                        )'
                    );

                    $messageStatement->execute([
                        'inquiry_id' => $inquiryId,
                        'sender_user_id' => $senderUserId,
                        'recipient_user_id' => $recipientUserId,
                        'subject' => $subject,
                        'body' => $replyForm['reply_message'],
                        'message_status' => 'reviewed',
                    ]);

                    $updateInquiryStatement = $connection->prepare(
                        'UPDATE inquiries
                         SET status = :status,
                             latest_subject = :latest_subject,
                             latest_message = :latest_message,
                             updated_at = CURRENT_TIMESTAMP
                         WHERE id = :id'
                    );

                    $updateInquiryStatement->execute([
                        'status' => 'replied',
                        'latest_subject' => $subject,
                        'latest_message' => $replyForm['reply_message'],
                        'id' => $inquiryId,
                    ]);

                    $connection->commit();
                    header('Location: ' . url('pages/business/inquiries.php?inquiry_id=' . $inquiryId . '&reply=sent&open=1#chat-modal'));
                    exit;
                } catch (Throwable $exception) {
                    if ($connection->inTransaction()) {
                        $connection->rollBack();
                    }

                    $replyError = 'Unable to send reply right now. Please try again.';
                }
            }
        }
    }
}

$inquiries = fetch_inquiries_for_business($businessId, 200);

$activeInquiryId = query_int('inquiry_id');
if ($activeInquiryId === null && !empty($inquiries)) {
    $activeInquiryId = (int) ($inquiries[0]['id'] ?? 0);
}

$activeInquiry = null;
foreach ($inquiries as $inquiryRow) {
    if ((int) ($inquiryRow['id'] ?? 0) === $activeInquiryId) {
        $activeInquiry = $inquiryRow;
        break;
    }
}

$conversationMessages = $activeInquiryId ? fetch_conversation_messages_for_business($businessId, $activeInquiryId, 200) : [];

$openParam = (string) ($_GET['open'] ?? '');
$showChatModal = $activeInquiry !== null && ($openParam === '1' || $replyStatus === 'sent' || $replyError !== '');

$activeSubject = '';
if ($activeInquiry !== null) {
    $activeSubject = trim((string) ($activeInquiry['latest_subject'] ?? ''));
    if ($activeSubject === '') {
        $activeSubject = trim((string) ($activeInquiry['campaign_need'] ?? ''));
    }
    if ($activeSubject === '') {
        $activeSubject = 'Message update';
    }
}

if ($replyForm['inquiry_id'] === '' && $activeInquiryId !== null) {
    $replyForm['inquiry_id'] = (string) $activeInquiryId;
}

$inquiryStatusMeta = [
    'pending' => 'Awaiting your reply',
    'replied' => 'Business replied',
    'scheduled' => 'Meeting scheduled',
    'closed' => 'Closed',
];

$statusCounts = [
    'pending' => 0,
    'replied' => 0,
    'scheduled' => 0,
    'closed' => 0,
];

foreach ($inquiries as $inquiryRow) {
    $statusKey = strtolower((string) ($inquiryRow['status'] ?? 'pending'));
    if (!array_key_exists($statusKey, $statusCounts)) {
        continue;
    }

    $statusCounts[$statusKey] += 1;
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container content-grid">
        <?php require dirname(__DIR__, 2) . '/includes/sidebar.php'; ?>

        <div class="section-stack">
            <section class="page-hero">
                <nav class="breadcrumbs" aria-label="Breadcrumb">
                    <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                    <span>/</span>
                    <a href="<?php echo e(url('pages/business/dashboard.php?role=business')); ?>">Business Dashboard</a>
                    <span>/</span>
                    <span>Inquiries</span>
                </nav>
                <h1>Client inquiries</h1>
                <p>Track inquiry stage and budget signals clearly so your team can prioritize replies faster.</p>
                <div class="hero-actions">
                    <?php if ($activeInquiryId !== null && $activeInquiryId > 0): ?>
                        <a class="btn" href="<?php echo e(url('pages/business/inquiries.php?inquiry_id=' . $activeInquiryId . '&open=1#chat-modal')); ?>">Open latest conversation</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="metrics">
                <article class="metric-card"><small>Total</small><strong data-counter="<?php echo e((string) count($inquiries)); ?>">0</strong></article>
                <article class="metric-card"><small>Awaiting Reply</small><strong data-counter="<?php echo e((string) ($statusCounts['pending'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Replied</small><strong data-counter="<?php echo e((string) ($statusCounts['replied'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Scheduled / Closed</small><strong data-counter="<?php echo e((string) (($statusCounts['scheduled'] ?? 0) + ($statusCounts['closed'] ?? 0))); ?>">0</strong></article>
            </section>

            <section class="card section-stack">
                <div class="inline-split">
                    <h3>Conversations</h3>
                    <?php if ($activeInquiryId !== null && $activeInquiryId > 0): ?>
                        <a class="btn-ghost" href="<?php echo e(url('pages/business/inquiries.php?inquiry_id=' . $activeInquiryId . '&open=1#chat-modal')); ?>">Open latest</a>
                    <?php endif; ?>
                </div>
                <div class="conversation-list">
                    <?php foreach ($inquiries as $inquiry): ?>
                        <?php
                        $inquiryId = (int) ($inquiry['id'] ?? 0);
                        $inquiryStatusKey = strtolower((string) ($inquiry['status'] ?? 'pending'));
                        $inquiryStatusLabel = $inquiryStatusMeta[$inquiryStatusKey] ?? ucfirst($inquiryStatusKey);
                        $budgetAmount = (float) ($inquiry['budget_amount'] ?? 0);
                        $budgetLabel = $budgetAmount > 0 ? money($budgetAmount) : 'Not specified';
                        $previewText = trim((string) ($inquiry['latest_message'] ?? ''));
                        if ($previewText === '') {
                            $previewText = trim((string) ($inquiry['latest_subject'] ?? ''));
                        }
                        if ($previewText === '') {
                            $previewText = trim((string) ($inquiry['campaign_need'] ?? ''));
                        }
                        if ($previewText === '') {
                            $previewText = 'No messages yet.';
                        }
                        $previewSnippet = substr($previewText, 0, 90);
                        $isActive = $activeInquiryId === $inquiryId;
                        ?>
                        <a class="conversation-item conversation-item--compact <?php echo $isActive ? 'is-active' : ''; ?>" href="<?php echo e(url('pages/business/inquiries.php?inquiry_id=' . $inquiryId . '&open=1#chat-modal')); ?>">
                            <div class="conversation-top">
                                <strong><?php echo e((string) ($inquiry['client_name'] ?? 'Client')); ?></strong>
                                <span class="badge <?php echo e(badge_class_for_status($inquiryStatusKey)); ?>"><?php echo e($inquiryStatusLabel); ?></span>
                            </div>
                            <p><?php echo e($previewSnippet); ?></p>
                            <div class="conversation-meta">
                                <span><?php echo e($budgetLabel); ?></span>
                                <span><?php echo e(relative_time((string) ($inquiry['updated_at'] ?? ''))); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($inquiries)): ?>
                        <div class="notice-item">No inquiries found.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card" data-visible-for="business,admin">
                <h3>Reminder</h3>
                <p>Reply within 4 hours for optimal visibility ranking in the directory.</p>
            </section>
        </div>
    </div>
</main>

<div class="modal <?php echo $showChatModal ? 'is-open' : ''; ?>" data-modal="chat-modal" data-chat-endpoint="<?php echo e(url('pages/api/chat-messages.php')); ?>" data-chat-inquiry-id="<?php echo e((string) $activeInquiryId); ?>" data-chat-meta-separator=" | " aria-hidden="<?php echo $showChatModal ? 'false' : 'true'; ?>">
    <div class="modal-card modal-chat">
        <div class="modal-head">
            <h3>Conversation</h3>
            <button class="btn-ghost" type="button" data-modal-close>Close</button>
        </div>
        <?php if ($activeInquiry): ?>
            <?php
            $activeStatusKey = strtolower((string) ($activeInquiry['status'] ?? 'pending'));
            $activeStatusLabel = $inquiryStatusMeta[$activeStatusKey] ?? ucfirst($activeStatusKey);
            $activeBudgetAmount = (float) ($activeInquiry['budget_amount'] ?? 0);
            $activeBudgetLabel = $activeBudgetAmount > 0 ? money($activeBudgetAmount) : 'Not specified';
            $activeClientName = (string) ($activeInquiry['client_name'] ?? 'Client');
            ?>
            <div class="inline-split">
                <div>
                    <h3><?php echo e($activeClientName); ?></h3>
                    <small>Budget: <?php echo e($activeBudgetLabel); ?> | Stage: <?php echo e($activeStatusLabel); ?></small>
                </div>
                <span class="badge <?php echo e(badge_class_for_status($activeStatusKey)); ?>"><?php echo e($activeStatusLabel); ?></span>
            </div>

            <div class="chat-thread" data-chat-thread>
                <?php foreach ($conversationMessages as $chatMessage): ?>
                    <?php
                    $isSent = (int) ($chatMessage['sender_user_id'] ?? 0) === (int) $businessUserId;
                    $bubbleClass = $isSent ? 'is-sent' : 'is-received';
                    $senderLabel = $isSent ? 'You' : $activeClientName;
                    ?>
                    <article class="chat-bubble <?php echo e($bubbleClass); ?>">
                        <p><?php echo e((string) ($chatMessage['body'] ?? '')); ?></p>
                        <div class="chat-meta">
                            <?php echo e($senderLabel); ?> | <?php echo e(relative_time((string) ($chatMessage['created_at'] ?? ''))); ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (empty($conversationMessages)): ?>
                    <div class="notice-item">No messages yet. Start the conversation below.</div>
                <?php endif; ?>
            </div>

            <?php if ($replyStatus === 'sent'): ?>
                <div class="notice-item" role="status">Reply sent successfully.</div>
            <?php endif; ?>
            <?php if ($replyError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($replyError); ?></div>
            <?php endif; ?>

            <form action="<?php echo e(url('pages/business/inquiries.php')); ?>#chat-modal" method="POST" data-validate data-allow-submit class="chat-input">
                <input type="hidden" name="inquiry_id" value="<?php echo e($replyForm['inquiry_id']); ?>">
                <input type="hidden" name="reply_subject" value="<?php echo e($replyForm['reply_subject'] !== '' ? $replyForm['reply_subject'] : $activeSubject); ?>">
                <div class="form-field">
                    <label for="reply-message">Message</label>
                    <textarea id="reply-message" name="reply_message" required><?php echo e($replyForm['reply_message']); ?></textarea>
                    <small class="field-error" data-error-for="reply_message"></small>
                </div>
                <button class="btn" type="submit">Send Reply</button>
            </form>
        <?php else: ?>
            <div class="notice-item">Select an inquiry to view the conversation.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
