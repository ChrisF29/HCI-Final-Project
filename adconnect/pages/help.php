<?php
require_once dirname(__DIR__) . '/includes/config.php';

$supportError = '';
$supportStatus = (string) ($_GET['support'] ?? '');
$supportForm = [
    'support_name' => '',
    'support_email' => '',
    'support_topic' => '',
    'support_message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supportForm['support_name'] = trim((string) ($_POST['support_name'] ?? ''));
    $supportForm['support_email'] = strtolower(trim((string) ($_POST['support_email'] ?? '')));
    $supportForm['support_topic'] = trim((string) ($_POST['support_topic'] ?? ''));
    $supportForm['support_message'] = trim((string) ($_POST['support_message'] ?? ''));

    if ($supportForm['support_name'] === '' || $supportForm['support_topic'] === '' || $supportForm['support_message'] === '') {
        $supportError = 'Please complete all support form fields.';
    } elseif (!filter_var($supportForm['support_email'], FILTER_VALIDATE_EMAIL)) {
        $supportError = 'Please provide a valid email address.';
    } elseif (!db_available()) {
        $supportError = 'Database is unavailable right now. Please try again.';
    } else {
        $saved = db_execute(
            'INSERT INTO support_requests (user_id, name, email, topic, message, status)
             VALUES (:user_id, :name, :email, :topic, :message, :status)',
            [
                'user_id' => current_user_id(),
                'name' => $supportForm['support_name'],
                'email' => $supportForm['support_email'],
                'topic' => $supportForm['support_topic'],
                'message' => $supportForm['support_message'],
                'status' => 'open',
            ]
        );

        if ($saved) {
            header('Location: ' . url('pages/help.php?support=sent'));
            exit;
        }

        $supportError = 'Unable to submit support request. Please try again.';
    }
}

$pageTitle = 'Help Center';
$activePage = 'help';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <span>Help Center</span>
            </nav>
            <h1>Need help with AdConnect?</h1>
            <p>Find quick answers, browse platform guides, and submit support requests.</p>
        </section>

        <section class="tabs" data-tabs>
            <div class="tab-list" role="tablist" aria-label="Help tabs">
                <button class="is-active" type="button" data-tab-target="faq">FAQ</button>
                <button type="button" data-tab-target="guides">Guides</button>
                <button type="button" data-tab-target="support">Support Form</button>
            </div>

            <article class="tab-panel is-active" data-tab-panel="faq">
                <div class="notice-list">
                    <article class="notice-item"><strong>How do I switch roles?</strong><p>Use portal links in the top navigation to simulate client, business, or admin views.</p></article>
                    <article class="notice-item"><strong>Are listings real-time?</strong><p>Listings and campaigns are loaded from the platform database and update as records change.</p></article>
                    <article class="notice-item"><strong>Can I submit ads now?</strong><p>Yes. Ads are captured through validated forms and stored for moderation workflow.</p></article>
                </div>
            </article>

            <article class="tab-panel" data-tab-panel="guides">
                <div class="card-grid">
                    <article class="card"><h3>Client Guide</h3><p>Browse directory, save favorites, and compare offers.</p></article>
                    <article class="card"><h3>Business Guide</h3><p>Manage profile, publish ads, and reply to inquiries.</p></article>
                    <article class="card"><h3>Admin Guide</h3><p>Moderate ads, approve users, and handle reports.</p></article>
                </div>
            </article>

            <article class="tab-panel" data-tab-panel="support">
                <?php if ($supportStatus === 'sent'): ?>
                    <div class="notice-item" role="status">Support request submitted successfully.</div>
                <?php endif; ?>
                <?php if ($supportError !== ''): ?>
                    <div class="notice-item" role="alert"><?php echo e($supportError); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(url('pages/help.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="support-name">Full Name</label>
                            <input id="support-name" name="support_name" value="<?php echo e($supportForm['support_name']); ?>" required>
                            <small class="field-error" data-error-for="support_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="support-email">Email Address</label>
                            <input id="support-email" type="email" name="support_email" value="<?php echo e($supportForm['support_email']); ?>" required>
                            <small class="field-error" data-error-for="support_email"></small>
                        </div>
                    </div>
                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="support-topic">Topic</label>
                            <select id="support-topic" name="support_topic" required>
                                <option value="">Select concern</option>
                                <option value="account" <?php echo $supportForm['support_topic'] === 'account' ? 'selected' : ''; ?>>Account</option>
                                <option value="billing" <?php echo $supportForm['support_topic'] === 'billing' ? 'selected' : ''; ?>>Billing</option>
                                <option value="technical" <?php echo $supportForm['support_topic'] === 'technical' ? 'selected' : ''; ?>>Technical Issue</option>
                            </select>
                            <small class="field-error" data-error-for="support_topic"></small>
                        </div>
                        <div class="form-field">
                            <label for="support-message">Message</label>
                            <textarea id="support-message" name="support_message" required data-minlength="20"><?php echo e($supportForm['support_message']); ?></textarea>
                            <small class="field-error" data-error-for="support_message"></small>
                        </div>
                    </div>
                    <button class="btn" type="submit">Submit Support Request</button>
                </form>
            </article>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
