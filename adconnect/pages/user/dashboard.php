<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Client Dashboard';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'dashboard';
$clientUserId = active_client_user_id();
$clientMetrics = fetch_dashboard_metrics_client($clientUserId);
$clientNotifications = fetch_notifications('client', $clientUserId, 8);
$recommendedListings = fetch_business_listings(8, $clientUserId, false);

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
                    <span>Client Dashboard</span>
                </nav>
                <h1>Client workspace overview</h1>
                <p>Track favorites, messages, and inquiry performance in one place.</p>
                <p><small>Last updated: <span data-current-date></span></small></p>
            </section>

            <section class="metrics">
                <article class="metric-card"><small>Saved Businesses</small><strong data-counter="<?php echo e((string) ($clientMetrics['saved_businesses'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Open Inquiries</small><strong data-counter="<?php echo e((string) ($clientMetrics['open_inquiries'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Unread Messages</small><strong data-counter="<?php echo e((string) ($clientMetrics['unread_messages'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Average Response Time</small><strong><?php echo e((float) ($clientMetrics['average_response_hours'] ?? 0) > 0 ? number_format((float) $clientMetrics['average_response_hours'], 1) . 'h' : 'N/A'); ?></strong></article>
            </section>

            <section class="card progress-wrap">
                <h3>Pipeline Progress</h3>
                <div class="progress-line"><small>Discovery Stage</small><div class="meter" data-meter="<?php echo e((string) ($clientMetrics['discovery_stage'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Ongoing Campaigns</small><div class="meter" data-meter="<?php echo e((string) ($clientMetrics['ongoing_campaigns'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Completed Negotiations</small><div class="meter" data-meter="<?php echo e((string) ($clientMetrics['completed_negotiations'] ?? 0)); ?>"><span></span></div></div>
            </section>

            <section class="tabs" data-tabs>
                <div class="tab-list" role="tablist" aria-label="Client dashboard tabs">
                    <button class="is-active" type="button" data-tab-target="updates">Updates</button>
                    <button type="button" data-tab-target="recommended">Recommended</button>
                    <button type="button" data-tab-target="alerts">Alerts</button>
                </div>
                <article class="tab-panel is-active" data-tab-panel="updates">
                    <div class="notice-list">
                        <?php foreach ($clientNotifications as $notification): ?>
                            <?php echo render_notification_item($notification); ?>
                        <?php endforeach; ?>
                        <?php if (empty($clientNotifications)): ?>
                            <article class="notice-item">No updates available right now.</article>
                        <?php endif; ?>
                    </div>
                </article>
                <article class="tab-panel" data-tab-panel="recommended">
                    <div class="card-grid">
                        <?php foreach ($recommendedListings as $listing): ?>
                            <?php echo render_listing_card($listing); ?>
                        <?php endforeach; ?>
                        <?php if (empty($recommendedListings)): ?>
                            <article class="card"><p>No recommended businesses yet.</p></article>
                        <?php endif; ?>
                    </div>
                </article>
                <article class="tab-panel" data-tab-panel="alerts">
                    <p class="card">Alerts are generated from inquiry and messaging status updates in your account.</p>
                </article>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
