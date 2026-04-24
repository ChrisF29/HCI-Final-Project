<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$campaignError = '';
$campaignStatus = (string) ($_GET['campaign'] ?? '');
$campaignForm = [
    'campaign_name' => '',
    'campaign_owner' => '',
    'start_date' => '',
    'end_date' => '',
];

$pageTitle = 'Campaigns';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'campaigns';
$businessId = active_business_profile_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campaignForm['campaign_name'] = trim((string) ($_POST['campaign_name'] ?? ''));
    $campaignForm['campaign_owner'] = trim((string) ($_POST['campaign_owner'] ?? ''));
    $campaignForm['start_date'] = trim((string) ($_POST['start_date'] ?? ''));
    $campaignForm['end_date'] = trim((string) ($_POST['end_date'] ?? ''));

    if ($businessId === null) {
        $campaignError = 'Business profile was not found.';
    } elseif ($campaignForm['campaign_name'] === '' || $campaignForm['campaign_owner'] === '' || $campaignForm['start_date'] === '' || $campaignForm['end_date'] === '') {
        $campaignError = 'Please complete all campaign fields.';
    } elseif (strtotime($campaignForm['start_date']) === false || strtotime($campaignForm['end_date']) === false) {
        $campaignError = 'Please provide valid campaign dates.';
    } elseif (strtotime($campaignForm['start_date']) > strtotime($campaignForm['end_date'])) {
        $campaignError = 'Start date must not be later than end date.';
    } elseif (!db_available()) {
        $campaignError = 'Database is unavailable right now. Please try again.';
    } else {
        $saved = db_execute(
            'INSERT INTO campaigns (
                business_id,
                owner_name,
                name,
                objective,
                status,
                budget_amount,
                start_date,
                end_date
            ) VALUES (
                :business_id,
                :owner_name,
                :name,
                :objective,
                :status,
                :budget_amount,
                :start_date,
                :end_date
            )',
            [
                'business_id' => $businessId,
                'owner_name' => $campaignForm['campaign_owner'],
                'name' => $campaignForm['campaign_name'],
                'objective' => 'awareness',
                'status' => 'planned',
                'budget_amount' => 0,
                'start_date' => $campaignForm['start_date'],
                'end_date' => $campaignForm['end_date'],
            ]
        );

        if ($saved) {
            header('Location: ' . url('pages/business/campaigns.php?campaign=created'));
            exit;
        }

        $campaignError = 'Unable to create campaign right now. Please try again.';
    }
}

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
                            <span class="badge <?php echo e(badge_class_for_status((string) ($campaign['status'] ?? ''))); ?>"><?php echo e($statusLabel); ?></span>
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
                <?php if ($campaignStatus === 'created'): ?>
                    <div class="notice-item" role="status">Campaign created successfully.</div>
                <?php endif; ?>
                <?php if ($campaignError !== ''): ?>
                    <div class="notice-item" role="alert"><?php echo e($campaignError); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(url('pages/business/campaigns.php')); ?>" method="POST" data-validate data-allow-submit>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="campaign-name">Campaign Name</label>
                            <input id="campaign-name" name="campaign_name" value="<?php echo e($campaignForm['campaign_name']); ?>" required>
                            <small class="field-error" data-error-for="campaign_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="campaign-owner">Owner</label>
                            <input id="campaign-owner" name="campaign_owner" value="<?php echo e($campaignForm['campaign_owner']); ?>" required>
                            <small class="field-error" data-error-for="campaign_owner"></small>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="campaign-start">Start Date</label>
                            <input id="campaign-start" type="date" name="start_date" value="<?php echo e($campaignForm['start_date']); ?>" required>
                            <small class="field-error" data-error-for="start_date"></small>
                        </div>
                        <div class="form-field">
                            <label for="campaign-end">End Date</label>
                            <input id="campaign-end" type="date" name="end_date" value="<?php echo e($campaignForm['end_date']); ?>" required>
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
