<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Inquiries';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'inquiries';

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
                        <tr><td>Alex Santos</td><td>Product launch ads</td><td>PHP 150K</td><td><span class="badge badge-warning">Pending</span></td></tr>
                        <tr><td>Luna Retail</td><td>Holiday awareness</td><td>PHP 95K</td><td><span class="badge badge-success">Replied</span></td></tr>
                        <tr><td>Flow Apps</td><td>Search performance</td><td>PHP 200K</td><td><span class="badge badge-neutral">Scheduled</span></td></tr>
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
