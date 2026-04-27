<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Ads Moderation';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'ads-moderation';
$moderationAds = fetch_ads_feed(80);
$previewId = query_int('preview_id');
$previewError = '';
$previewAd = null;

if ($previewId !== null) {
    $previewAd = fetch_moderation_ad_detail($previewId);
    if (!$previewAd) {
        $previewError = 'No campaign found for the selected preview entry.';
    }
}

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

            <?php if ($previewError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($previewError); ?></div>
            <?php endif; ?>

            <?php if ($previewAd): ?>
                <section class="card section-stack">
                    <h2>Campaign Preview</h2>
                    <div class="chip-row">
                        <span class="chip"><?php echo e(ucfirst((string) ($previewAd['status'] ?? 'planned'))); ?></span>
                        <span class="chip"><?php echo e(ucfirst((string) ($previewAd['channel'] ?? 'social'))); ?></span>
                        <span class="chip"><?php echo e((string) ($previewAd['category_name'] ?? 'Uncategorized')); ?></span>
                    </div>
                    <p><strong>Title:</strong> <?php echo e((string) ($previewAd['title'] ?? 'Untitled ad')); ?></p>
                    <p><strong>Owner:</strong> <?php echo e((string) ($previewAd['owner_name'] ?? 'Unknown owner')); ?></p>
                    <p><strong>Objective:</strong> <?php echo e(ucfirst((string) ($previewAd['objective'] ?? 'awareness'))); ?></p>
                    <p><strong>Location:</strong> <?php echo e((string) ($previewAd['location'] ?? 'Unspecified')); ?></p>
                    <p><strong>Budget:</strong> <?php echo e(money((float) ($previewAd['budget_amount'] ?? 0))); ?></p>
                    <p><strong>Created:</strong> <?php echo e(format_date_label((string) ($previewAd['created_at'] ?? ''))); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo e(format_date_label((string) ($previewAd['updated_at'] ?? ''))); ?></p>
                    <p><strong>Published:</strong> <?php echo e((string) ($previewAd['published_at'] ?? '') !== '' ? format_date_label((string) $previewAd['published_at']) : 'Not published'); ?></p>
                    <p><?php echo e((string) ($previewAd['description'] ?? 'No campaign description provided.')); ?></p>
                    <p><strong>Moderation Notes:</strong> <?php echo e((string) ($previewAd['moderation_notes'] ?? 'No moderation notes yet.')); ?></p>
                    <div class="hero-actions">
                        <a class="btn-secondary" href="<?php echo e(url('pages/admin/ads-moderation.php')); ?>">Close Preview</a>
                    </div>
                </section>
            <?php endif; ?>

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
                        <?php echo render_ad_card($ad, url('pages/admin/ads-moderation.php?preview_id=' . (string) ((int) ($ad['id'] ?? 0)))); ?>
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
