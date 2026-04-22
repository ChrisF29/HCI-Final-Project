<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Users';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'users';
$users = fetch_admin_users(200);

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
                <p>Review account status and role assignments.</p>
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
                        <?php foreach ($users as $user): ?>
                            <?php $statusLabel = ucfirst((string) ($user['status'] ?? 'unknown')); ?>
                            <tr>
                                <td><?php echo e((string) ($user['full_name'] ?? 'User')); ?></td>
                                <td><?php echo e((string) ($user['email'] ?? '')); ?></td>
                                <td><?php echo e(ucfirst((string) ($user['role'] ?? 'client'))); ?></td>
                                <td><span class="badge <?php echo e(badge_class_for_status((string) ($user['status'] ?? ''))); ?>"><?php echo e($statusLabel); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="4">No user records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
