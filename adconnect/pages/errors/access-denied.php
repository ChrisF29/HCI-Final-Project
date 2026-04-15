<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Access Denied';
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
                <span>Access Denied</span>
            </nav>
            <h1>Access denied</h1>
            <p>Your current role does not have permission to view this page. Switch portal views or sign in with an authorized account.</p>
            <div class="hero-actions">
                <a class="btn" href="<?php echo e(url('pages/auth/login.php')); ?>">Login</a>
                <a class="btn-ghost" href="<?php echo e(url('pages/home.php')); ?>">Return Home</a>
            </div>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
