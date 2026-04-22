<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Campaigns';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'campaigns';
$businessId = active_business_profile_id();
$campaigns = fetch_business_campaigns($businessId, 60);

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
                <?php foreach ($campaigns as $campaign): ?>
                    <?php $statusLabel = ucfirst((string) ($campaign['status'] ?? 'planned')); ?>
                    <article class="card">
                        <div class="card-top">
                            <h3><?php echo e((string) ($campaign['name'] ?? 'Campaign')); ?></h3>
                            <span class="badge <?php echo e(badge_class_for_status((string) ($campaign['status'] ?? '')); ?>"><?php echo e($statusLabel); ?></span>
                        </div>
                        <p>
                            Owner: <?php echo e((string) ($campaign['owner_name'] ?? 'Unassigned')); ?>
                            · Spend: <?php echo e(money((float) ($campaign['budget_amount'] ?? 0))); ?>
                        </p>
                    </article>
                <?php endforeach; ?>
                <?php if (empty($campaigns)): ?>
                    <article class="card">
                        <p>No campaigns found. Create your first campaign below.</p>
                    </article>
                <?php endif; ?>
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
