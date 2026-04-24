<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$resetError = '';
$resetStatus = (string) ($_GET['sent'] ?? '');
$resetEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resetEmail = strtolower(trim((string) ($_POST['reset_email'] ?? '')));

    if (!filter_var($resetEmail, FILTER_VALIDATE_EMAIL)) {
        $resetError = 'Please provide a valid email address.';
    } elseif (!db_available()) {
        $resetError = 'Database is unavailable right now. Please try again.';
    } else {
        $user = db_one(
            'SELECT id, first_name, last_name FROM users WHERE email = :email LIMIT 1',
            ['email' => $resetEmail]
        );

        $name = $user
            ? trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''))
            : 'Password Reset Request';

        $saved = db_execute(
            'INSERT INTO support_requests (user_id, name, email, topic, message, status)
             VALUES (:user_id, :name, :email, :topic, :message, :status)',
            [
                'user_id' => $user ? (int) ($user['id'] ?? 0) : null,
                'name' => $name !== '' ? $name : 'Password Reset Request',
                'email' => $resetEmail,
                'topic' => 'account-recovery',
                'message' => 'Password reset requested.',
                'status' => 'open',
            ]
        );

        if ($saved) {
            header('Location: ' . url('pages/auth/forgot-password.php?sent=1'));
            exit;
        }

        $resetError = 'Unable to process reset request. Please try again.';
    }
}

$pageTitle = 'Forgot Password';
$activePage = '';

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack" style="max-width:720px;">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <a href="<?php echo e(url('pages/auth/login.php')); ?>">Login</a>
                <span>/</span>
                <span>Forgot Password</span>
            </nav>
            <h1>Reset your password</h1>
            <p>Enter your email address and we will send reset instructions.</p>
        </section>

        <section class="card section-stack">
            <?php if ($resetStatus === '1'): ?>
                <div class="notice-item" role="status">If the email exists, reset instructions were queued.</div>
            <?php endif; ?>
            <?php if ($resetError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($resetError); ?></div>
            <?php endif; ?>
            <form action="<?php echo e(url('pages/auth/forgot-password.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
                <div class="form-grid full">
                    <div class="form-field">
                        <label for="reset-email">Email Address</label>
                        <input id="reset-email" type="email" name="reset_email" value="<?php echo e($resetEmail); ?>" required>
                        <small class="field-error" data-error-for="reset_email"></small>
                    </div>
                </div>
                <button class="btn" type="submit">Send Reset Link</button>
                <p>Remembered your password? <a href="<?php echo e(url('pages/auth/login.php')); ?>">Back to login</a></p>
            </form>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
