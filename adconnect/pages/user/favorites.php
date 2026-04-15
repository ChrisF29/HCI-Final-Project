<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Favorites';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'favorites';

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
                    <a href="<?php echo e(url('pages/user/dashboard.php?role=client')); ?>">Client Dashboard</a>
                    <span>/</span>
                    <span>Favorites</span>
                </nav>
                <h1>Saved businesses</h1>
                <p>Review your shortlist and refine by location and category.</p>
            </section>

            <section class="card section-stack">
                <div class="toolbar">
                    <input type="search" data-search-input placeholder="Search saved businesses">
                    <select name="category" data-filter-select>
                        <option value="all">All Categories</option>
                        <option value="creative">Creative</option>
                        <option value="digital">Digital</option>
                        <option value="video">Video</option>
                        <option value="events">Events</option>
                    </select>
                    <button class="btn-ghost" type="button" data-filter-reset>Clear</button>
                </div>
                <p><strong><span data-filter-count>0</span></strong> items found.</p>
                <div class="card-grid" data-feed="listings"></div>
                <div class="empty-state is-hidden" data-filter-empty data-empty-state>
                    <p>No favorites matched your current filters.</p>
                </div>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
