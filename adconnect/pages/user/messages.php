<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$messageError = '';
$messageStatus = (string) ($_GET['sent'] ?? '');
$messageForm = [
    'recipient' => '',
    'subject' => '',
    'message_body' => '',
    'budget_amount' => '',
];

$chatError = '';
$chatStatus = (string) ($_GET['chat'] ?? '');
$chatForm = [
    'inquiry_id' => '',
    'message_body' => '',
];

$inquiryStageLabels = [
    'pending' => 'Awaiting business reply',
    'replied' => 'Business replied',
    'scheduled' => 'Meeting scheduled',
    'closed' => 'Closed',
];

$pageTitle = 'Messages';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'messages';
$clientUserId = current_user_id();
if ($clientUserId === null) {
    $clientUserId = active_client_user_id();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formContext = strtolower(trim((string) ($_POST['form_context'] ?? 'new')));

    if ($formContext === 'chat') {
        $chatForm['inquiry_id'] = trim((string) ($_POST['inquiry_id'] ?? ''));
        $chatForm['message_body'] = trim((string) ($_POST['message_body'] ?? ''));

        $senderUserId = current_user_id();
        $inquiryId = ctype_digit($chatForm['inquiry_id']) ? (int) $chatForm['inquiry_id'] : 0;

        if ($inquiryId <= 0 || $chatForm['message_body'] === '') {
            $chatError = 'Please write a message before sending.';
        } elseif ($senderUserId === null) {
            $chatError = 'You must be signed in to send a message.';
        } elseif (!db_available()) {
            $chatError = 'Database is unavailable right now. Please try again.';
        } else {
            $inquiryRow = db_one(
                'SELECT business_id, client_user_id, latest_subject, campaign_need FROM inquiries WHERE id = :id LIMIT 1',
                ['id' => $inquiryId]
            );

            if (!$inquiryRow || (int) ($inquiryRow['client_user_id'] ?? 0) !== $senderUserId) {
                $chatError = 'Selected conversation was not found.';
            } else {
                $businessId = (int) ($inquiryRow['business_id'] ?? 0);
                $subject = trim((string) ($inquiryRow['latest_subject'] ?? ''));
                if ($subject === '') {
                    $subject = trim((string) ($inquiryRow['campaign_need'] ?? ''));
                }
                if ($subject === '') {
                    $subject = 'Message update';
                }

                $businessUserId = (int) (db_value(
                    'SELECT user_id FROM business_profiles WHERE id = :business_id LIMIT 1',
                    ['business_id' => $businessId]
                ) ?? 0);

                if ($businessUserId <= 0) {
                    $chatError = 'Selected business was not found.';
                } else {
                    $connection = db();
                    if (!$connection) {
                        $chatError = 'Unable to send message right now.';
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
                                'recipient_user_id' => $businessUserId,
                                'subject' => $subject,
                                'body' => $chatForm['message_body'],
                                'message_status' => 'open',
                            ]);

                            $updateInquiryStatement = $connection->prepare(
                                'UPDATE inquiries
                                 SET latest_subject = :latest_subject,
                                     latest_message = :latest_message,
                                     status = :status,
                                     updated_at = CURRENT_TIMESTAMP
                                 WHERE id = :id'
                            );

                            $updateInquiryStatement->execute([
                                'latest_subject' => $subject,
                                'latest_message' => $chatForm['message_body'],
                                'status' => 'pending',
                                'id' => $inquiryId,
                            ]);

                            $connection->commit();
                            header('Location: ' . url('pages/user/messages.php?conversation_id=' . $inquiryId . '&chat=sent&open=1#chat-modal'));
                            exit;
                        } catch (Throwable $exception) {
                            if ($connection->inTransaction()) {
                                $connection->rollBack();
                            }

                            $chatError = 'Unable to send message right now. Please try again.';
                        }
                    }
                }
            }
        }
    } else {
        $messageForm['recipient'] = trim((string) ($_POST['recipient'] ?? ''));
        $messageForm['subject'] = trim((string) ($_POST['subject'] ?? ''));
        $messageForm['message_body'] = trim((string) ($_POST['message_body'] ?? ''));
        $messageForm['budget_amount'] = trim((string) ($_POST['budget_amount'] ?? ''));

        $senderUserId = current_user_id();
        $businessId = ctype_digit($messageForm['recipient']) ? (int) $messageForm['recipient'] : 0;
        $budgetAmount = (float) preg_replace('/[^0-9.]/', '', $messageForm['budget_amount']);
        $normalizedBudget = $budgetAmount > 0 ? $budgetAmount : null;

        if ($businessId <= 0 || $messageForm['subject'] === '' || $messageForm['message_body'] === '') {
            $messageError = 'Please complete all message fields.';
        } elseif ($senderUserId === null) {
            $messageError = 'You must be signed in to send a message.';
        } elseif (!db_available()) {
            $messageError = 'Database is unavailable right now. Please try again.';
        } else {
            $businessUserId = (int) (db_value('SELECT user_id FROM business_profiles WHERE id = :business_id LIMIT 1', ['business_id' => $businessId]) ?? 0);

            if ($businessUserId <= 0) {
                $messageError = 'Selected business was not found.';
            } else {
                $connection = db();
                if (!$connection) {
                    $messageError = 'Unable to send message right now.';
                } else {
                    try {
                        $connection->beginTransaction();

                        $inquiryId = (int) (db_value(
                            'SELECT id FROM inquiries WHERE client_user_id = :client_user_id AND business_id = :business_id ORDER BY updated_at DESC, id DESC LIMIT 1',
                            ['client_user_id' => $senderUserId, 'business_id' => $businessId]
                        ) ?? 0);

                        if ($inquiryId <= 0) {
                            $inquiryStatement = $connection->prepare(
                                'INSERT INTO inquiries (
                                    client_user_id,
                                    business_id,
                                    campaign_need,
                                    budget_amount,
                                    status,
                                    latest_subject,
                                    latest_message
                                ) VALUES (
                                    :client_user_id,
                                    :business_id,
                                    :campaign_need,
                                    :budget_amount,
                                    :status,
                                    :latest_subject,
                                    :latest_message
                                )'
                            );

                            $inquiryStatement->execute([
                                'client_user_id' => $senderUserId,
                                'business_id' => $businessId,
                                'campaign_need' => substr($messageForm['subject'], 0, 200),
                                'budget_amount' => $normalizedBudget,
                                'status' => 'pending',
                                'latest_subject' => $messageForm['subject'],
                                'latest_message' => $messageForm['message_body'],
                            ]);

                            $inquiryId = (int) $connection->lastInsertId();
                        }

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
                            'recipient_user_id' => $businessUserId,
                            'subject' => $messageForm['subject'],
                            'body' => $messageForm['message_body'],
                            'message_status' => 'open',
                        ]);

                        $updateInquiryStatement = $connection->prepare(
                            'UPDATE inquiries
                             SET latest_subject = :latest_subject,
                                 latest_message = :latest_message,
                                 budget_amount = COALESCE(:budget_amount, budget_amount),
                                 status = :status,
                                 updated_at = CURRENT_TIMESTAMP
                             WHERE id = :id'
                        );

                        $updateInquiryStatement->execute([
                            'latest_subject' => $messageForm['subject'],
                            'latest_message' => $messageForm['message_body'],
                            'budget_amount' => $normalizedBudget,
                            'status' => 'pending',
                            'id' => $inquiryId,
                        ]);

                        $connection->commit();
                        header('Location: ' . url('pages/user/messages.php?conversation_id=' . $inquiryId . '&sent=1&open=1#chat-modal'));
                        exit;
                    } catch (Throwable $exception) {
                        if ($connection->inTransaction()) {
                            $connection->rollBack();
                        }

                        $messageError = 'Unable to send message right now. Please try again.';
                    }
                }
            }
        }
    }
}

