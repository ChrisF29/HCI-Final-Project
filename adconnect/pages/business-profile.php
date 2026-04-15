<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'Business Profile';
$activePage = 'directory';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <a href="<?php echo e(url('pages/directory.php')); ?>">Directory</a>
                <span>/</span>
                <span>Business Profile</span>
            </nav>
            <h1>BrightPixel Studio</h1>
            <p>Full-service creative and digital agency focused on product storytelling, paid growth, and campaign optimization.</p>
            <div class="chip-row" style="margin-top:0.85rem;">
                <span class="chip">Creative</span>
                <span class="chip">Manila</span>
                <span class="chip">4.8 Rating</span>
                <span class="chip">Mid Budget Tier</span>
            </div>
        </section>

        <section class="tabs" data-tabs>
            <div class="tab-list" role="tablist" aria-label="Business tabs">
                <button class="is-active" type="button" data-tab-target="overview">Overview</button>
                <button type="button" data-tab-target="services">Services</button>
                <button type="button" data-tab-target="reviews">Reviews</button>
            </div>

            <article class="tab-panel is-active" data-tab-panel="overview">
                <div class="card section-stack">
                    <h3>About this business</h3>
                    <p>BrightPixel Studio builds user-centered ad journeys from awareness to conversion. Their workflow is optimized for measurable outcomes and collaborative iteration.</p>
                    <div class="metrics">
                        <article class="metric-card">
                            <small>Campaigns Completed</small>
                            <strong data-counter="124">0</strong>
                        </article>
                        <article class="metric-card">
                            <small>Average Conversion Lift</small>
                            <strong>+21%</strong>
                        </article>
                        <article class="metric-card">
                            <small>Response Time</small>
                            <strong>2 hrs</strong>
                        </article>
                    </div>
                </div>
            </article>

            <article class="tab-panel" data-tab-panel="services">
                <div class="card-grid">
                    <article class="card"><h3>Campaign Strategy</h3><p>Audience segmentation, channel planning, and KPI definition.</p></article>
                    <article class="card"><h3>Creative Production</h3><p>Ad copywriting, design systems, and video direction.</p></article>
                    <article class="card"><h3>Performance Optimization</h3><p>Weekly reporting and budget reallocation based on results.</p></article>
                </div>
            </article>

            <article class="tab-panel" data-tab-panel="reviews">
                <div class="notice-list">
                    <article class="notice-item">"Great strategic thinking and very reliable communication." - Retail Brand</article>
                    <article class="notice-item">"Helped us reduce CPA by 18% in less than two months." - SaaS Startup</article>
                    <article class="notice-item">"Clear process and excellent creative direction." - FMCG Team</article>
                </div>
            </article>
        </section>

        <section class="card section-stack">
            <h2>Send an inquiry</h2>
            <form action="#" method="POST" data-validate>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="contact-name">Your Name</label>
                        <input id="contact-name" name="contact_name" required>
                        <small class="field-error" data-error-for="contact_name"></small>
                    </div>
                    <div class="form-field">
                        <label for="contact-email">Your Email</label>
                        <input id="contact-email" type="email" name="contact_email" required>
                        <small class="field-error" data-error-for="contact_email"></small>
                    </div>
                </div>
                <div class="form-grid full">
                    <div class="form-field">
                        <label for="contact-needs">Campaign Needs</label>
                        <textarea id="contact-needs" name="campaign_needs" required data-minlength="20"></textarea>
                        <small class="field-error" data-error-for="campaign_needs"></small>
                    </div>
                </div>
                <button class="btn" type="submit">Submit Inquiry</button>
            </form>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
