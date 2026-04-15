<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Business Dashboard';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'dashboard';

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
                <article class="metric-card"><small>Active Ads</small><strong data-counter="14">0</strong></article>
                <article class="metric-card"><small>Open Inquiries</small><strong data-counter="9">0</strong></article>
                <article class="metric-card"><small>Avg CTR</small><strong>4.9%</strong></article>
                <article class="metric-card"><small>Monthly Spend</small><strong>PHP 380K</strong></article>
            </section>

            <section class="card progress-wrap">
                <h3>Campaign Health</h3>
                <div class="progress-line"><small>Lead Quality</small><div class="meter" data-meter="81"><span></span></div></div>
                <div class="progress-line"><small>Ad Approval Rate</small><div class="meter" data-meter="93"><span></span></div></div>
                <div class="progress-line"><small>Response SLA</small><div class="meter" data-meter="74"><span></span></div></div>
            </section>

            <section class="card section-stack">
                <div class="inline-split">
                    <h3>Recent notifications</h3>
                    <a class="btn-ghost" href="<?php echo e(url('pages/business/inquiries.php?role=business')); ?>">Open inquiries</a>
                </div>
                <div class="notice-list" data-feed="notifications"></div>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