$conversations = fetch_conversations_for_client($clientUserId, 200);
$recipientBusinesses = fetch_business_listings(100, $clientUserId, false);

$activeConversationId = query_int('conversation_id');
if ($activeConversationId === null && !empty($conversations)) {
    $activeConversationId = (int) ($conversations[0]['inquiry_id'] ?? 0);
}

$activeConversation = null;
foreach ($conversations as $conversation) {
    if ((int) ($conversation['inquiry_id'] ?? 0) === $activeConversationId) {
        $activeConversation = $conversation;
        break;
    }
}

$conversationMessages = $activeConversationId ? fetch_conversation_messages_for_client($clientUserId, $activeConversationId, 200) : [];

$openParam = (string) ($_GET['open'] ?? '');
$showChatModal = $activeConversation !== null && ($openParam === '1' || $chatError !== '' || $chatStatus === 'sent');

$openThreadCount = 0;
$uniqueBusinesses = [];
foreach ($conversations as $conversationRow) {
    $stageKey = strtolower((string) ($conversationRow['status'] ?? 'pending'));
    if (in_array($stageKey, ['pending', 'replied', 'scheduled'], true)) {
        $openThreadCount += 1;
    }

    $businessName = trim((string) ($conversationRow['business_name'] ?? ''));
    if ($businessName !== '') {
        $uniqueBusinesses[$businessName] = true;
    }
}

