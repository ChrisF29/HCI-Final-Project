<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$settingsError = '';
$settingsStatus = (string) ($_GET['saved'] ?? '');

$pageTitle = 'Profile Settings';
$activePage = '';
$sidebarRole = 'user';
$sidebarPage = 'profile-settings';
$clientUserId = active_client_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = current_user_id();
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $notifyEmail = isset($_POST['notify_email']) ? 1 : 0;

    if ($userId === null) {
        $settingsError = 'You must be signed in to update your profile.';
    } elseif ($firstName === '' || $lastName === '' || $phoneNumber === '') {
        $settingsError = 'Please complete all required profile fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $settingsError = 'Please provide a valid email address.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
        $settingsError = 'New password must be at least 8 characters long.';
    } elseif (!db_available()) {
        $settingsError = 'Database is unavailable right now. Please try again.';
    } else {
        $existingUser = db_one(
            'SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1',
            ['email' => $email, 'id' => $userId]
        );

        if ($existingUser) {
            $settingsError = 'That email is already used by another account.';
        } else {
            $params = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone_number' => $phoneNumber,
                'notify_email' => $notifyEmail,
                'id' => $userId,
            ];

            $sql = 'UPDATE users
                    SET first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        phone_number = :phone_number,
                        notify_email = :notify_email';

            if ($newPassword !== '') {
                $sql .= ', password_hash = :password_hash';
                $params['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $sql .= ' WHERE id = :id';

            $saved = db_execute($sql, $params);
            if ($saved) {
                header('Location: ' . url('pages/user/profile-settings.php?saved=1'));
                exit;
            }

            $settingsError = 'Unable to save profile changes right now.';
        }
    }
}

$profile = $clientUserId !== null
    ? db_one(
        'SELECT first_name, last_name, email, phone_number, notify_email FROM users WHERE id = :client_user_id LIMIT 1',
        ['client_user_id' => $clientUserId]
    )
    : null;

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
                <?php if ($settingsStatus === '1'): ?>
                    <div class="notice-item" role="status">Profile settings updated successfully.</div>
                <?php endif; ?>
                <?php if ($settingsError !== ''): ?>
                    <div class="notice-item" role="alert"><?php echo e($settingsError); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(url('pages/user/profile-settings.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="settings-first-name">First Name</label>
                            <input id="settings-first-name" name="first_name" value="<?php echo e((string) ($profile['first_name'] ?? '')); ?>" required>
                            <small class="field-error" data-error-for="first_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="settings-last-name">Last Name</label>
                            <input id="settings-last-name" name="last_name" value="<?php echo e((string) ($profile['last_name'] ?? '')); ?>" required>
                            <small class="field-error" data-error-for="last_name"></small>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="settings-email">Email Address</label>
                            <input id="settings-email" type="email" name="email" value="<?php echo e((string) ($profile['email'] ?? '')); ?>" required>
                            <small class="field-error" data-error-for="email"></small>
                        </div>
                        <div class="form-field">
                            <label for="settings-phone">Phone Number</label>
                            <input id="settings-phone" name="phone_number" value="<?php echo e((string) ($profile['phone_number'] ?? '')); ?>" required>
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

                    <label><input type="checkbox" name="notify_email" <?php echo !empty($profile['notify_email']) ? 'checked' : ''; ?>> Receive campaign updates by email</label>
                    <button class="btn" type="submit">Save Changes</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
