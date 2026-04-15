<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Manage Profile';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'manage-profile';

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
                    <a href="<?php echo e(url('pages/business/dashboard.php?role=business')); ?>">Business Dashboard</a>
                    <span>/</span>
                    <span>Manage Profile</span>
                </nav>
                <h1>Update business profile</h1>
                <p>Keep your description, specialization, and contact points up to date for better matching.</p>
            </section>

            <section class="card section-stack">
                <form action="#" method="POST" data-validate class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="business-name">Business Name</label>
                            <input id="business-name" name="business_name" value="BrightPixel Studio" required>
                            <small class="field-error" data-error-for="business_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="business-category">Category</label>
                            <select id="business-category" name="business_category" required>
                                <option value="creative">Creative</option>
                                <option value="digital">Digital</option>
                                <option value="video">Video</option>
                                <option value="events">Events</option>
                            </select>
                            <small class="field-error" data-error-for="business_category"></small>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="business-email">Contact Email</label>
                            <input id="business-email" type="email" name="contact_email" value="hello@brightpixel.example" required>
                            <small class="field-error" data-error-for="contact_email"></small>
                        </div>
                        <div class="form-field">
                            <label for="business-phone">Phone</label>
                            <input id="business-phone" name="contact_phone" value="09179876543" required>
                            <small class="field-error" data-error-for="contact_phone"></small>
                        </div>
                    </div>

                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="business-description">Description</label>
                            <textarea id="business-description" name="business_description" required data-minlength="20">Creative campaigns for startups and enterprise brands.</textarea>
                            <small class="field-error" data-error-for="business_description"></small>
                        </div>
                    </div>

                    <button class="btn" type="submit">Save Profile</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
