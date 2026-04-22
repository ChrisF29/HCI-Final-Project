<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'Business Directory';
$activePage = 'directory';
$clientUserId = active_client_user_id();
$listings = fetch_business_listings(60, $clientUserId, false);
$categoryRows = fetch_categories_with_counts();

$locations = [];
$budgets = [];
foreach ($listings as $listing) {
    $city = trim((string) ($listing['city'] ?? ''));
    if ($city !== '') {
        $locations[strtolower($city)] = $city;
    }

    $budget = strtolower(trim((string) ($listing['budget_tier'] ?? '')));
    if ($budget !== '') {
        $budgets[$budget] = ucfirst($budget);
    }
}
ksort($locations);
ksort($budgets);

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <span>Business Directory</span>
            </nav>
            <h1>Browse verified businesses</h1>
            <p>Search by expertise, location, and budget compatibility across verified business profiles.</p>
        </section>

        <section class="card section-stack" data-search-scope data-filter-scope>
            <div class="inline-split">
                <h2>Directory Results</h2>
                <small><span data-filter-count>0</span> listings available</small>
            </div>
            <div class="toolbar">
                <input type="search" data-search-input placeholder="Search by name, service, or city">
                <select name="category" data-filter-select>
                    <option value="all">All Categories</option>
                    <?php foreach ($categoryRows as $category): ?>
                        <option value="<?php echo e((string) ($category['slug'] ?? '')); ?>"><?php echo e((string) ($category['name'] ?? 'Category')); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="location" data-filter-select>
                    <option value="all">All Locations</option>
                    <?php foreach ($locations as $locationKey => $locationLabel): ?>
                        <option value="<?php echo e($locationKey); ?>"><?php echo e($locationLabel); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="budget" data-filter-select>
                    <option value="all">All Budget Tiers</option>
                    <?php foreach ($budgets as $budgetKey => $budgetLabel): ?>
                        <option value="<?php echo e($budgetKey); ?>"><?php echo e($budgetLabel); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-ghost" type="button" data-filter-reset>Reset Filters</button>
            </div>
            <div class="card-grid">
                <?php foreach ($listings as $listing): ?>
                    <?php echo render_listing_card($listing); ?>
                <?php endforeach; ?>
            </div>
            <div class="empty-state <?php echo !empty($listings) ? 'is-hidden' : ''; ?>" data-filter-empty data-empty-state>
                <p>No listing matched the selected filters.</p>
            </div>
        </section>

        <section class="card">
            <div class="inline-split">
                <div>
                    <h3>Need tailored recommendations?</h3>
                    <p>Our onboarding flow can suggest top matches based on your campaign goals and timeline.</p>
                </div>
                <a class="btn" href="<?php echo e(url('pages/auth/register.php')); ?>">Create an Account</a>
            </div>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
