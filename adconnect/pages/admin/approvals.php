<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Approvals';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'approvals';

$approvalError = '';
$approvalNotice = '';
$approvalResult = strtolower(trim((string) ($_GET['result'] ?? '')));
$approvalBusiness = trim((string) ($_GET['business'] ?? ''));
$reviewId = query_int('review_id');
$reviewTarget = null;

if ($approvalResult === 'approved') {
    $approvalNotice = $approvalBusiness !== ''
        ? $approvalBusiness . ' has been approved.'
        : 'Business profile has been approved.';
} elseif ($approvalResult === 'rejected') {
    $approvalNotice = $approvalBusiness !== ''
        ? $approvalBusiness . ' has been rejected.'
        : 'Business profile has been rejected.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decision = strtolower(trim((string) ($_POST['decision'] ?? '')));
    $approvalIdRaw = trim((string) ($_POST['approval_id'] ?? ''));
    $approvalId = preg_match('/^\d+$/', $approvalIdRaw) ? (int) $approvalIdRaw : null;

    if ($approvalId === null || !in_array($decision, ['approve', 'reject'], true)) {
        $approvalError = 'Invalid approval request.';
    } elseif (!db_available()) {
        $approvalError = 'Database is unavailable right now. Please try again.';
    } else {
        $existingApproval = db_one(
            'SELECT id, business_name, approval_status FROM business_profiles WHERE id = :id LIMIT 1',
            ['id' => $approvalId]
        );

        if (!$existingApproval) {
            $approvalError = 'Business profile was not found.';
        } elseif (strtolower((string) ($existingApproval['approval_status'] ?? '')) !== 'pending') {
            $approvalError = 'This business profile is no longer pending approval.';
        } else {
            $nextStatus = $decision === 'approve' ? 'approved' : 'rejected';
            $isVerified = $decision === 'approve' ? 1 : 0;

            $saved = db_execute(
                'UPDATE business_profiles
                 SET approval_status = :approval_status,
                     is_verified = :is_verified
                 WHERE id = :id
                 LIMIT 1',
                [
                    'approval_status' => $nextStatus,
                    'is_verified' => $isVerified,
                    'id' => $approvalId,
                ]
            );

            if ($saved) {
                $query = http_build_query([
                    'result' => $nextStatus,
                    'business' => (string) ($existingApproval['business_name'] ?? ''),
                ]);

                header('Location: ' . url('pages/admin/approvals.php?' . $query));
                exit;
            }

            $approvalError = 'Unable to update approval status right now. Please try again.';
        }
    }

    $reviewId = $approvalId;
}

if ($reviewId !== null) {
    $reviewTarget = db_one(
        "SELECT
            bp.id,
            bp.business_name,
            COALESCE(c.name, 'Uncategorized') AS category_name,
            bp.city,
            bp.budget_tier,
            bp.contact_email,
            bp.contact_phone,
            bp.description,
            bp.approval_status,
            bp.created_at
         FROM business_profiles bp
         LEFT JOIN categories c ON c.id = bp.category_id
         WHERE bp.id = :id
         LIMIT 1",
        ['id' => $reviewId]
    );

    if (!$reviewTarget && $approvalError === '') {
        $approvalError = 'No matching business profile found for review.';
    }
}

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

            <?php if ($approvalNotice !== ''): ?>
                <div class="notice-item" role="status"><?php echo e($approvalNotice); ?></div>
            <?php endif; ?>
            <?php if ($approvalError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($approvalError); ?></div>
            <?php endif; ?>

            <?php if ($reviewTarget): ?>
                <?php $isPendingReview = strtolower((string) ($reviewTarget['approval_status'] ?? '')) === 'pending'; ?>
                <section class="card section-stack">
                    <h2>Review Business Profile</h2>
                    <div class="chip-row">
                        <span class="chip"><?php echo e((string) ($reviewTarget['category_name'] ?? 'Uncategorized')); ?></span>
                        <span class="chip"><?php echo e(ucfirst((string) ($reviewTarget['approval_status'] ?? 'pending'))); ?></span>
                        <span class="chip"><?php echo e(format_date_label((string) ($reviewTarget['created_at'] ?? ''))); ?></span>
                    </div>
                    <p><strong>Business:</strong> <?php echo e((string) ($reviewTarget['business_name'] ?? 'Business')); ?></p>
                    <p><strong>City:</strong> <?php echo e((string) ($reviewTarget['city'] ?? 'Unspecified')); ?></p>
                    <p><strong>Budget Tier:</strong> <?php echo e(ucfirst((string) ($reviewTarget['budget_tier'] ?? 'mid'))); ?></p>
                    <p><strong>Contact Email:</strong> <?php echo e((string) ($reviewTarget['contact_email'] ?? 'N/A')); ?></p>
                    <p><strong>Contact Phone:</strong> <?php echo e((string) ($reviewTarget['contact_phone'] ?? 'N/A')); ?></p>
                    <p><?php echo e((string) ($reviewTarget['description'] ?? 'No business description provided.')); ?></p>

                    <?php if ($isPendingReview): ?>
                        <form action="<?php echo e(url('pages/admin/approvals.php')); ?>" method="POST" class="hero-actions">
                            <input type="hidden" name="approval_id" value="<?php echo e((string) ((int) ($reviewTarget['id'] ?? 0))); ?>">
                            <button class="btn" type="submit" name="decision" value="approve">Approve Profile</button>
                            <button class="btn-ghost" type="submit" name="decision" value="reject">Reject Profile</button>
                            <a class="btn-secondary" href="<?php echo e(url('pages/admin/approvals.php')); ?>">Cancel</a>
                        </form>
                    <?php else: ?>
                        <div class="notice-item" role="status">This profile has already been processed.</div>
                        <a class="btn-secondary" href="<?php echo e(url('pages/admin/approvals.php')); ?>">Back to Queue</a>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

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
                                <td>
                                    <a class="btn-ghost" href="<?php echo e(url('pages/admin/approvals.php?review_id=' . (string) ((int) ($approval['id'] ?? 0)))); ?>">Review</a>
                                </td>
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