$businessesContactedCount = count($uniqueBusinesses);

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
                    <a href="<?php echo e(url('pages/user/dashboard.php?role=client')); ?>">Client Dashboard</a>
                    <span>/</span>
                    <span>Messages</span>
                </nav>
                <h1>Message center</h1>
                <p>Track conversation stage, budget context, and business updates in one place.</p>
            </section>

            <section class="metrics">
                <article class="metric-card"><small>Total Conversations</small><strong data-counter="<?php echo e((string) count($conversations)); ?>">0</strong></article>
                <article class="metric-card"><small>Open Threads</small><strong data-counter="<?php echo e((string) $openThreadCount); ?>">0</strong></article>
                <article class="metric-card"><small>Businesses Contacted</small><strong data-counter="<?php echo e((string) $businessesContactedCount); ?>">0</strong></article>
            </section>

            <section class="card section-stack">
                <div class="inline-split">
                    <h3>Conversations</h3>
                    <a class="btn-ghost" href="#new-conversation">New message</a>
                </div>
                <div class="conversation-list">
                    <?php foreach ($conversations as $conversation): ?>
                        <?php
                        $conversationId = (int) ($conversation['inquiry_id'] ?? 0);
                        $conversationStatus = strtolower((string) ($conversation['status'] ?? 'pending'));
                        $conversationLabel = $inquiryStageLabels[$conversationStatus] ?? ucfirst($conversationStatus);
                        $conversationBudget = (float) ($conversation['budget_amount'] ?? 0);
                        $budgetLabel = $conversationBudget > 0 ? money($conversationBudget) : 'Not specified';
                        $previewText = trim((string) ($conversation['latest_message'] ?? ''));
                        if ($previewText === '') {
                            $previewText = trim((string) ($conversation['latest_subject'] ?? ''));
                        }
                        if ($previewText === '') {
                            $previewText = 'No messages yet.';
                        }
                        $previewSnippet = substr($previewText, 0, 90);
                        $isActive = $activeConversationId === $conversationId;
                        ?>
                        <a class="conversation-item conversation-item--compact <?php echo $isActive ? 'is-active' : ''; ?>" href="<?php echo e(url('pages/user/messages.php?conversation_id=' . $conversationId . '&open=1#chat-modal')); ?>">
                            <div class="conversation-top">
                                <strong><?php echo e((string) ($conversation['business_name'] ?? 'Business')); ?></strong>
                                <span class="badge <?php echo e(badge_class_for_status($conversationStatus)); ?>"><?php echo e($conversationLabel); ?></span>
                            </div>
                            <p><?php echo e($previewSnippet); ?></p>
                            <div class="conversation-meta">
                                <span><?php echo e($budgetLabel); ?></span>
                                <span><?php echo e(relative_time((string) ($conversation['updated_at'] ?? ''))); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($conversations)): ?>
                        <div class="notice-item">No conversations yet. Start a new message to connect with a business.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section id="new-conversation" class="card section-stack">
                <h3>Start a new conversation</h3>
                <?php if ($messageStatus === '1'): ?>
                    <div class="notice-item" role="status">Message sent successfully.</div>
                <?php endif; ?>
                <?php if ($messageError !== ''): ?>
                    <div class="notice-item" role="alert"><?php echo e($messageError); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(url('pages/user/messages.php')); ?>#new-conversation" method="POST" data-validate data-allow-submit>
                    <input type="hidden" name="form_context" value="new">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="msg-recipient">Recipient</label>
                            <select id="msg-recipient" name="recipient" required>
                                <option value="">Choose business</option>
                                <?php foreach ($recipientBusinesses as $business): ?>
                                    <?php $businessIdOption = (string) ($business['id'] ?? ''); ?>
                                    <option value="<?php echo e($businessIdOption); ?>" <?php echo $messageForm['recipient'] === $businessIdOption ? 'selected' : ''; ?>><?php echo e((string) ($business['business_name'] ?? 'Business')); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="field-error" data-error-for="recipient"></small>
                        </div>
                        <div class="form-field">
                            <label for="msg-subject">Subject</label>
                            <input id="msg-subject" name="subject" value="<?php echo e($messageForm['subject']); ?>" required>
                            <small class="field-error" data-error-for="subject"></small>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="msg-budget">Budget (optional)</label>
                            <input id="msg-budget" name="budget_amount" value="<?php echo e($messageForm['budget_amount']); ?>" placeholder="e.g. 50000">
                            <small>Used as budget signal in your inquiry thread.</small>
                        </div>
                    </div>
                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="msg-body">Message</label>
                            <textarea id="msg-body" name="message_body" required><?php echo e($messageForm['message_body']); ?></textarea>
                            <small class="field-error" data-error-for="message_body"></small>
                        </div>
                    </div>
                    <button class="btn" type="submit">Send Message</button>
                </form>
            </section>
        </div>
    </div>
