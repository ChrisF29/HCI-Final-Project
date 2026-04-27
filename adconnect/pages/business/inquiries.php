<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$replyError = '';
$replyStatus = (string) ($_GET['reply'] ?? '');
$replyForm = [
    'inquiry_id' => '',
    'reply_subject' => '',
    'reply_message' => '',
];

$showReplyModal = false;

$pageTitle = 'Inquiries';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'inquiries';
$businessId = active_business_profile_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $replyForm['inquiry_id'] = trim((string) ($_POST['inquiry_id'] ?? ''));
    $replyForm['reply_subject'] = trim((string) ($_POST['reply_subject'] ?? ''));
    $replyForm['reply_message'] = trim((string) ($_POST['reply_message'] ?? ''));

    $senderUserId = current_user_id();
    $inquiryId = ctype_digit($replyForm['inquiry_id']) ? (int) $replyForm['inquiry_id'] : 0;

    if ($businessId === null) {
        $replyError = 'Business profile was not found.';
    } elseif ($inquiryId <= 0 || $replyForm['reply_subject'] === '' || $replyForm['reply_message'] === '') {
        $replyError = 'Please complete all reply fields.';
    } elseif ($senderUserId === null) {
        $replyError = 'You must be signed in to reply to inquiries.';
    } elseif (!db_available()) {
        $replyError = 'Database is unavailable right now. Please try again.';
    } else {
        $recipientUserId = (int) (db_value(
            'SELECT client_user_id FROM inquiries WHERE id = :id AND business_id = :business_id LIMIT 1',
            ['id' => $inquiryId, 'business_id' => $businessId]
        ) ?? 0);

        if ($recipientUserId <= 0) {
            $replyError = 'Selected inquiry was not found.';
        } else {
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
                        'subject' => $replyForm['reply_subject'],
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
                        'latest_subject' => $replyForm['reply_subject'],
                        'latest_message' => $replyForm['reply_message'],
                        'id' => $inquiryId,
                    ]);

                    $connection->commit();
                    header('Location: ' . url('pages/business/inquiries.php?reply=sent'));
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
$showReplyModal = $replyStatus === 'sent' || $replyError !== '';

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
                    <button class="btn" type="button" data-modal-target="reply-modal">Compose Reply</button>
                </div>
            </section>

            <section class="metrics">
                <article class="metric-card"><small>Total</small><strong data-counter="<?php echo e((string) count($inquiries)); ?>">0</strong></article>
                <article class="metric-card"><small>Awaiting Reply</small><strong data-counter="<?php echo e((string) ($statusCounts['pending'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Replied</small><strong data-counter="<?php echo e((string) ($statusCounts['replied'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Scheduled / Closed</small><strong data-counter="<?php echo e((string) (($statusCounts['scheduled'] ?? 0) + ($statusCounts['closed'] ?? 0))); ?>">0</strong></article>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Campaign Need</th>
                            <th>Budget Signal</th>
                            <th>Inquiry Stage</th>
                            <th>Last Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <?php
                            $inquiryStatusKey = strtolower((string) ($inquiry['status'] ?? 'pending'));
                            $inquiryStatusLabel = $inquiryStatusMeta[$inquiryStatusKey] ?? ucfirst($inquiryStatusKey);
                            $budgetAmount = (float) ($inquiry['budget_amount'] ?? 0);
                            $budgetLabel = $budgetAmount > 0 ? money($budgetAmount) : 'Not specified';
                            ?>
                            <tr>
                                <td><?php echo e((string) ($inquiry['client_name'] ?? 'Client')); ?></td>
                                <td><?php echo e((string) ($inquiry['campaign_need'] ?? 'Campaign request')); ?></td>
                                <td>
                                    <span class="badge <?php echo e($budgetAmount > 0 ? 'badge-success' : 'badge-neutral'); ?>"><?php echo e($budgetLabel); ?></span>
                                </td>
                                <td>
                                    <span class="badge <?php echo e(badge_class_for_status($inquiryStatusKey)); ?>"><?php echo e($inquiryStatusLabel); ?></span>
                                </td>
                                <td><?php echo e(relative_time((string) ($inquiry['updated_at'] ?? ''))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($inquiries)): ?>
                            <tr>
                                <td colspan="5">No inquiries found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section class="card" data-visible-for="business,admin">
                <h3>Reminder</h3>
                <p>Reply within 4 hours for optimal visibility ranking in the directory.</p>
            </section>
        </div>
    </div>
</main>

<div class="modal <?php echo $showReplyModal ? 'is-open' : ''; ?>" data-modal="reply-modal" aria-hidden="<?php echo $showReplyModal ? 'false' : 'true'; ?>">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Reply to inquiry</h3>
            <button class="btn-ghost" type="button" data-modal-close>Close</button>
        </div>
        <?php if ($replyStatus === 'sent'): ?>
            <div class="notice-item" role="status">Reply sent successfully.</div>
        <?php endif; ?>
        <?php if ($replyError !== ''): ?>
            <div class="notice-item" role="alert"><?php echo e($replyError); ?></div>
        <?php endif; ?>
        <form action="<?php echo e(url('pages/business/inquiries.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
            <div class="form-grid">
                <div class="form-field">
                    <label for="reply-inquiry">Inquiry</label>
                    <select id="reply-inquiry" name="inquiry_id" required>
                        <option value="">Select inquiry</option>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <?php $inquiryOptionId = (string) ($inquiry['id'] ?? ''); ?>
                            <?php
                            $optionBudgetAmount = (float) ($inquiry['budget_amount'] ?? 0);
                            $optionBudgetLabel = $optionBudgetAmount > 0 ? money($optionBudgetAmount) : 'No budget shared';
                            $optionStatusKey = strtolower((string) ($inquiry['status'] ?? 'pending'));
                            $optionStatusLabel = $inquiryStatusMeta[$optionStatusKey] ?? ucfirst($optionStatusKey);
                            ?>
                            <option value="<?php echo e($inquiryOptionId); ?>" <?php echo $replyForm['inquiry_id'] === $inquiryOptionId ? 'selected' : ''; ?>>
                                <?php echo e((string) ($inquiry['client_name'] ?? 'Client')); ?> - <?php echo e((string) ($inquiry['campaign_need'] ?? 'Need')); ?>
                                (<?php echo e($optionBudgetLabel); ?>, <?php echo e($optionStatusLabel); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="field-error" data-error-for="inquiry_id"></small>
                </div>
                <div class="form-field">
                    <label for="reply-subject">Subject</label>
                    <input id="reply-subject" name="reply_subject" value="<?php echo e($replyForm['reply_subject']); ?>" required>
                    <small class="field-error" data-error-for="reply_subject"></small>
                </div>
            </div>
            <div class="form-grid full">
                <div class="form-field">
                    <label for="reply-message">Message</label>
                    <textarea id="reply-message" name="reply_message" required data-minlength="20"><?php echo e($replyForm['reply_message']); ?></textarea>
                    <small class="field-error" data-error-for="reply_message"></small>
                </div>
            </div>
            <button class="btn" type="submit">Send Reply</button>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
