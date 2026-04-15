<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

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
            <form action="#" method="POST" data-validate class="section-stack">
                <div class="form-grid full">
                    <div class="form-field">
                        <label for="reset-email">Email Address</label>
                        <input id="reset-email" type="email" name="reset_email" required>
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
