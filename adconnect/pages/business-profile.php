<?php
require_once dirname(__DIR__) . '/includes/config.php';

$inquiryError = '';
$inquiryStatus = (string) ($_GET['inquiry'] ?? '');
$inquiryForm = [
    'contact_name' => '',
    'contact_email' => '',
    'campaign_needs' => '',
];

$pageTitle = 'Business Profile';
$activePage = 'directory';
$businessId = active_business_profile_id();
$businessProfile = fetch_business_profile($businessId);
$businessReviews = fetch_reviews_for_business($businessId, 20);
$campaignCount = $businessId !== null
    ? db_count('SELECT COUNT(*) FROM campaigns WHERE business_id = :business_id', ['business_id' => $businessId])
    : 0;
$analyticsSummary = fetch_business_analytics_summary($businessId);
$conversionLift = ($analyticsSummary['clicks'] ?? 0) > 0
    ? (($analyticsSummary['leads'] ?? 0) / $analyticsSummary['clicks']) * 100
    : 0;
$responseTimeHours = $businessId !== null
    ? (float) (db_value(
        "SELECT COALESCE(AVG(TIMESTAMPDIFF(HOUR, i.created_at, i.updated_at)), 0)
         FROM inquiries i
         WHERE i.business_id = :business_id",
        ['business_id' => $businessId]
    ) ?? 0)
    : 0;

