<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$adError = '';
$adStatus = (string) ($_GET['ad'] ?? '');
$adForm = [
    'ad_title' => '',
    'ad_channel' => '',
    'ad_budget' => '',
    'ad_objective' => '',
    'ad_description' => '',
];

$pageTitle = 'Manage Ads';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'manage-ads';
$businessId = active_business_profile_id();
$previewId = query_int('preview_id');
$previewError = '';
$previewAd = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adForm['ad_title'] = trim((string) ($_POST['ad_title'] ?? ''));
    $adForm['ad_channel'] = strtolower(trim((string) ($_POST['ad_channel'] ?? '')));
    $adForm['ad_budget'] = trim((string) ($_POST['ad_budget'] ?? ''));
    $adForm['ad_objective'] = strtolower(trim((string) ($_POST['ad_objective'] ?? '')));
    $adForm['ad_description'] = trim((string) ($_POST['ad_description'] ?? ''));

    $budgetAmount = (float) preg_replace('/[^0-9.]/', '', $adForm['ad_budget']);
    $allowedChannels = ['social', 'search', 'video', 'events'];
    $allowedObjectives = ['awareness', 'engagement', 'sales', 'leads'];

    if ($businessId === null) {
        $adError = 'Business profile was not found.';
    } elseif ($adForm['ad_title'] === '' || $adForm['ad_description'] === '' || $adForm['ad_budget'] === '') {
        $adError = 'Please complete all advertisement fields.';
    } elseif (!in_array($adForm['ad_channel'], $allowedChannels, true) || !in_array($adForm['ad_objective'], $allowedObjectives, true)) {
        $adError = 'Please select valid channel and objective values.';
    } elseif ($budgetAmount <= 0) {
        $adError = 'Please provide a valid budget amount.';
    } elseif (!db_available()) {
        $adError = 'Database is unavailable right now. Please try again.';
    } else {
        $connection = db();
        if (!$connection) {
            $adError = 'Unable to submit ad right now.';
        } else {
            try {
                $connection->beginTransaction();

                $campaignId = (int) (db_value(
                    "SELECT id FROM campaigns WHERE business_id = :business_id ORDER BY updated_at DESC, id DESC LIMIT 1",
                    ['business_id' => $businessId]
                ) ?? 0);

                if ($campaignId <= 0) {
                    $campaignStatement = $connection->prepare(
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
                            CURRENT_DATE,
                            DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
                        )'
                    );

                    $campaignStatement->execute([
                        'business_id' => $businessId,
                        'owner_name' => 'Business Team',
                        'name' => 'General Campaign',
                        'objective' => $adForm['ad_objective'],
                        'status' => 'planned',
                        'budget_amount' => $budgetAmount,
                    ]);

                    $campaignId = (int) $connection->lastInsertId();
                }

                $adStatement = $connection->prepare(
                    'INSERT INTO ads (
                        campaign_id,
                        business_id,
                        title,
                        channel,
                        location,
                        status,
                        objective,
                        budget_amount,
                        description,
                        moderation_notes,
                        published_at
                    ) VALUES (
                        :campaign_id,
                        :business_id,
                        :title,
                        :channel,
                        :location,
                        :status,
                        :objective,
                        :budget_amount,
                        :description,
                        :moderation_notes,
                        NULL
                    )'
                );

                $adStatement->execute([
                    'campaign_id' => $campaignId,
                    'business_id' => $businessId,
                    'title' => $adForm['ad_title'],
                    'channel' => $adForm['ad_channel'],
                    'location' => 'Unspecified',
                    'status' => 'live',
                    'objective' => $adForm['ad_objective'],
                    'budget_amount' => $budgetAmount,
                    'description' => $adForm['ad_description'],
                    'moderation_notes' => 'Published via manage ads form',
                ]);

                $connection->commit();
                header('Location: ' . url('pages/business/manage-ads.php?ad=created'));
                exit;
            } catch (Throwable $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }

                $adError = 'Unable to submit ad right now. Please try again.';
            }
        }
    }
}

$businessAds = fetch_ads_feed(60, $businessId);

