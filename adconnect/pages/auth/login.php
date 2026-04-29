<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$loginError = '';
$loginEmail = '';
$registeredNotice = isset($_GET['registered']) && $_GET['registered'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginEmail = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if ($loginEmail === '' || $password === '') {
        $loginError = 'Email and password are required.';
    } elseif (!filter_var($loginEmail, FILTER_VALIDATE_EMAIL)) {
        $loginError = 'Please enter a valid email address.';
    } elseif (!db_available()) {
        $loginError = 'Unable to connect to the database right now. Please try again.';
    } else {
        $user = db_one(
            'SELECT id, role, status, password_hash FROM users WHERE email = :email LIMIT 1',
            ['email' => $loginEmail]
        );

        $passwordHash = (string) ($user['password_hash'] ?? '');
        $status = strtolower((string) ($user['status'] ?? 'pending'));
        $role = strtolower((string) ($user['role'] ?? 'guest'));

        if (!$user || $passwordHash === '' || !password_verify($password, $passwordHash)) {
            $loginError = 'Invalid email or password.';
        } elseif (!in_array($status, ['active', 'verified'], true)) {
            $loginError = 'Your account is not active yet. Please contact support.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) ($user['id'] ?? 0);
            $_SESSION['role'] = $role;

            $redirectMap = [
                'client' => 'pages/user/dashboard.php',
                'business' => 'pages/business/dashboard.php',
                'admin' => 'pages/admin/dashboard.php',
            ];

            $redirectTo = $redirectMap[$role] ?? 'pages/home.php';
            header('Location: ' . url($redirectTo));
            exit;
        }
    }
}

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

            <?php if ($registeredNotice): ?>
                <div class="notice-item" role="status">Registration successful. You can now log in.</div>
            <?php endif; ?>

            <?php if ($loginError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($loginError); ?></div>
            <?php endif; ?>

            <form action="<?php echo e(url('pages/auth/login.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
                <div class="form-grid full">
                    <div class="form-field">
                        <label for="login-email">Email Address</label>
                        <input id="login-email" type="email" name="email" value="<?php echo e($loginEmail); ?>" required>
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
