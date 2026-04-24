<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Login';
$activePage = '';

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack" style="max-width:760px;">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <span>Login</span>
            </nav>
            <h1>Welcome back</h1>
            <p>Sign in to access your dashboard and campaign tools.</p>
        </section>

        <section class="card section-stack">
            <div class="notice-item">
                <strong>Default Admin Account</strong><br>
                Email: admin@adconnect.local<br>
                Password: Admin123!
            </div>

            <form action="#" method="POST" data-validate class="section-stack">
                <div class="form-grid full">
                    <div class="form-field">
                        <label for="login-email">Email Address</label>
                        <input id="login-email" type="email" name="email" required>
                        <small class="field-error" data-error-for="email"></small>
                    </div>
                    <div class="form-field">
                        <label for="login-password">Password</label>
                        <input id="login-password" type="password" name="password" required data-minlength="8">
                        <small class="field-error" data-error-for="password"></small>
                    </div>
                </div>

                <div class="inline-split">
                    <label><input type="checkbox" name="remember_me"> Remember me</label>
                    <a href="<?php echo e(url('pages/auth/forgot-password.php')); ?>">Forgot password?</a>
                </div>

                <button class="btn" type="submit">Login</button>
                <p>New user? <a href="<?php echo e(url('pages/auth/register.php')); ?>">Create an account</a></p>
            </form>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
