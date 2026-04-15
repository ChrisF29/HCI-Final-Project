<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = '404 Not Found';
$activePage = '';

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack" style="max-width:760px;">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <span>404</span>
            </nav>
            <h1>404: Page not found</h1>
            <p>The page you are looking for does not exist or may have been moved.</p>
            <div class="hero-actions">
                <a class="btn" href="<?php echo e(url('pages/home.php')); ?>">Go to Home</a>
                <a class="btn-ghost" href="<?php echo e(url('pages/help.php')); ?>">Visit Help Center</a>
            </div>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
