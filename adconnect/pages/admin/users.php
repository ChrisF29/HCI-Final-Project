<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Users';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'users';

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
                    <span>Users</span>
                </nav>
                <h1>User management</h1>
                <p>Review account status and role assignments before backend integration.</p>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Alex Santos</td><td>alex@example.com</td><td>Client</td><td><span class="badge badge-success">Active</span></td></tr>
                        <tr><td>BrightPixel Studio</td><td>hello@brightpixel.example</td><td>Business</td><td><span class="badge badge-warning">Pending</span></td></tr>
                        <tr><td>Maria Delos Reyes</td><td>maria@agency.example</td><td>Admin</td><td><span class="badge badge-neutral">Verified</span></td></tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
