<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Inquiries';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'inquiries';
$businessId = active_business_profile_id();
$inquiries = fetch_inquiries_for_business($businessId, 200);

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
                <p>Respond quickly to maintain high response SLA and better conversion rates.</p>
                <div class="hero-actions">
                    <button class="btn" type="button" data-modal-target="reply-modal">Compose Reply</button>
                </div>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Campaign Need</th>
                            <th>Budget</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <tr>
                                <td><?php echo e((string) ($inquiry['client_name'] ?? 'Client')); ?></td>
                                <td><?php echo e((string) ($inquiry['campaign_need'] ?? 'Campaign request')); ?></td>
                                <td><?php echo e(money((float) ($inquiry['budget_amount'] ?? 0))); ?></td>
                                <td><span class="badge <?php echo e(badge_class_for_status((string) ($inquiry['status'] ?? '')); ?>"><?php echo e(ucfirst((string) ($inquiry['status'] ?? 'pending'))); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($inquiries)): ?>
                            <tr>
                                <td colspan="4">No inquiries found.</td>
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

<div class="modal" data-modal="reply-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Reply to inquiry</h3>
            <button class="btn-ghost" type="button" data-modal-close>Close</button>
        </div>
        <form action="#" method="POST" data-validate class="section-stack">
            <div class="form-grid">
                <div class="form-field">
                    <label for="reply-client">Client</label>
                    <input id="reply-client" name="client_name" required>
                    <small class="field-error" data-error-for="client_name"></small>
                </div>
                <div class="form-field">
                    <label for="reply-subject">Subject</label>
                    <input id="reply-subject" name="reply_subject" required>
                    <small class="field-error" data-error-for="reply_subject"></small>
                </div>
            </div>
            <div class="form-grid full">
                <div class="form-field">
                    <label for="reply-message">Message</label>
                    <textarea id="reply-message" name="reply_message" required data-minlength="20"></textarea>
                    <small class="field-error" data-error-for="reply_message"></small>
                </div>
            </div>
            <button class="btn" type="submit">Send Reply</button>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
