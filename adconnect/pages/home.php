<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'Home';
$activePage = 'home';

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
            <div class="card-grid" data-feed="listings"></div>
            <div class="empty-state is-hidden" data-empty-state>
                <p>No businesses matched your search term.</p>
            </div>
        </section>

        <section class="card section-stack" data-search-scope data-filter-scope>
            <div class="inline-split">
                <h2>Trending Advertisement Feed</h2>
                <small><span data-filter-count>0</span> campaigns visible</small>
            </div>
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
                    <option value="all">All Status</option>
                    <option value="live">Live</option>
                    <option value="review">Review</option>
                    <option value="planned">Planned</option>
                </select>
                <button class="btn-ghost" type="button" data-filter-reset>Reset</button>
            </div>
            <div class="card-grid" data-feed="ads"></div>
            <div class="empty-state is-hidden" data-filter-empty data-empty-state>
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

<div class="modal" data-modal="inquiry-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Submit Campaign Inquiry</h3>
            <button class="btn-ghost" type="button" data-modal-close>Close</button>
        </div>
        <form action="#" method="POST" data-validate class="section-stack">
            <div class="form-grid">
                <div class="form-field">
                    <label for="inquiry-name">Full Name</label>
                    <input id="inquiry-name" name="full_name" required>
                    <small class="field-error" data-error-for="full_name"></small>
                </div>
                <div class="form-field">
                    <label for="inquiry-email">Email Address</label>
                    <input id="inquiry-email" type="email" name="email" required>
                    <small class="field-error" data-error-for="email"></small>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label for="inquiry-company">Company</label>
                    <input id="inquiry-company" name="company_name" required>
                    <small class="field-error" data-error-for="company_name"></small>
                </div>
                <div class="form-field">
                    <label for="inquiry-budget">Budget Range</label>
                    <select id="inquiry-budget" name="budget_range" required>
                        <option value="">Select budget</option>
                        <option value="under-50k">Under PHP 50,000</option>
                        <option value="50k-150k">PHP 50,000 - PHP 150,000</option>
                        <option value="150k-plus">PHP 150,000+</option>
                    </select>
                    <small class="field-error" data-error-for="budget_range"></small>
                </div>
            </div>
            <div class="form-grid full">
                <div class="form-field">
                    <label for="inquiry-message">Project Brief</label>
                    <textarea id="inquiry-message" name="project_brief" required data-minlength="20"></textarea>
                    <small class="field-error" data-error-for="project_brief"></small>
                </div>
            </div>
            <button class="btn" type="submit">Send Inquiry</button>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
