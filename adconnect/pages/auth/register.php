<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

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
            <p>Select your role and complete your profile. This structure is ready for PHP registration handling.</p>
        </section>

        <section class="card section-stack">
            <form action="#" method="POST" data-validate class="section-stack">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="register-role">Account Type</label>
                        <select id="register-role" name="account_type" required>
                            <option value="">Choose role</option>
                            <option value="client">Client</option>
                            <option value="business">Business</option>
                        </select>
                        <small class="field-error" data-error-for="account_type"></small>
                    </div>
                    <div class="form-field">
                        <label for="register-company">Company Name</label>
                        <input id="register-company" name="company_name" required>
                        <small class="field-error" data-error-for="company_name"></small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="register-firstname">First Name</label>
                        <input id="register-firstname" name="first_name" required>
                        <small class="field-error" data-error-for="first_name"></small>
                    </div>
                    <div class="form-field">
                        <label for="register-lastname">Last Name</label>
                        <input id="register-lastname" name="last_name" required>
                        <small class="field-error" data-error-for="last_name"></small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="register-email">Email Address</label>
                        <input id="register-email" type="email" name="email" required>
                        <small class="field-error" data-error-for="email"></small>
                    </div>
                    <div class="form-field">
                        <label for="register-phone">Phone Number</label>
                        <input id="register-phone" name="phone_number" required>
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
