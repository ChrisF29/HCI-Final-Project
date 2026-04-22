<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'Business Profile';
$activePage = 'directory';
$businessId = active_business_profile_id();
$businessProfile = fetch_business_profile($businessId);
$businessReviews = fetch_reviews_for_business($businessId, 20);
$campaignCount = $businessId !== null
    ? db_count('SELECT COUNT(*) FROM campaigns WHERE business_id = :business_id', ['business_id' => $businessId])
    : 0;
$analyticsSummary = fetch_business_analytics_summary($businessId);
$conversionLift = ($analyticsSummary['clicks'] ?? 0) > 0
    ? (($analyticsSummary['leads'] ?? 0) / $analyticsSummary['clicks']) * 100
    : 0;
$responseTimeHours = $businessId !== null
    ? (float) (db_value(
        "SELECT COALESCE(AVG(TIMESTAMPDIFF(HOUR, i.created_at, i.updated_at)), 0)
         FROM inquiries i
         WHERE i.business_id = :business_id",
        ['business_id' => $businessId]
    ) ?? 0)
    : 0;

$profileName = (string) ($businessProfile['business_name'] ?? 'Business');
$profileDescription = (string) ($businessProfile['description'] ?? 'No business profile information found.');
$profileCategory = (string) ($businessProfile['category_name'] ?? 'Uncategorized');
$profileCity = (string) ($businessProfile['city'] ?? 'Unspecified');
$profileRating = (float) ($businessProfile['rating'] ?? 0);
$profileBudget = ucfirst((string) ($businessProfile['budget_tier'] ?? 'mid'));
$profileSpecialties = is_array($businessProfile['specialties'] ?? null) ? $businessProfile['specialties'] : [];

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
            <h1><?php echo e($profileName); ?></h1>
            <p><?php echo e($profileDescription); ?></p>
            <div class="chip-row" style="margin-top:0.85rem;">
                <span class="chip"><?php echo e($profileCategory); ?></span>
                <span class="chip"><?php echo e($profileCity); ?></span>
                <span class="chip"><?php echo e(number_format($profileRating, 1)); ?> Rating</span>
                <span class="chip"><?php echo e($profileBudget); ?> Budget Tier</span>
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
                    <p><?php echo e($profileDescription); ?></p>
                    <div class="metrics">
                        <article class="metric-card">
                            <small>Campaigns Completed</small>
                            <strong data-counter="<?php echo e((string) $campaignCount); ?>">0</strong>
                        </article>
                        <article class="metric-card">
                            <small>Average Conversion Lift</small>
                            <strong>+<?php echo e(number_format($conversionLift, 1)); ?>%</strong>
                        </article>
                        <article class="metric-card">
                            <small>Response Time</small>
                            <strong><?php echo e($responseTimeHours > 0 ? number_format($responseTimeHours, 1) . ' hrs' : 'N/A'); ?></strong>
                        </article>
                    </div>
                </div>
            </article>

            <article class="tab-panel" data-tab-panel="services">
                <div class="card-grid">
                    <?php foreach ($profileSpecialties as $specialty): ?>
                        <article class="card">
                            <h3><?php echo e((string) $specialty); ?></h3>
                            <p>Service capability listed by this business profile.</p>
                        </article>
                    <?php endforeach; ?>
                    <?php if (empty($profileSpecialties)): ?>
                        <article class="card"><p>No services listed yet.</p></article>
                    <?php endif; ?>
                </div>
            </article>

            <article class="tab-panel" data-tab-panel="reviews">
                <div class="notice-list">
                    <?php foreach ($businessReviews as $review): ?>
                        <article class="notice-item">
                            &quot;<?php echo e((string) ($review['comment'] ?? '')); ?>&quot;
                            - <?php echo e((string) ($review['reviewer_name'] ?? 'Client')); ?> (<?php echo e((string) ((int) ($review['rating'] ?? 0))); ?>/5)
                        </article>
                    <?php endforeach; ?>
                    <?php if (empty($businessReviews)): ?>
                        <article class="notice-item">No reviews posted yet.</article>
                    <?php endif; ?>
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
