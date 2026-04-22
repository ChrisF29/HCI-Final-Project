<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'Ads Feed';
$activePage = 'ads';
$campaignAds = fetch_ads_feed(50);
$liveCampaignCount = 0;

foreach ($campaignAds as $campaignAd) {
    if (($campaignAd['status'] ?? '') === 'live') {
        $liveCampaignCount += 1;
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
                <section class="section-stack" data-search-scope data-filter-scope>
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
                            <option value="all">All Status</option>
                            <option value="live">Live</option>
                            <option value="review">Review</option>
                            <option value="planned">Planned</option>
                        </select>
                        <button class="btn-ghost" type="button" data-filter-reset>Reset</button>
                    </div>
                    <p><strong><span data-filter-count>0</span></strong> campaigns currently visible.</p>
                    <div class="card-grid">
                        <?php foreach ($campaignAds as $ad): ?>
                            <?php echo render_ad_card($ad); ?>
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
