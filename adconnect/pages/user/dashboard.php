<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Client Dashboard';
$activePage = '';
$sidebarRole = 'user';
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
                    <span>Client Dashboard</span>
                </nav>
                <h1>Client workspace overview</h1>
                <p>Track favorites, messages, and inquiry performance in one place.</p>
                <p><small>Last updated: <span data-current-date></span></small></p>
            </section>

            <section class="metrics">
                <article class="metric-card"><small>Saved Businesses</small><strong data-counter="18">0</strong></article>
                <article class="metric-card"><small>Open Inquiries</small><strong data-counter="6">0</strong></article>
                <article class="metric-card"><small>Unread Messages</small><strong data-counter="12">0</strong></article>
                <article class="metric-card"><small>Average Response Time</small><strong>3.2h</strong></article>
            </section>

            <section class="card progress-wrap">
                <h3>Pipeline Progress</h3>
                <div class="progress-line"><small>Discovery Stage</small><div class="meter" data-meter="78"><span></span></div></div>
                <div class="progress-line"><small>Ongoing Campaigns</small><div class="meter" data-meter="52"><span></span></div></div>
                <div class="progress-line"><small>Completed Negotiations</small><div class="meter" data-meter="64"><span></span></div></div>
            </section>

            <section class="tabs" data-tabs>
                <div class="tab-list" role="tablist" aria-label="Client dashboard tabs">
                    <button class="is-active" type="button" data-tab-target="updates">Updates</button>
                    <button type="button" data-tab-target="recommended">Recommended</button>
                    <button type="button" data-tab-target="alerts">Alerts</button>
                </div>
                <article class="tab-panel is-active" data-tab-panel="updates">
                    <div class="notice-list" data-feed="notifications"></div>
                </article>
                <article class="tab-panel" data-tab-panel="recommended">
                    <div class="card-grid" data-feed="listings"></div>
                </article>
                <article class="tab-panel" data-tab-panel="alerts">
                    <p class="card">Role-aware alerts appear here. Future backend logic can push real message and status updates.</p>
                </article>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
