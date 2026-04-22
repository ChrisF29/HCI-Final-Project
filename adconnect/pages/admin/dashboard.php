<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Admin Dashboard';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'dashboard';
$adminMetrics = fetch_dashboard_metrics_admin();
$adminNotifications = fetch_notifications('admin', null, 8);

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
                    <span>Admin Dashboard</span>
                </nav>
                <h1>Platform control center</h1>
                <p>Track moderation queues, user activity, approvals, and compliance indicators.</p>
            </section>

            <section class="metrics">
                <article class="metric-card"><small>Total Users</small><strong data-counter="<?php echo e((string) ($adminMetrics['total_users'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Pending Approvals</small><strong data-counter="<?php echo e((string) ($adminMetrics['pending_approvals'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Ads for Review</small><strong data-counter="<?php echo e((string) ($adminMetrics['ads_for_review'] ?? 0)); ?>">0</strong></article>
                <article class="metric-card"><small>Open Reports</small><strong data-counter="<?php echo e((string) ($adminMetrics['open_reports'] ?? 0)); ?>">0</strong></article>
            </section>

            <section class="card progress-wrap">
                <h3>Operational health</h3>
                <div class="progress-line"><small>Approval throughput</small><div class="meter" data-meter="<?php echo e((string) ($adminMetrics['approval_throughput'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Moderation SLA</small><div class="meter" data-meter="<?php echo e((string) ($adminMetrics['moderation_sla'] ?? 0)); ?>"><span></span></div></div>
                <div class="progress-line"><small>Resolved reports</small><div class="meter" data-meter="<?php echo e((string) ($adminMetrics['resolved_reports'] ?? 0)); ?>"><span></span></div></div>
            </section>

            <section class="card section-stack">
                <h3>Admin notifications</h3>
                <div class="notice-list">
                    <?php foreach ($adminNotifications as $notification): ?>
                        <?php echo render_notification_item($notification); ?>
                    <?php endforeach; ?>
                    <?php if (empty($adminNotifications)): ?>
                        <article class="notice-item">No notifications available.</article>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
