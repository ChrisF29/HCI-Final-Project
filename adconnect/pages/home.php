<?php
require_once dirname(__DIR__) . '/includes/config.php';

$inquiryError = '';
$inquiryStatus = (string) ($_GET['inquiry'] ?? '');
$inquiryForm = [
    'full_name' => '',
    'email' => '',
    'company_name' => '',
    'budget_range' => '',
    'project_brief' => '',
];

$currentUserId = current_user_id();
$isClientUser = has_role('client') && $currentUserId !== null;
$clientContact = $isClientUser ? current_user_contact_profile() : null;

if ($isClientUser && $clientContact) {
    $inquiryForm['full_name'] = (string) ($clientContact['name'] ?? '');
    $inquiryForm['email'] = (string) ($clientContact['email'] ?? '');
}

$showInquiryModal = false;
$previewId = query_int('preview_id');
$previewError = '';
$previewAd = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiryForm['company_name'] = trim((string) ($_POST['company_name'] ?? ''));
    $inquiryForm['budget_range'] = trim((string) ($_POST['budget_range'] ?? ''));
    $inquiryForm['project_brief'] = trim((string) ($_POST['project_brief'] ?? ''));

    if ($isClientUser && $clientContact) {
        $inquiryForm['full_name'] = (string) ($clientContact['name'] ?? '');
        $inquiryForm['email'] = (string) ($clientContact['email'] ?? '');
    } else {
        $inquiryForm['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
        $inquiryForm['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    }

    if ($inquiryForm['company_name'] === '' || $inquiryForm['budget_range'] === '' || $inquiryForm['project_brief'] === '') {
        $inquiryError = 'Please complete all inquiry fields.';
    } elseif ($isClientUser && !$clientContact) {
        $inquiryError = 'Unable to load your account contact details. Please update your profile and try again.';
    } elseif (!$isClientUser && $inquiryForm['full_name'] === '') {
        $inquiryError = 'Please complete all inquiry fields.';
    } elseif (!filter_var($inquiryForm['email'], FILTER_VALIDATE_EMAIL)) {
        $inquiryError = 'Please provide a valid email address.';
    } elseif (!db_available()) {
        $inquiryError = 'Database is unavailable right now. Please try again.';
    } else {
        $message = 'Company: ' . $inquiryForm['company_name']
            . "\nBudget: " . $inquiryForm['budget_range']
            . "\nBrief: " . $inquiryForm['project_brief'];

        $saved = db_execute(
            'INSERT INTO support_requests (user_id, name, email, topic, message, status)
             VALUES (:user_id, :name, :email, :topic, :message, :status)',
            [
                'user_id' => $currentUserId,
                'name' => $inquiryForm['full_name'],
                'email' => $inquiryForm['email'],
                'topic' => 'campaign-inquiry',
                'message' => $message,
                'status' => 'open',
            ]
        );

        if ($saved) {
            header('Location: ' . url('pages/home.php?inquiry=sent'));
            exit;
        }

        $inquiryError = 'We could not submit your inquiry. Please try again.';
    }
}

$showInquiryModal = $inquiryStatus !== '' || $inquiryError !== '';

$pageTitle = 'Home';
$activePage = 'home';
$clientUserId = active_client_user_id();
$featuredListings = fetch_business_listings(8, $clientUserId, false);
$trendingAds = fetch_ads_feed(8, null, 'live');

if ($previewId !== null) {
    $previewAd = fetch_moderation_ad_detail($previewId);
    if (!$previewAd) {
        $previewError = 'No campaign found for the selected preview entry.';
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('index.php')); ?>">Home</a>
                <span>/</span>
                <span>Dashboard Overview</span>
            </nav>
            <h1>Find the right advertising partner in minutes</h1>
            <p>AdConnect matches clients with trusted businesses, showcases active campaigns, and keeps communication clear through role-based dashboards.</p>
            <div class="hero-actions">
                <a class="btn" href="<?php echo e(url('pages/directory.php')); ?>">Browse Directory</a>
                <a class="btn-secondary" href="<?php echo e(url('pages/ads.php')); ?>">View Ad Feed</a>
                <button class="btn-ghost" type="button" data-modal-target="inquiry-modal">Quick Inquiry</button>
            </div>
        </section>

        <section class="card section-stack" data-search-scope>
            <div class="inline-split">
                <h2>Featured Businesses</h2>
                <small><span data-search-count>0</span> results</small>
            </div>
            <div class="toolbar">
                <input type="search" data-search-input placeholder="Search by category, city, or skill">
                <a class="btn-ghost" href="<?php echo e(url('pages/directory.php')); ?>">Advanced Filters</a>
            </div>
            <div class="card-grid">
                <?php foreach ($featuredListings as $listing): ?>
                    <?php echo render_listing_card($listing); ?>
                <?php endforeach; ?>
            </div>
            <div class="empty-state <?php echo !empty($featuredListings) ? 'is-hidden' : ''; ?>" data-empty-state>
                <p>No businesses matched your search term.</p>
            </div>
        </section>

        <section id="trending-ads-feed" class="card section-stack" data-search-scope data-filter-scope>
            <div class="inline-split">
                <h2>Trending Advertisement Feed</h2>
                <small><span data-filter-count>0</span> campaigns visible</small>
            </div>
            <?php if ($previewError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($previewError); ?></div>
            <?php endif; ?>
            <?php if ($previewAd): ?>
                <section id="ad-preview" class="card section-stack">
                    <h3>Campaign Preview</h3>
                    <div class="chip-row">
                        <span class="chip"><?php echo e(ucfirst((string) ($previewAd['status'] ?? 'planned'))); ?></span>
                        <span class="chip"><?php echo e(ucfirst((string) ($previewAd['channel'] ?? 'social'))); ?></span>
                        <span class="chip"><?php echo e((string) ($previewAd['category_name'] ?? 'Uncategorized')); ?></span>
                    </div>
                    <p><strong>Title:</strong> <?php echo e((string) ($previewAd['title'] ?? 'Untitled ad')); ?></p>
                    <p><strong>Owner:</strong> <?php echo e((string) ($previewAd['owner_name'] ?? 'Unknown owner')); ?></p>
                    <p><strong>Objective:</strong> <?php echo e(ucfirst((string) ($previewAd['objective'] ?? 'awareness'))); ?></p>
                    <p><strong>Location:</strong> <?php echo e((string) ($previewAd['location'] ?? 'Unspecified')); ?></p>
                    <p><strong>Budget:</strong> <?php echo e(money((float) ($previewAd['budget_amount'] ?? 0))); ?></p>
                    <p><?php echo e((string) ($previewAd['description'] ?? 'No campaign description provided.')); ?></p>
                    <div class="hero-actions">
                        <a class="btn-secondary" href="<?php echo e(url('pages/home.php#trending-ads-feed')); ?>">Close Preview</a>
                    </div>
                </section>
            <?php endif; ?>
            <div class="toolbar">
                <input type="search" data-search-input placeholder="Search campaign title or owner">
                <select name="channel" data-filter-select>
                    <option value="all">All Channels</option>
                    <option value="social">Social</option>
                    <option value="search">Search</option>
                    <option value="video">Video</option>
                    <option value="events">Events</option>
                </select>
                <select name="status" data-filter-select>
                    <option value="live">Live</option>
                </select>
                <button class="btn-ghost" type="button" data-filter-reset>Reset</button>
            </div>
            <div class="card-grid">
                <?php foreach ($trendingAds as $ad): ?>
                    <?php echo render_ad_card($ad, url('pages/home.php?preview_id=' . (string) ((int) ($ad['id'] ?? 0)) . '#ad-preview')); ?>
                <?php endforeach; ?>
            </div>
            <div class="empty-state <?php echo !empty($trendingAds) ? 'is-hidden' : ''; ?>" data-filter-empty data-empty-state>
                <p>No campaigns matched your filters.</p>
            </div>
        </section>

        <section class="tabs" data-tabs>
            <div class="tab-list" role="tablist" aria-label="How AdConnect Works">
                <button class="is-active" type="button" data-tab-target="step-1">1. Discover</button>
                <button type="button" data-tab-target="step-2">2. Connect</button>
                <button type="button" data-tab-target="step-3">3. Scale</button>
            </div>
            <article class="tab-panel is-active" data-tab-panel="step-1">
                <h3>Discover high-fit businesses</h3>
                <p>Use role-aware filters and curated listings to quickly find agencies and freelancers aligned with your campaign needs.</p>
            </article>
            <article class="tab-panel" data-tab-panel="step-2">
                <h3>Connect through structured inquiries</h3>
                <p>Submit inquiries, track replies, and compare offers with a clear message flow that prepares data for PHP handling later.</p>
            </article>
            <article class="tab-panel" data-tab-panel="step-3">
                <h3>Scale with campaign analytics</h3>
                <p>Move from first campaign to repeat growth using dashboard KPIs, feed monitoring, and ad moderation workflows.</p>
            </article>
        </section>
    </div>
</main>

<div class="modal <?php echo $showInquiryModal ? 'is-open' : ''; ?>" data-modal="inquiry-modal" aria-hidden="<?php echo $showInquiryModal ? 'false' : 'true'; ?>">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Submit Campaign Inquiry</h3>
            <button class="btn-ghost" type="button" data-modal-close>Close</button>
        </div>
        <?php if ($inquiryStatus === 'sent'): ?>
            <div class="notice-item" role="status">Inquiry submitted successfully. Our team will contact you soon.</div>
        <?php endif; ?>
        <?php if ($inquiryError !== ''): ?>
            <div class="notice-item" role="alert"><?php echo e($inquiryError); ?></div>
        <?php endif; ?>
        <form action="<?php echo e(url('pages/home.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
            <?php if ($isClientUser && $clientContact): ?>
                <div class="notice-item" role="status">
                    Sending inquiry as <?php echo e((string) ($clientContact['name'] ?? 'Client')); ?> (<?php echo e((string) ($clientContact['email'] ?? '')); ?>).
                </div>
            <?php else: ?>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="inquiry-name">Full Name</label>
                        <input id="inquiry-name" name="full_name" value="<?php echo e($inquiryForm['full_name']); ?>" required>
                        <small class="field-error" data-error-for="full_name"></small>
                    </div>
                    <div class="form-field">
                        <label for="inquiry-email">Email Address</label>
                        <input id="inquiry-email" type="email" name="email" value="<?php echo e($inquiryForm['email']); ?>" required>
                        <small class="field-error" data-error-for="email"></small>
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-grid">
                <div class="form-field">
                    <label for="inquiry-company">Company</label>
                    <input id="inquiry-company" name="company_name" value="<?php echo e($inquiryForm['company_name']); ?>" required>
                    <small class="field-error" data-error-for="company_name"></small>
                </div>
                <div class="form-field">
                    <label for="inquiry-budget">Budget Range</label>
                    <select id="inquiry-budget" name="budget_range" required>
                        <option value="">Select budget</option>
                        <option value="under-50k" <?php echo $inquiryForm['budget_range'] === 'under-50k' ? 'selected' : ''; ?>>Under PHP 50,000</option>
                        <option value="50k-150k" <?php echo $inquiryForm['budget_range'] === '50k-150k' ? 'selected' : ''; ?>>PHP 50,000 - PHP 150,000</option>
                        <option value="150k-plus" <?php echo $inquiryForm['budget_range'] === '150k-plus' ? 'selected' : ''; ?>>PHP 150,000+</option>
                    </select>
                    <small class="field-error" data-error-for="budget_range"></small>
                </div>
            </div>
            <div class="form-grid full">
                <div class="form-field">
                    <label for="inquiry-message">Project Brief</label>
                    <textarea id="inquiry-message" name="project_brief" required data-minlength="20"><?php echo e($inquiryForm['project_brief']); ?></textarea>
                    <small class="field-error" data-error-for="project_brief"></small>
                </div>
            </div>
            <button class="btn" type="submit">Send Inquiry</button>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
