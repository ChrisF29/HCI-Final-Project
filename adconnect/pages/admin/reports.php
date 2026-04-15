<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Reports';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'reports';

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
                    <span>Reports</span>
                </nav>
                <h1>Incident reports</h1>
                <p>Track policy violations and resolve user-submitted concerns.</p>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Reported By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>REP-2026-0411</td><td>Misleading ad copy</td><td>Client user</td><td><span class="badge badge-warning">Open</span></td></tr>
                        <tr><td>REP-2026-0408</td><td>Spam inquiry</td><td>Business user</td><td><span class="badge badge-success">Resolved</span></td></tr>
                        <tr><td>REP-2026-0404</td><td>Profile impersonation</td><td>Admin</td><td><span class="badge badge-neutral">Investigating</span></td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card section-stack">
                <h3>Submit internal report</h3>
                <form action="#" method="POST" data-validate>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="report-type">Issue Type</label>
                            <select id="report-type" name="issue_type" required>
                                <option value="">Select type</option>
                                <option value="ad-content">Ad Content</option>
                                <option value="user-behavior">User Behavior</option>
                                <option value="security">Security</option>
                            </select>
                            <small class="field-error" data-error-for="issue_type"></small>
                        </div>
                        <div class="form-field">
                            <label for="report-reference">Reference</label>
                            <input id="report-reference" name="reference_code" required>
                            <small class="field-error" data-error-for="reference_code"></small>
                        </div>
                    </div>
                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="report-notes">Notes</label>
                            <textarea id="report-notes" name="report_notes" required data-minlength="20"></textarea>
                            <small class="field-error" data-error-for="report_notes"></small>
                        </div>
                    </div>
                    <button class="btn" type="submit">File Report</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
