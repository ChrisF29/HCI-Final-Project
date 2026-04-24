<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Analytics';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'analytics';
$businessId = active_business_profile_id();
$analyticsSummary = fetch_business_analytics_summary($businessId);
$channelDistribution = fetch_business_channel_distribution($businessId);
$campaignRows = fetch_campaign_analytics_rows($businessId, 60);

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
                    <span>Analytics</span>
                </nav>
                <h1>Campaign analytics</h1>
                <p>Visualize conversion trends and optimize spend distribution.</p>
            </section>

            <section class="metrics">
                <article class="metric-card"><small>Total Impressions</small><strong data-counter="<?php echo e((string) ($analyticsSummary['impressions'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Total Clicks</small><strong data-counter="<?php echo e((string) ($analyticsSummary['clicks'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Average CTR</small><strong><?php echo e(number_format((float) ($analyticsSummary['avg_ctr'] ?? 0), 2)); ?>%</strong></article>
                <article class="metric-card"><small>Cost per Lead</small><strong><?php echo e(money((float) ($analyticsSummary['cost_per_lead'] ?? 0))); ?></strong></article>
            </section>

            <section class="card progress-wrap">
                <h3>Channel performance</h3>
                <div class="progress-line"><small>Social Ads</small><div class="meter" data-meter="<?php echo e((string) ($channelDistribution['social'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Search Ads</small><div class="meter" data-meter="<?php echo e((string) ($channelDistribution['search'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Video Ads</small><div class="meter" data-meter="<?php echo e((string) ($channelDistribution['video'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Event Activations</small><div class="meter" data-meter="<?php echo e((string) ($channelDistribution['events'] ?? 0)); ?>"><span></span></div></div>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>CTR</th>
                            <th>Leads</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaignRows as $campaignRow): ?>
                            <tr>
                                <td><?php echo e((string) ($campaignRow['name'] ?? 'Campaign')); ?></td>
                                <td><?php echo e(number_format((float) ($campaignRow['ctr'] ?? 0), 2)); ?>%</td>
                                <td><?php echo e((string) ((int) ($campaignRow['leads'] ?? 0))); ?></td>
                                <td><span class="badge <?php echo e(badge_class_for_status((string) ($campaignRow['status'] ?? ''))); ?>"><?php echo e(ucfirst((string) ($campaignRow['status'] ?? 'planned'))); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($campaignRows)): ?>
                            <tr>
                                <td colspan="4">No campaign analytics found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
