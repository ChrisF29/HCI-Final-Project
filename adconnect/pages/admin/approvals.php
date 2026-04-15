<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Approvals';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'approvals';

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
                    <a href="<?php echo e(url('pages/admin/dashboard.php?role=admin')); ?>">Admin Dashboard</a>
                    <span>/</span>
                    <span>Approvals</span>
                </nav>
                <h1>Profile approvals queue</h1>
                <p>Verify business submissions before they become publicly searchable.</p>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Category</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Nova Media Lab</td><td>Digital</td><td>Apr 14, 2026</td><td><button class="btn-ghost" type="button" data-notify="Approval action simulated.">Approve</button></td></tr>
                        <tr><td>Urban Reach</td><td>Events</td><td>Apr 13, 2026</td><td><button class="btn-ghost" type="button" data-notify="Approval action simulated.">Review</button></td></tr>
                        <tr><td>SceneCraft PH</td><td>Video</td><td>Apr 12, 2026</td><td><button class="btn-ghost" type="button" data-notify="Approval action simulated.">Approve</button></td></tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
