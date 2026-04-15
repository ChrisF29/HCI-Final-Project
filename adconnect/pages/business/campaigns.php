<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Campaigns';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'campaigns';

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
                    <span>Campaigns</span>
                </nav>
                <h1>Campaign planning and scheduling</h1>
                <p>Track launches, monitor owners, and prepare next campaign waves.</p>
            </section>

            <section class="card-grid">
                <article class="card">
                    <div class="card-top"><h3>Summer Push 2026</h3><span class="badge badge-success">Active</span></div>
                    <p>Owner: Growth Team · Spend: PHP 120,000</p>
                </article>
                <article class="card">
                    <div class="card-top"><h3>Back-to-School Promo</h3><span class="badge badge-warning">Review</span></div>
                    <p>Owner: Performance Team · Spend: PHP 80,000</p>
                </article>
                <article class="card">
                    <div class="card-top"><h3>Holiday Launch</h3><span class="badge badge-neutral">Planned</span></div>
                    <p>Owner: Creative Team · Spend: PHP 200,000</p>
                </article>
            </section>

            <section class="card section-stack">
                <h3>Create Campaign</h3>
                <form action="#" method="POST" data-validate>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="campaign-name">Campaign Name</label>
                            <input id="campaign-name" name="campaign_name" required>
                            <small class="field-error" data-error-for="campaign_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="campaign-owner">Owner</label>
                            <input id="campaign-owner" name="campaign_owner" required>
                            <small class="field-error" data-error-for="campaign_owner"></small>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="campaign-start">Start Date</label>
                            <input id="campaign-start" type="date" name="start_date" required>
                            <small class="field-error" data-error-for="start_date"></small>
                        </div>
                        <div class="form-field">
                            <label for="campaign-end">End Date</label>
                            <input id="campaign-end" type="date" name="end_date" required>
                            <small class="field-error" data-error-for="end_date"></small>
                        </div>
                    </div>
                    <button class="btn" type="submit">Save Campaign</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
