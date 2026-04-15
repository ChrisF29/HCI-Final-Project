<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Reviews';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'reviews';

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
                        <tr><td>BrightPixel Studio</td><td>5/5</td><td>Excellent strategic support and clear updates.</td><td>Apr 8, 2026</td></tr>
                        <tr><td>Community Buzz PH</td><td>4/5</td><td>Great on-ground activation and budget control.</td><td>Mar 27, 2026</td></tr>
                        <tr><td>MetroReach Media</td><td>4/5</td><td>Good performance outcomes in search campaigns.</td><td>Mar 10, 2026</td></tr>
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
                        <option value="brightpixel">BrightPixel Studio</option>
                        <option value="metroreach">MetroReach Media</option>
                        <option value="northlight">Northlight Productions</option>
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
