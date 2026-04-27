<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$registerError = '';
$form = [
    'account_type' => '',
    'company_name' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone_number' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['account_type'] = strtolower(trim((string) ($_POST['account_type'] ?? '')));
    $form['company_name'] = trim((string) ($_POST['company_name'] ?? ''));
    $form['first_name'] = trim((string) ($_POST['first_name'] ?? ''));
    $form['last_name'] = trim((string) ($_POST['last_name'] ?? ''));
    $form['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $form['phone_number'] = trim((string) ($_POST['phone_number'] ?? ''));

    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $termsAccepted = isset($_POST['terms_accepted']);

    if (!in_array($form['account_type'], ['client', 'business'], true)) {
        $registerError = 'Please choose a valid account type.';
    } elseif ($form['first_name'] === '' || $form['last_name'] === '') {
        $registerError = 'First name and last name are required.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $registerError = 'Please enter a valid email address.';
    } elseif ($form['phone_number'] === '') {
        $registerError = 'Phone number is required.';
    } elseif (strlen($password) < 8) {
        $registerError = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $registerError = 'Password confirmation does not match.';
    } elseif (!$termsAccepted) {
        $registerError = 'You must accept the terms and privacy policy.';
    } elseif ($form['account_type'] === 'business' && $form['company_name'] === '') {
        $registerError = 'Company name is required for business accounts.';
    } elseif (!db_available()) {
        $registerError = 'Unable to connect to the database right now. Please try again.';
    } else {
        $existingUser = db_one(
            'SELECT id FROM users WHERE email = :email LIMIT 1',
            ['email' => $form['email']]
        );

        if ($existingUser) {
            $registerError = 'This email is already registered. Please sign in instead.';
        } else {
            $connection = db();
            if (!$connection) {
                $registerError = 'Unable to complete registration right now.';
            } else {
                $status = 'active';
                $displayName = $form['account_type'] === 'business' && $form['company_name'] !== ''
                    ? $form['company_name']
                    : trim($form['first_name'] . ' ' . $form['last_name']);

                try {
                    $connection->beginTransaction();

                    $userStatement = $connection->prepare(
                        'INSERT INTO users (
                            first_name,
                            last_name,
                            display_name,
                            email,
                            password_hash,
                            role,
                            status,
                            phone_number,
                            notify_email
                        ) VALUES (
                            :first_name,
                            :last_name,
                            :display_name,
                            :email,
                            :password_hash,
                            :role,
                            :status,
                            :phone_number,
                            1
                        )'
                    );

                    $userStatement->execute([
                        'first_name' => $form['first_name'],
                        'last_name' => $form['last_name'],
                        'display_name' => $displayName,
                        'email' => $form['email'],
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => $form['account_type'],
                        'status' => $status,
                        'phone_number' => $form['phone_number'],
                    ]);

                    $userId = (int) $connection->lastInsertId();

                    if ($form['account_type'] === 'business') {
                        $profileStatement = $connection->prepare(
                            'INSERT INTO business_profiles (
                                user_id,
                                business_name,
                                city,
                                budget_tier,
                                description,
                                contact_email,
                                contact_phone,
                                approval_status,
                                is_verified
                            ) VALUES (
                                :user_id,
                                :business_name,
                                :city,
                                :budget_tier,
                                :description,
                                :contact_email,
                                :contact_phone,
                                :approval_status,
                                0
                            )'
                        );

                        $profileStatement->execute([
                            'user_id' => $userId,
                            'business_name' => $form['company_name'],
                            'city' => 'Unspecified',
                            'budget_tier' => 'mid',
                            'description' => 'Newly registered business profile.',
                            'contact_email' => $form['email'],
                            'contact_phone' => $form['phone_number'],
                            'approval_status' => 'pending',
                        ]);
                    }

                    $connection->commit();

                    header('Location: ' . url('pages/auth/login.php?registered=1'));
                    exit;
                } catch (Throwable $exception) {
                    if ($connection->inTransaction()) {
                        $connection->rollBack();
                    }

                    $registerError = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Register';
$activePage = '';

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/navbar.php';
?>
<main class="page-main">
    <div class="container section-stack" style="max-width:860px;">
        <section class="page-hero">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
                <span>/</span>
                <span>Register</span>
            </nav>
            <h1>Create your AdConnect account</h1>
            <p>Select your role and complete your profile to create your account.</p>
        </section>

        <section class="card section-stack">
            <?php if ($registerError !== ''): ?>
                <div class="notice-item" role="alert"><?php echo e($registerError); ?></div>
            <?php endif; ?>

            <form action="<?php echo e(url('pages/auth/register.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="register-role">Account Type</label>
                        <select id="register-role" name="account_type" required>
                            <option value="">Choose role</option>
                            <option value="client" <?php echo $form['account_type'] === 'client' ? 'selected' : ''; ?>>Client</option>
                            <option value="business" <?php echo $form['account_type'] === 'business' ? 'selected' : ''; ?>>Business</option>
                        </select>
                        <small class="field-error" data-error-for="account_type"></small>
                    </div>
                    <div class="form-field">
                        <label for="register-company">Company Name</label>
                        <input id="register-company" name="company_name" value="<?php echo e($form['company_name']); ?>" data-required-when="business" data-required-source="account_type">
                        <small class="field-error" data-error-for="company_name"></small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="register-firstname">First Name</label>
                        <input id="register-firstname" name="first_name" value="<?php echo e($form['first_name']); ?>" required>
                        <small class="field-error" data-error-for="first_name"></small>
                    </div>
                    <div class="form-field">
                        <label for="register-lastname">Last Name</label>
                        <input id="register-lastname" name="last_name" value="<?php echo e($form['last_name']); ?>" required>
                        <small class="field-error" data-error-for="last_name"></small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="register-email">Email Address</label>
                        <input id="register-email" type="email" name="email" value="<?php echo e($form['email']); ?>" required>
                        <small class="field-error" data-error-for="email"></small>
                    </div>
                    <div class="form-field">
                        <label for="register-phone">Phone Number</label>
                        <input id="register-phone" name="phone_number" value="<?php echo e($form['phone_number']); ?>" required>
                        <small class="field-error" data-error-for="phone_number"></small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="register-password">Password</label>
                        <input id="register-password" type="password" name="password" required data-minlength="8">
                        <small class="field-error" data-error-for="password"></small>
                    </div>
                    <div class="form-field">
                        <label for="register-confirm">Confirm Password</label>
                        <input id="register-confirm" type="password" name="confirm_password" required data-match="password">
                        <small class="field-error" data-error-for="confirm_password"></small>
                    </div>
                </div>

                <label><input type="checkbox" name="terms_accepted" required> I agree to the platform terms and privacy policy.</label>
                <small class="field-error" data-error-for="terms_accepted"></small>

                <button class="btn" type="submit">Create Account</button>
            </form>
        </section>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
