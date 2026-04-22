<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Ads Moderation';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'ads-moderation';
$moderationAds = fetch_ads_feed(80);

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
                    <a href="<?php echo e(url('pages/admin/dashboard.php?role=admin')); ?>">Admin Dashboard</a>
                    <span>/</span>
                    <span>Ads Moderation</span>
                </nav>
                <h1>Moderate submitted ads</h1>
                <p>Review campaign compliance and publication readiness.</p>
            </section>

            <section class="card section-stack">
                <div class="toolbar">
                    <input type="search" data-search-input placeholder="Search campaign or owner">
                    <select name="status" data-filter-select>
                        <option value="all">All Status</option>
                        <option value="live">Live</option>
                        <option value="review">Review</option>
                        <option value="planned">Planned</option>
                    </select>
                    <button class="btn-ghost" type="button" data-filter-reset>Reset</button>
                </div>
                <p><strong><span data-filter-count>0</span></strong> ads in current view.</p>
                <div class="card-grid">
                    <?php foreach ($moderationAds as $ad): ?>
                        <?php echo render_ad_card($ad); ?>
                    <?php endforeach; ?>
                </div>
                <div class="empty-state <?php echo !empty($moderationAds) ? 'is-hidden' : ''; ?>" data-filter-empty data-empty-state>
                    <p>No moderation entries match your filters.</p>
                </div>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