if ($previewId !== null) {
    if ($businessId === null) {
        $previewError = 'Business profile was not found.';
    } else {
        $previewAd = fetch_business_ad_detail($previewId, $businessId);
        if (!$previewAd) {
            $previewError = 'No campaign found for the selected preview entry.';
        }
    }
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container content-grid">
        <?php require dirname(__DIR__, 2) . '/includes/sidebar.php'; ?>

        <div class="section-stack" data-search-scope data-filter-scope>
            <section class="page-hero">
                <nav class="breadcrumbs" aria-label="Breadcrumb">
                    <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                    <span>/</span>
                    <a href="<?php echo e(url('pages/business/dashboard.php?role=business')); ?>">Business Dashboard</a>
                    <span>/</span>
                    <span>Manage Ads</span>
                </nav>
                <h1>Create and monitor advertisements</h1>
                <p>Upload campaigns with validation-ready forms and monitor publishing states.</p>
            </section>

            <section class="card section-stack">
                <h3>Upload New Advertisement</h3>
                <?php if ($adStatus === 'created'): ?>
                    <div class="notice-item" role="status">Advertisement published successfully.</div>
                <?php endif; ?>
                <?php if ($adError !== ''): ?>
                    <div class="notice-item" role="alert"><?php echo e($adError); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(url('pages/business/manage-ads.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="ad-title">Ad Title</label>
                            <input id="ad-title" name="ad_title" value="<?php echo e($adForm['ad_title']); ?>" required>
                            <small class="field-error" data-error-for="ad_title"></small>
                        </div>
                        <div class="form-field">
                            <label for="ad-channel">Channel</label>
                            <select id="ad-channel" name="ad_channel" required>
                                <option value="">Select channel</option>
                                <option value="social" <?php echo $adForm['ad_channel'] === 'social' ? 'selected' : ''; ?>>Social</option>
                                <option value="search" <?php echo $adForm['ad_channel'] === 'search' ? 'selected' : ''; ?>>Search</option>
                                <option value="video" <?php echo $adForm['ad_channel'] === 'video' ? 'selected' : ''; ?>>Video</option>
                                <option value="events" <?php echo $adForm['ad_channel'] === 'events' ? 'selected' : ''; ?>>Events</option>
                            </select>
                            <small class="field-error" data-error-for="ad_channel"></small>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="ad-budget">Budget</label>
                            <input id="ad-budget" name="ad_budget" value="<?php echo e($adForm['ad_budget']); ?>" required>
                            <small class="field-error" data-error-for="ad_budget"></small>
                        </div>
                        <div class="form-field">
                            <label for="ad-objective">Objective</label>
                            <select id="ad-objective" name="ad_objective" required>
                                <option value="">Select objective</option>
                                <option value="awareness" <?php echo $adForm['ad_objective'] === 'awareness' ? 'selected' : ''; ?>>Awareness</option>
                                <option value="engagement" <?php echo $adForm['ad_objective'] === 'engagement' ? 'selected' : ''; ?>>Engagement</option>
                                <option value="sales" <?php echo $adForm['ad_objective'] === 'sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="leads" <?php echo $adForm['ad_objective'] === 'leads' ? 'selected' : ''; ?>>Leads</option>
                            </select>
                            <small class="field-error" data-error-for="ad_objective"></small>
                        </div>
                    </div>

                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="ad-description">Description</label>
                            <textarea id="ad-description" name="ad_description" required data-minlength="20"><?php echo e($adForm['ad_description']); ?></textarea>
                            <small class="field-error" data-error-for="ad_description"></small>
                        </div>
                    </div>

                    <button class="btn" type="submit">Submit Ad</button>
                </form>
            </section>

            <?php if ($previewError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($previewError); ?></div>
            <?php endif; ?>

            <?php if ($previewAd): ?>
                <section id="ad-preview" class="card section-stack">
                    <h3>Campaign Preview</h3>
                    <div class="chip-row">
                        <span class="chip"><?php echo e(ucfirst((string) ($previewAd['status'] ?? 'planned'))); ?></span>
                        <span class="chip"><?php echo e(ucfirst((string) ($previewAd['channel'] ?? 'social'))); ?></span>
                    </div>
                    <p><strong>Title:</strong> <?php echo e((string) ($previewAd['title'] ?? 'Untitled ad')); ?></p>
                    <p><strong>Objective:</strong> <?php echo e(ucfirst((string) ($previewAd['objective'] ?? 'awareness'))); ?></p>
                    <p><strong>Location:</strong> <?php echo e((string) ($previewAd['location'] ?? 'Unspecified')); ?></p>
                    <p><strong>Budget:</strong> <?php echo e(money((float) ($previewAd['budget_amount'] ?? 0))); ?></p>
                    <p><strong>Created:</strong> <?php echo e(format_date_label((string) ($previewAd['created_at'] ?? ''))); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo e(format_date_label((string) ($previewAd['updated_at'] ?? ''))); ?></p>
                    <p><strong>Published:</strong> <?php echo e((string) ($previewAd['published_at'] ?? '') !== '' ? format_date_label((string) $previewAd['published_at']) : 'Not published'); ?></p>
                    <p><?php echo e((string) ($previewAd['description'] ?? 'No campaign description provided.')); ?></p>
                    <p><strong>Moderation Notes:</strong> <?php echo e((string) ($previewAd['moderation_notes'] ?? 'No moderation notes yet.')); ?></p>
                    <div class="hero-actions">
                        <a class="btn-secondary" href="<?php echo e(url('pages/business/manage-ads.php#ads-list')); ?>">Close Preview</a>
                    </div>
                </section>
            <?php endif; ?>

            <section id="ads-list" class="card section-stack">
                <div class="toolbar">
                    <input type="search" data-search-input placeholder="Search ad title">
                    <select name="status" data-filter-select>
                        <option value="all">All Status</option>
                        <option value="live">Live</option>
                        <option value="planned">Planned</option>
                    </select>
                    <button class="btn-ghost" type="button" data-filter-reset>Reset</button>
                </div>
                <p><strong><span data-filter-count>0</span></strong> ads displayed.</p>
                <div class="card-grid">
                    <?php foreach ($businessAds as $ad): ?>
                        <?php echo render_ad_card($ad, url('pages/business/manage-ads.php?preview_id=' . (string) ((int) ($ad['id'] ?? 0)) . '#ad-preview')); ?>
                    <?php endforeach; ?>
                </div>
                <div class="empty-state <?php echo !empty($businessAds) ? 'is-hidden' : ''; ?>" data-filter-empty data-empty-state>
                    <p>No ad matched your search or status filter.</p>
                </div>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
