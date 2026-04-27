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

$inquiryStageLabels = [
    'pending' => 'Awaiting business reply',
    'replied' => 'Business replied',
    'scheduled' => 'Meeting scheduled',
    'closed' => 'Closed',
];

$messageStatusLabels = [
    'open' => 'New update',
    'pending' => 'Awaiting read',
    'reviewed' => 'Reviewed by business',
    'read' => 'Read',
];

$messageDirectionLabels = [
    'sent' => 'You sent',
    'received' => 'Business sent',
];

$pageTitle = 'Messages';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'messages';
$clientUserId = active_client_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    header('Location: ' . url('pages/user/messages.php?sent=1'));
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

$messages = fetch_messages_for_client($clientUserId, 200);
$recipientBusinesses = fetch_business_listings(100, $clientUserId, false);

$openThreadCount = 0;
$uniqueBusinesses = [];
foreach ($messages as $messageRow) {
    $stageKey = strtolower((string) ($messageRow['inquiry_status'] ?? 'pending'));
    if (in_array($stageKey, ['pending', 'replied', 'scheduled'], true)) {
        $openThreadCount += 1;
    }

    $businessName = trim((string) ($messageRow['business_name'] ?? ''));
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
                <article class="metric-card"><small>Total Updates</small><strong data-counter="<?php echo e((string) count($messages)); ?>">0</strong></article>
                <article class="metric-card"><small>Open Threads</small><strong data-counter="<?php echo e((string) $openThreadCount); ?>">0</strong></article>
                <article class="metric-card"><small>Businesses Contacted</small><strong data-counter="<?php echo e((string) $businessesContactedCount); ?>">0</strong></article>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Inquiry Topic</th>
                            <th>Budget Signal</th>
                            <th>Inquiry Stage</th>
                            <th>Direction</th>
                            <th>Latest Message Status</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <?php
                            $messageStatusKey = strtolower((string) ($message['message_status'] ?? 'open'));
                            $messageStatusLabel = $messageStatusLabels[$messageStatusKey] ?? ucfirst($messageStatusKey);
                            $inquiryStageKey = strtolower((string) ($message['inquiry_status'] ?? 'pending'));
                            $inquiryStageLabel = $inquiryStageLabels[$inquiryStageKey] ?? ucfirst($inquiryStageKey);
                            $budgetAmount = (float) ($message['budget_amount'] ?? 0);
                            $budgetLabel = $budgetAmount > 0 ? money($budgetAmount) : 'Not specified';
                            $updatedAt = (string) (($message['inquiry_updated_at'] ?? '') !== '' ? $message['inquiry_updated_at'] : ($message['created_at'] ?? ''));
                            $directionKey = strtolower((string) ($message['message_direction'] ?? 'received'));
                            $directionLabel = $messageDirectionLabels[$directionKey] ?? ucfirst($directionKey);
                            ?>
                            <tr>
                                <td><?php echo e((string) ($message['business_name'] ?? 'Business')); ?></td>
                                <td><?php echo e((string) ($message['inquiry_topic'] ?? 'No inquiry topic')); ?></td>
                                <td><span class="badge <?php echo e($budgetAmount > 0 ? 'badge-success' : 'badge-neutral'); ?>"><?php echo e($budgetLabel); ?></span></td>
                                <td><span class="badge <?php echo e(badge_class_for_status($inquiryStageKey)); ?>"><?php echo e($inquiryStageLabel); ?></span></td>
                                <td><span class="badge <?php echo e($directionKey === 'sent' ? 'badge-neutral' : 'badge-success'); ?>"><?php echo e($directionLabel); ?></span></td>
                                <td><span class="badge <?php echo e(badge_class_for_status($messageStatusKey)); ?>"><?php echo e($messageStatusLabel); ?></span></td>
                                <td><?php echo e(relative_time($updatedAt)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="7">No message threads found yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section class="card section-stack">
                <h3>Send a new message</h3>
                <?php if ($messageStatus === '1'): ?>
                    <div class="notice-item" role="status">Message sent successfully.</div>
                <?php endif; ?>
                <?php if ($messageError !== ''): ?>
                    <div class="notice-item" role="alert"><?php echo e($messageError); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(url('pages/user/messages.php')); ?>" method="POST" data-validate data-allow-submit>
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
                            <textarea id="msg-body" name="message_body" required data-minlength="15"><?php echo e($messageForm['message_body']); ?></textarea>
                            <small class="field-error" data-error-for="message_body"></small>
                        </div>
                    </div>
                    <button class="btn" type="submit">Send Message</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
