<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$reviewError = '';
$reviewStatus = (string) ($_GET['review'] ?? '');
$reviewForm = [
    'review_business' => '',
    'review_rating' => '',
    'review_comment' => '',
];

$pageTitle = 'Reviews';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'reviews';
$clientUserId = active_client_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewForm['review_business'] = trim((string) ($_POST['review_business'] ?? ''));
    $reviewForm['review_rating'] = trim((string) ($_POST['review_rating'] ?? ''));
    $reviewForm['review_comment'] = trim((string) ($_POST['review_comment'] ?? ''));

    $userId = current_user_id();
    $businessId = ctype_digit($reviewForm['review_business']) ? (int) $reviewForm['review_business'] : 0;
    $rating = ctype_digit($reviewForm['review_rating']) ? (int) $reviewForm['review_rating'] : 0;

    if ($businessId <= 0 || $rating < 1 || $rating > 5 || $reviewForm['review_comment'] === '') {
        $reviewError = 'Please complete all review fields correctly.';
    } elseif ($userId === null) {
        $reviewError = 'You must be signed in to publish a review.';
    } elseif (!db_available()) {
        $reviewError = 'Database is unavailable right now. Please try again.';
    } else {
        $businessExists = db_value('SELECT id FROM business_profiles WHERE id = :business_id LIMIT 1', ['business_id' => $businessId]);
        if (!$businessExists) {
            $reviewError = 'Selected business was not found.';
        } else {
            $saved = db_execute(
                'INSERT INTO reviews (business_id, client_user_id, rating, comment)
                 VALUES (:business_id, :client_user_id, :rating, :comment)',
                [
                    'business_id' => $businessId,
                    'client_user_id' => $userId,
                    'rating' => $rating,
                    'comment' => $reviewForm['review_comment'],
                ]
            );

            if ($saved) {
                $avgRating = (float) (db_value(
                    'SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE business_id = :business_id',
                    ['business_id' => $businessId]
                ) ?? 0);

                db_execute(
                    'UPDATE business_profiles SET rating = :rating WHERE id = :business_id',
                    ['rating' => number_format($avgRating, 2, '.', ''), 'business_id' => $businessId]
                );

                header('Location: ' . url('pages/user/reviews.php?review=posted'));
                exit;
            }

            $reviewError = 'Unable to publish review right now. Please try again.';
        }
    }
}

$reviews = fetch_reviews_for_client($clientUserId, 200);
$reviewableBusinesses = fetch_business_listings(100, $clientUserId, false);

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
                    <a href="<?php echo e(url('pages/user/dashboard.php?role=client')); ?>">Client Dashboard</a>
                    <span>/</span>
                    <span>Reviews</span>
                </nav>
                <h1>Your published reviews</h1>
                <p>Monitor ratings and maintain transparent feedback history.</p>
                <div class="hero-actions">
                    <button class="btn" type="button" data-modal-target="review-modal">Write a Review</button>
                </div>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?php echo e((string) ($review['business_name'] ?? 'Business')); ?></td>
                                <td><?php echo e((string) ((int) ($review['rating'] ?? 0))); ?>/5</td>
                                <td><?php echo e((string) ($review['comment'] ?? '')); ?></td>
                                <td><?php echo e(format_date_label((string) ($review['created_at'] ?? ''))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="4">No reviews published yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</main>

<div class="modal" data-modal="review-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Write a new review</h3>
            <button class="btn-ghost" type="button" data-modal-close>Close</button>
        </div>
        <?php if ($reviewStatus === 'posted'): ?>
            <div class="notice-item" role="status">Review published successfully.</div>
        <?php endif; ?>
        <?php if ($reviewError !== ''): ?>
            <div class="notice-item" role="alert"><?php echo e($reviewError); ?></div>
        <?php endif; ?>
        <form action="<?php echo e(url('pages/user/reviews.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
            <div class="form-grid">
                <div class="form-field">
                    <label for="review-business">Business</label>
                    <select id="review-business" name="review_business" required>
                        <option value="">Select business</option>
                            <?php foreach ($reviewableBusinesses as $business): ?>
                                <?php $businessIdOption = (string) ($business['id'] ?? ''); ?>
                                <option value="<?php echo e($businessIdOption); ?>" <?php echo $reviewForm['review_business'] === $businessIdOption ? 'selected' : ''; ?>><?php echo e((string) ($business['business_name'] ?? 'Business')); ?></option>
                            <?php endforeach; ?>
                    </select>
                    <small class="field-error" data-error-for="review_business"></small>
                </div>
                <div class="form-field">
                    <label for="review-rating">Rating</label>
                    <select id="review-rating" name="review_rating" required>
                        <option value="">Select rating</option>
                        <option value="5" <?php echo $reviewForm['review_rating'] === '5' ? 'selected' : ''; ?>>5</option>
                        <option value="4" <?php echo $reviewForm['review_rating'] === '4' ? 'selected' : ''; ?>>4</option>
                        <option value="3" <?php echo $reviewForm['review_rating'] === '3' ? 'selected' : ''; ?>>3</option>
                        <option value="2" <?php echo $reviewForm['review_rating'] === '2' ? 'selected' : ''; ?>>2</option>
                        <option value="1" <?php echo $reviewForm['review_rating'] === '1' ? 'selected' : ''; ?>>1</option>
                    </select>
                    <small class="field-error" data-error-for="review_rating"></small>
                </div>
            </div>
            <div class="form-grid full">
                <div class="form-field">
                    <label for="review-comment">Comment</label>
                    <textarea id="review-comment" name="review_comment" required data-minlength="20"><?php echo e($reviewForm['review_comment']); ?></textarea>
                    <small class="field-error" data-error-for="review_comment"></small>
                </div>
            </div>
            <button class="btn" type="submit">Publish Review</button>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
