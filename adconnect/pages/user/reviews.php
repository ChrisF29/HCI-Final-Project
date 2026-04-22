<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Reviews';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'reviews';
$clientUserId = active_client_user_id();
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
        <form action="#" method="POST" data-validate class="section-stack">
            <div class="form-grid">
                <div class="form-field">
                    <label for="review-business">Business</label>
                    <select id="review-business" name="review_business" required>
                        <option value="">Select business</option>
                            <?php foreach ($reviewableBusinesses as $business): ?>
                                <option value="<?php echo e((string) ($business['id'] ?? '')); ?>"><?php echo e((string) ($business['business_name'] ?? 'Business')); ?></option>
                            <?php endforeach; ?>
                    </select>
                    <small class="field-error" data-error-for="review_business"></small>
                </div>
                <div class="form-field">
                    <label for="review-rating">Rating</label>
                    <select id="review-rating" name="review_rating" required>
                        <option value="">Select rating</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                    <small class="field-error" data-error-for="review_rating"></small>
                </div>
            </div>
            <div class="form-grid full">
                <div class="form-field">
                    <label for="review-comment">Comment</label>
                    <textarea id="review-comment" name="review_comment" required data-minlength="20"></textarea>
                    <small class="field-error" data-error-for="review_comment"></small>
                </div>
            </div>
            <button class="btn" type="submit">Publish Review</button>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
