<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'Ads Feed';
$activePage = 'ads';
$campaignAds = fetch_ads_feed(50, null, 'live');
$previewId = query_int('preview_id');
$previewError = '';
$previewAd = null;
$liveCampaignCount = 0;

foreach ($campaignAds as $campaignAd) {
    if (($campaignAd['status'] ?? '') === 'live') {
        $liveCampaignCount += 1;
    }
}

if ($previewId !== null) {
    $previewAd = fetch_moderation_ad_detail($previewId);

    if (!$previewAd || strtolower((string) ($previewAd['status'] ?? '')) !== 'live') {
        $previewAd = null;
        $previewError = 'No live campaign found for the selected preview entry.';
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <span>Ads Feed</span>
            </nav>
            <h1>Campaign feed and moderation states</h1>
            <p>Browse current campaigns, filter by channel and status, and review publication readiness.</p>
        </section>

        <section class="tabs" data-tabs>
            <div class="tab-list" role="tablist" aria-label="Ad feed tabs">
                <button class="is-active" type="button" data-tab-target="ads-all">All Campaigns</button>
                <button type="button" data-tab-target="ads-live">Live Focus</button>
                <button type="button" data-tab-target="ads-safety">Moderation Notes</button>
            </div>

            <article class="tab-panel is-active" data-tab-panel="ads-all">
                <section id="ads-feed" class="section-stack" data-search-scope data-filter-scope>
                    <?php if ($previewError !== ''): ?>
                        <div class="notice-item" role="alert"><?php echo e($previewError); ?></div>
                    <?php endif; ?>

                    <?php if ($previewAd): ?>
                        <section id="ad-preview" class="card section-stack">
                            <h3>Campaign Preview</h3>
                            <div class="chip-row">
                                <span class="chip"><?php echo e(ucfirst((string) ($previewAd['status'] ?? 'live'))); ?></span>
                                <span class="chip"><?php echo e(ucfirst((string) ($previewAd['channel'] ?? 'social'))); ?></span>
                                <span class="chip"><?php echo e((string) ($previewAd['category_name'] ?? 'Uncategorized')); ?></span>
                            </div>
                            <p><strong>Title:</strong> <?php echo e((string) ($previewAd['title'] ?? 'Untitled ad')); ?></p>
                            <p><strong>Owner:</strong> <?php echo e((string) ($previewAd['owner_name'] ?? 'Unknown owner')); ?></p>
                            <p><strong>Objective:</strong> <?php echo e(ucfirst((string) ($previewAd['objective'] ?? 'awareness'))); ?></p>
                            <p><strong>Location:</strong> <?php echo e((string) ($previewAd['location'] ?? 'Unspecified')); ?></p>
                            <p><strong>Budget:</strong> <?php echo e(money((float) ($previewAd['budget_amount'] ?? 0))); ?></p>
                            <p><?php echo e((string) ($previewAd['description'] ?? 'No campaign description provided.')); ?></p>
                            <div class="hero-actions">
                                <a class="btn-secondary" href="<?php echo e(url('pages/ads.php#ads-feed')); ?>">Close Preview</a>
                            </div>
                        </section>
                    <?php endif; ?>

                    <div class="toolbar">
                        <input type="search" data-search-input placeholder="Search ad title or agency">
                        <select name="channel" data-filter-select>
                            <option value="all">All Channels</option>
                            <option value="social">Social</option>
                            <option value="search">Search</option>
                            <option value="video">Video</option>
                            <option value="events">Events</option>
                        </select>
                        <select name="status" data-filter-select>
                            <option value="live">Live</option>
                        </select>
                        <button class="btn-ghost" type="button" data-filter-reset>Reset</button>
                    </div>
                    <p><strong><span data-filter-count>0</span></strong> campaigns currently visible.</p>
                    <div class="card-grid">
                        <?php foreach ($campaignAds as $ad): ?>
                            <?php echo render_ad_card($ad, url('pages/ads.php?preview_id=' . (string) ((int) ($ad['id'] ?? 0)) . '#ad-preview')); ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="empty-state <?php echo !empty($campaignAds) ? 'is-hidden' : ''; ?>" data-filter-empty data-empty-state>
                        <p>No campaigns match your current criteria.</p>
                    </div>
                </section>
            </article>

            <article class="tab-panel" data-tab-panel="ads-live">
                <p><?php echo e((string) $liveCampaignCount); ?> live campaign(s) are active. Prioritize clear CTA language, honest claims, and audience fit for top approval rates.</p>
            </article>

            <article class="tab-panel" data-tab-panel="ads-safety">
                <p>Moderation status is managed through the campaign records and can be reviewed in the admin moderation queue.</p>
            </article>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
