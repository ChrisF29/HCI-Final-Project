<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'Business Directory';
$activePage = 'directory';

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
            <p>Search by expertise, location, and budget compatibility. Listing cards are generated from static data and prepared for API or PHP rendering later.</p>
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
                    <option value="creative">Creative</option>
                    <option value="digital">Digital</option>
                    <option value="video">Video</option>
                    <option value="events">Events</option>
                </select>
                <select name="location" data-filter-select>
                    <option value="all">All Locations</option>
                    <option value="manila">Manila</option>
                    <option value="cebu">Cebu</option>
                    <option value="davao">Davao</option>
                    <option value="baguio">Baguio</option>
                </select>
                <select name="budget" data-filter-select>
                    <option value="all">All Budget Tiers</option>
                    <option value="low">Low</option>
                    <option value="mid">Mid</option>
                    <option value="high">High</option>
                </select>
                <button class="btn-ghost" type="button" data-filter-reset>Reset Filters</button>
            </div>
            <div class="card-grid" data-feed="listings"></div>
            <div class="empty-state is-hidden" data-filter-empty data-empty-state>
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
