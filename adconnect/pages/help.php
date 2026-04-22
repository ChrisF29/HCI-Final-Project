<?php
require_once dirname(__DIR__) . '/includes/config.php';

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
                <form action="#" method="POST" data-validate class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="support-name">Full Name</label>
                            <input id="support-name" name="support_name" required>
                            <small class="field-error" data-error-for="support_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="support-email">Email Address</label>
                            <input id="support-email" type="email" name="support_email" required>
                            <small class="field-error" data-error-for="support_email"></small>
                        </div>
                    </div>
                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="support-topic">Topic</label>
                            <select id="support-topic" name="support_topic" required>
                                <option value="">Select concern</option>
                                <option value="account">Account</option>
                                <option value="billing">Billing</option>
                                <option value="technical">Technical Issue</option>
                            </select>
                            <small class="field-error" data-error-for="support_topic"></small>
                        </div>
                        <div class="form-field">
                            <label for="support-message">Message</label>
                            <textarea id="support-message" name="support_message" required data-minlength="20"></textarea>
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
