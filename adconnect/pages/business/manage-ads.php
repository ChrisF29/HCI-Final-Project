<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Manage Ads';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'manage-ads';
$businessId = active_business_profile_id();
$businessAds = fetch_ads_feed(60, $businessId);

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container content-grid">
        <?php require dirname(__DIR__, 2) . '/includes/sidebar.php'; ?>

        <div class="section-stack" data-search-scope data-filter-scope>
            <section class="page-hero">
                <nav class="breadcrumbs" aria-label="Breadcrumb">
                    <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                    <span>/</span>
                    <a href="<?php echo e(url('pages/business/dashboard.php?role=business')); ?>">Business Dashboard</a>
                    <span>/</span>
                    <span>Manage Ads</span>
                </nav>
                <h1>Create and monitor advertisements</h1>
                <p>Upload campaigns with validation-ready forms and moderate publishing states.</p>
            </section>

            <section class="card section-stack">
                <h3>Upload New Advertisement</h3>
                <form action="#" method="POST" data-validate class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="ad-title">Ad Title</label>
                            <input id="ad-title" name="ad_title" required>
                            <small class="field-error" data-error-for="ad_title"></small>
                        </div>
                        <div class="form-field">
                            <label for="ad-channel">Channel</label>
                            <select id="ad-channel" name="ad_channel" required>
                                <option value="">Select channel</option>
                                <option value="social">Social</option>
                                <option value="search">Search</option>
                                <option value="video">Video</option>
                                <option value="events">Events</option>
                            </select>
                            <small class="field-error" data-error-for="ad_channel"></small>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="ad-budget">Budget</label>
                            <input id="ad-budget" name="ad_budget" required>
                            <small class="field-error" data-error-for="ad_budget"></small>
                        </div>
                        <div class="form-field">
                            <label for="ad-objective">Objective</label>
                            <select id="ad-objective" name="ad_objective" required>
                                <option value="">Select objective</option>
                                <option value="awareness">Awareness</option>
                                <option value="engagement">Engagement</option>
                                <option value="sales">Sales</option>
                                <option value="leads">Leads</option>
                            </select>
                            <small class="field-error" data-error-for="ad_objective"></small>
                        </div>
                    </div>

                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="ad-description">Description</label>
                            <textarea id="ad-description" name="ad_description" required data-minlength="20"></textarea>
                            <small class="field-error" data-error-for="ad_description"></small>
                        </div>
                    </div>

                    <button class="btn" type="submit">Submit Ad</button>
                </form>
            </section>

            <section class="card section-stack">
                <div class="toolbar">
                    <input type="search" data-search-input placeholder="Search ad title">
                    <select name="status" data-filter-select>
                        <option value="all">All Status</option>
                        <option value="live">Live</option>
                        <option value="review">Review</option>
                        <option value="planned">Planned</option>
                    </select>
                    <button class="btn-ghost" type="button" data-filter-reset>Reset</button>
                </div>
                <p><strong><span data-filter-count>0</span></strong> ads displayed.</p>
                <div class="card-grid">
                    <?php foreach ($businessAds as $ad): ?>
                        <?php echo render_ad_card($ad); ?>
                    <?php endforeach; ?>
                </div>
                <div class="empty-state <?php echo !empty($businessAds) ? 'is-hidden' : ''; ?>" data-filter-empty data-empty-state>
                    <p>No ad matched your search or status filter.</p>
                </div>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