$profileName = (string) ($businessProfile['business_name'] ?? 'Business');
$profileDescription = (string) ($businessProfile['description'] ?? 'No business profile information found.');
$profileCategory = (string) ($businessProfile['category_name'] ?? 'Uncategorized');
$profileCity = (string) ($businessProfile['city'] ?? 'Unspecified');
$profileRating = (float) ($businessProfile['rating'] ?? 0);
$profileBudget = ucfirst((string) ($businessProfile['budget_tier'] ?? 'mid'));
$profileSpecialties = is_array($businessProfile['specialties'] ?? null) ? $businessProfile['specialties'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiryForm['contact_name'] = trim((string) ($_POST['contact_name'] ?? ''));
    $inquiryForm['contact_email'] = strtolower(trim((string) ($_POST['contact_email'] ?? '')));
    $inquiryForm['campaign_needs'] = trim((string) ($_POST['campaign_needs'] ?? ''));

    if ($inquiryForm['contact_name'] === '' || $inquiryForm['campaign_needs'] === '') {
        $inquiryError = 'Please complete all inquiry fields.';
    } elseif (!filter_var($inquiryForm['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $inquiryError = 'Please provide a valid email address.';
    } elseif (!db_available()) {
        $inquiryError = 'Database is unavailable right now. Please try again.';
    } elseif ($businessId === null) {
        $inquiryError = 'Unable to identify business profile for this inquiry.';
    } else {
        $currentUserId = current_user_id();
        $isClient = has_role('client') && $currentUserId !== null;

        if ($isClient) {
            $connection = db();
            if (!$connection) {
                $inquiryError = 'Unable to submit inquiry right now.';
            } else {
                try {
                    $connection->beginTransaction();

                    $inquiryStatement = $connection->prepare(
                        'INSERT INTO inquiries (
                            client_user_id,
                            business_id,
                            campaign_need,
                            budget_amount,
                            status,
                            latest_subject,
                            latest_message
                        ) VALUES (
                            :client_user_id,
                            :business_id,
                            :campaign_need,
                            :budget_amount,
                            :status,
                            :latest_subject,
                            :latest_message
                        )'
                    );

                    $inquiryStatement->execute([
                        'client_user_id' => $currentUserId,
                        'business_id' => $businessId,
                        'campaign_need' => substr($inquiryForm['campaign_needs'], 0, 200),
                        'budget_amount' => null,
                        'status' => 'pending',
                        'latest_subject' => 'New inquiry via profile',
                        'latest_message' => $inquiryForm['campaign_needs'],
                    ]);

                    $inquiryId = (int) $connection->lastInsertId();
                    $businessUserId = (int) (db_value('SELECT user_id FROM business_profiles WHERE id = :business_id LIMIT 1', ['business_id' => $businessId]) ?? 0);

                    if ($inquiryId > 0 && $businessUserId > 0) {
                        $messageStatement = $connection->prepare(
                            'INSERT INTO messages (
                                inquiry_id,
                                sender_user_id,
                                recipient_user_id,
                                subject,
                                body,
                                message_status
                            ) VALUES (
                                :inquiry_id,
                                :sender_user_id,
                                :recipient_user_id,
                                :subject,
                                :body,
                                :message_status
                            )'
                        );

                        $messageStatement->execute([
                            'inquiry_id' => $inquiryId,
                            'sender_user_id' => $currentUserId,
                            'recipient_user_id' => $businessUserId,
                            'subject' => 'New inquiry via profile',
                            'body' => $inquiryForm['campaign_needs'],
                            'message_status' => 'open',
                        ]);
                    }

                    $connection->commit();
                    header('Location: ' . url('pages/business-profile.php?business_id=' . $businessId . '&inquiry=sent'));
                    exit;
                } catch (Throwable $exception) {
                    if ($connection->inTransaction()) {
                        $connection->rollBack();
                    }
                    $inquiryError = 'Unable to submit inquiry right now. Please try again.';
                }
            }
        } else {
            $saved = db_execute(
                'INSERT INTO support_requests (user_id, name, email, topic, message, status)
                 VALUES (:user_id, :name, :email, :topic, :message, :status)',
                [
                    'user_id' => $currentUserId,
                    'name' => $inquiryForm['contact_name'],
                    'email' => $inquiryForm['contact_email'],
                    'topic' => 'business-inquiry',
                    'message' => 'Business: ' . $profileName . "\nNeed: " . $inquiryForm['campaign_needs'],
                    'status' => 'open',
                ]
            );

            if ($saved) {
                header('Location: ' . url('pages/business-profile.php?business_id=' . $businessId . '&inquiry=queued'));
                exit;
            }

            $inquiryError = 'Unable to submit inquiry right now. Please try again.';
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <a href="<?php echo e(url('pages/directory.php')); ?>">Directory</a>
                <span>/</span>
                <span>Business Profile</span>
            </nav>
            <h1><?php echo e($profileName); ?></h1>
            <p><?php echo e($profileDescription); ?></p>
            <div class="chip-row" style="margin-top:0.85rem;">
                <span class="chip"><?php echo e($profileCategory); ?></span>
                <span class="chip"><?php echo e($profileCity); ?></span>
                <span class="chip"><?php echo e(number_format($profileRating, 1)); ?> Rating</span>
                <span class="chip"><?php echo e($profileBudget); ?> Budget Tier</span>
            </div>
        </section>

        <section class="tabs" data-tabs>
            <div class="tab-list" role="tablist" aria-label="Business tabs">
                <button class="is-active" type="button" data-tab-target="overview">Overview</button>
                <button type="button" data-tab-target="services">Services</button>
                <button type="button" data-tab-target="reviews">Reviews</button>
            </div>

            <article class="tab-panel is-active" data-tab-panel="overview">
                <div class="card section-stack">
                    <h3>About this business</h3>
                    <p><?php echo e($profileDescription); ?></p>
                    <div class="metrics">
                        <article class="metric-card">
                            <small>Campaigns Completed</small>
                            <strong data-counter="<?php echo e((string) $campaignCount); ?>">0</strong>
                        </article>
                        <article class="metric-card">
                            <small>Average Conversion Lift</small>
                            <strong>+<?php echo e(number_format($conversionLift, 1)); ?>%</strong>
                        </article>
                        <article class="metric-card">
                            <small>Response Time</small>
                            <strong><?php echo e($responseTimeHours > 0 ? number_format($responseTimeHours, 1) . ' hrs' : 'N/A'); ?></strong>
                        </article>
                    </div>
                </div>
            </article>

            <article class="tab-panel" data-tab-panel="services">
                <div class="card-grid">
                    <?php foreach ($profileSpecialties as $specialty): ?>
                        <article class="card">
                            <h3><?php echo e((string) $specialty); ?></h3>
                            <p>Service capability listed by this business profile.</p>
                        </article>
                    <?php endforeach; ?>
                    <?php if (empty($profileSpecialties)): ?>
                        <article class="card"><p>No services listed yet.</p></article>
                    <?php endif; ?>
                </div>
            </article>

            <article class="tab-panel" data-tab-panel="reviews">
                <div class="notice-list">
                    <?php foreach ($businessReviews as $review): ?>
                        <article class="notice-item">
                            &quot;<?php echo e((string) ($review['comment'] ?? '')); ?>&quot;
                            - <?php echo e((string) ($review['reviewer_name'] ?? 'Client')); ?> (<?php echo e((string) ((int) ($review['rating'] ?? 0))); ?>/5)
                        </article>
                    <?php endforeach; ?>
                    <?php if (empty($businessReviews)): ?>
                        <article class="notice-item">No reviews posted yet.</article>
                    <?php endif; ?>
                </div>
            </article>
        </section>

        <section class="card section-stack">
            <h2>Send an inquiry</h2>
            <?php if ($inquiryStatus === 'sent'): ?>
                <div class="notice-item" role="status">Inquiry sent successfully to this business.</div>
            <?php elseif ($inquiryStatus === 'queued'): ?>
                <div class="notice-item" role="status">Inquiry submitted. Sign in as a client to open tracked conversations.</div>
            <?php endif; ?>
            <?php if ($inquiryError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($inquiryError); ?></div>
            <?php endif; ?>
            <form action="<?php echo e(url('pages/business-profile.php?business_id=' . $businessId)); ?>" method="POST" data-validate data-allow-submit>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="contact-name">Your Name</label>
                        <input id="contact-name" name="contact_name" value="<?php echo e($inquiryForm['contact_name']); ?>" required>
                        <small class="field-error" data-error-for="contact_name"></small>
                    </div>
                    <div class="form-field">
                        <label for="contact-email">Your Email</label>
                        <input id="contact-email" type="email" name="contact_email" value="<?php echo e($inquiryForm['contact_email']); ?>" required>
                        <small class="field-error" data-error-for="contact_email"></small>
                    </div>
                </div>
                <div class="form-grid full">
                    <div class="form-field">
                        <label for="contact-needs">Campaign Needs</label>
                        <textarea id="contact-needs" name="campaign_needs" required data-minlength="20"><?php echo e($inquiryForm['campaign_needs']); ?></textarea>
                        <small class="field-error" data-error-for="campaign_needs"></small>
                    </div>
                </div>
                <button class="btn" type="submit">Submit Inquiry</button>
            </form>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
