<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Approvals';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'approvals';
$pendingApprovals = fetch_pending_approvals(200);

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
                        <?php foreach ($pendingApprovals as $approval): ?>
                            <tr>
                                <td><?php echo e((string) ($approval['business_name'] ?? 'Business')); ?></td>
                                <td><?php echo e((string) ($approval['category_name'] ?? 'Uncategorized')); ?></td>
                                <td><?php echo e(format_date_label((string) ($approval['created_at'] ?? ''))); ?></td>
                                <td><button class="btn-ghost" type="button" data-notify="Approval workflow will be connected to update endpoints.">Review</button></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pendingApprovals)): ?>
                            <tr>
                                <td colspan="4">No pending approvals at the moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
