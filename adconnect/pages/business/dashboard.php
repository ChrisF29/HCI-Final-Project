<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Business Dashboard';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'dashboard';
$businessId = active_business_profile_id();
$businessUserId = active_business_user_id();
$businessMetrics = fetch_dashboard_metrics_business($businessId);
$businessNotifications = fetch_notifications('business', $businessUserId, 8);

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
                    <span>Business Dashboard</span>
                </nav>
                <h1>Business performance overview</h1>
                <p>Monitor campaign delivery, inquiries, and ad moderation status.</p>
                <p><small>Report date: <span data-current-date></span></small></p>
            </section>

            <section class="metrics">
                <article class="metric-card"><small>Active Ads</small><strong data-counter="<?php echo e((string) ($businessMetrics['active_ads'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Open Inquiries</small><strong data-counter="<?php echo e((string) ($businessMetrics['open_inquiries'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Avg CTR</small><strong><?php echo e(number_format((float) ($businessMetrics['avg_ctr'] ?? 0), 2)); ?>%</strong></article>
                <article class="metric-card"><small>Monthly Spend</small><strong><?php echo e(money_compact((float) ($businessMetrics['monthly_spend'] ?? 0))); ?></strong></article>
            </section>

            <section class="card progress-wrap">
                <h3>Campaign Health</h3>
                <div class="progress-line"><small>Lead Quality</small><div class="meter" data-meter="<?php echo e((string) ($businessMetrics['lead_quality'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Ad Approval Rate</small><div class="meter" data-meter="<?php echo e((string) ($businessMetrics['ad_approval_rate'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Response SLA</small><div class="meter" data-meter="<?php echo e((string) ($businessMetrics['response_sla'] ?? 0)); ?>"><span></span></div></div>
            </section>

            <section class="card section-stack">
                <div class="inline-split">
                    <h3>Recent notifications</h3>
                    <a class="btn-ghost" href="<?php echo e(url('pages/business/inquiries.php?role=business')); ?>">Open inquiries</a>
                </div>
                <div class="notice-list">
                    <?php foreach ($businessNotifications as $notification): ?>
                        <?php echo render_notification_item($notification); ?>
                    <?php endforeach; ?>
                    <?php if (empty($businessNotifications)): ?>
                        <article class="notice-item">No notifications available.</article>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
