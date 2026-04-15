<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Profile Settings';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'profile-settings';

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
                    <a href="<?php echo e(url('pages/user/dashboard.php?role=client')); ?>">Client Dashboard</a>
                    <span>/</span>
                    <span>Profile Settings</span>
                </nav>
                <h1>Update profile details</h1>
                <p>Manage contact info and notification preferences.</p>
            </section>

            <section class="card section-stack">
                <form action="#" method="POST" data-validate class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="settings-first-name">First Name</label>
                            <input id="settings-first-name" name="first_name" value="Alex" required>
                            <small class="field-error" data-error-for="first_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="settings-last-name">Last Name</label>
                            <input id="settings-last-name" name="last_name" value="Santos" required>
                            <small class="field-error" data-error-for="last_name"></small>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="settings-email">Email Address</label>
                            <input id="settings-email" type="email" name="email" value="alex@example.com" required>
                            <small class="field-error" data-error-for="email"></small>
                        </div>
                        <div class="form-field">
                            <label for="settings-phone">Phone Number</label>
                            <input id="settings-phone" name="phone_number" value="09171234567" required>
                            <small class="field-error" data-error-for="phone_number"></small>
                        </div>
                    </div>

                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="settings-password">New Password</label>
                            <input id="settings-password" type="password" name="new_password" data-minlength="8">
                            <small class="field-error" data-error-for="new_password"></small>
                        </div>
                    </div>

                    <label><input type="checkbox" name="notify_email" checked> Receive campaign updates by email</label>
                    <button class="btn" type="submit">Save Changes</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
