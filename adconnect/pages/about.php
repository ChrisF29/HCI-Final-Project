<?php
require_once dirname(__DIR__) . '/includes/config.php';

$pageTitle = 'About';
$activePage = 'about';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <span>About</span>
            </nav>
            <h1>Designed for better client-business matching</h1>
            <p>AdConnect combines clear UX, role-based dashboards, and backend-ready architecture to make advertising collaborations simple and trustworthy.</p>
        </section>

        <section class="card-grid">
            <article class="card">
                <h3>Mission</h3>
                <p>Help businesses and clients find strong campaign matches through transparent data and streamlined workflows.</p>
            </article>
            <article class="card">
                <h3>Vision</h3>
                <p>Become the most trusted collaboration platform for digital and local advertising services in growing markets.</p>
            </article>
            <article class="card">
                <h3>Approach</h3>
                <p>Build user-centered interfaces first, then connect secure backend services and analytics with minimal refactoring.</p>
            </article>
        </section>

        <section class="card section-stack">
            <h2>Platform principles</h2>
            <div class="card-grid">
                <article class="card"><h3>Clarity</h3><p>Every core action is visible and understandable.</p></article>
                <article class="card"><h3>Scalability</h3><p>File structure supports future PHP controllers and MySQL tables.</p></article>
                <article class="card"><h3>Safety</h3><p>Validation and role-based UI are prepared from day one.</p></article>
            </div>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