</main>

<div class="modal <?php echo $showChatModal ? 'is-open' : ''; ?>" data-modal="chat-modal" data-chat-endpoint="<?php echo e(url('pages/api/chat-messages.php')); ?>" data-chat-inquiry-id="<?php echo e((string) $activeConversationId); ?>" data-chat-meta-separator=" · " aria-hidden="<?php echo $showChatModal ? 'false' : 'true'; ?>">
    <div class="modal-card modal-chat">
        <div class="modal-head">
            <h3>Conversation</h3>
            <button class="btn-ghost" type="button" data-modal-close>Close</button>
        </div>
        <?php if ($activeConversation): ?>
            <?php
            $activeStatus = strtolower((string) ($activeConversation['status'] ?? 'pending'));
            $activeStatusLabel = $inquiryStageLabels[$activeStatus] ?? ucfirst($activeStatus);
            $activeBudgetAmount = (float) ($activeConversation['budget_amount'] ?? 0);
            $activeBudgetLabel = $activeBudgetAmount > 0 ? money($activeBudgetAmount) : 'Not specified';
            ?>
            <div class="inline-split">
                <div>
                    <h3><?php echo e((string) ($activeConversation['business_name'] ?? 'Business')); ?></h3>
                    <small>Budget: <?php echo e($activeBudgetLabel); ?> · Stage: <?php echo e($activeStatusLabel); ?></small>
                </div>
                <span class="badge <?php echo e(badge_class_for_status($activeStatus)); ?>"><?php echo e($activeStatusLabel); ?></span>
            </div>

            <div class="chat-thread" data-chat-thread>
                <?php foreach ($conversationMessages as $chatMessage): ?>
                    <?php
                    $isSent = (int) ($chatMessage['sender_user_id'] ?? 0) === (int) $clientUserId;
                    $bubbleClass = $isSent ? 'is-sent' : 'is-received';
                    $senderLabel = $isSent ? 'You' : (string) ($activeConversation['business_name'] ?? 'Business');
                    ?>
                    <article class="chat-bubble <?php echo e($bubbleClass); ?>">
                        <p><?php echo e((string) ($chatMessage['body'] ?? '')); ?></p>
                        <div class="chat-meta">
                            <?php echo e($senderLabel); ?> · <?php echo e(relative_time((string) ($chatMessage['created_at'] ?? ''))); ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (empty($conversationMessages)): ?>
                    <div class="notice-item">No messages yet. Start the conversation below.</div>
                <?php endif; ?>
            </div>

            <?php if ($chatStatus === 'sent'): ?>
                <div class="notice-item" role="status">Message sent.</div>
            <?php endif; ?>
            <?php if ($chatError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($chatError); ?></div>
            <?php endif; ?>

            <form action="<?php echo e(url('pages/user/messages.php')); ?>#chat-modal" method="POST" data-validate data-allow-submit class="chat-input">
                <input type="hidden" name="form_context" value="chat">
                <input type="hidden" name="inquiry_id" value="<?php echo e((string) $activeConversationId); ?>">
                <div class="form-field">
                    <label for="chat-message">Message</label>
                    <textarea id="chat-message" name="message_body" required><?php echo e($chatForm['message_body']); ?></textarea>
                    <small class="field-error" data-error-for="message_body"></small>
                </div>
                <button class="btn" type="submit">Send message</button>
            </form>
        <?php else: ?>
            <div class="notice-item">Select a conversation to view the chat history.</div>
        <?php endif; ?>
    </div>
</div>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
