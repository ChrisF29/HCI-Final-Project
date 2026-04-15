<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Analytics';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'analytics';

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
                <article class="metric-card"><small>Total Impressions</small><strong data-counter="845000">0</strong></article>
                <article class="metric-card"><small>Total Clicks</small><strong data-counter="38410">0</strong></article>
                <article class="metric-card"><small>Average CTR</small><strong>4.55%</strong></article>
                <article class="metric-card"><small>Cost per Lead</small><strong>PHP 312</strong></article>
            </section>

            <section class="card progress-wrap">
                <h3>Channel performance</h3>
                <div class="progress-line"><small>Social Ads</small><div class="meter" data-meter="76"><span></span></div></div>
                <div class="progress-line"><small>Search Ads</small><div class="meter" data-meter="88"><span></span></div></div>
                <div class="progress-line"><small>Video Ads</small><div class="meter" data-meter="63"><span></span></div></div>
                <div class="progress-line"><small>Event Activations</small><div class="meter" data-meter="54"><span></span></div></div>
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
                        <tr><td>Summer Push 2026</td><td>5.2%</td><td>240</td><td><span class="badge badge-success">Strong</span></td></tr>
                        <tr><td>Back-to-School Promo</td><td>4.1%</td><td>132</td><td><span class="badge badge-warning">Monitor</span></td></tr>
                        <tr><td>Holiday Launch</td><td>3.7%</td><td>96</td><td><span class="badge badge-neutral">Early</span></td></tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
