<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$profileError = '';
$profileStatus = (string) ($_GET['saved'] ?? '');

$pageTitle = 'Manage Profile';
$activePage = '';
$sidebarRole = 'business';
$sidebarPage = 'manage-profile';
$businessId = active_business_profile_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $businessName = trim((string) ($_POST['business_name'] ?? ''));
    $businessCategorySlug = strtolower(trim((string) ($_POST['business_category'] ?? '')));
    $businessLocation = trim((string) ($_POST['business_location'] ?? ''));
    $contactEmail = strtolower(trim((string) ($_POST['contact_email'] ?? '')));
    $contactPhone = trim((string) ($_POST['contact_phone'] ?? ''));
    $businessDescription = trim((string) ($_POST['business_description'] ?? ''));

    if ($businessId === null) {
        $profileError = 'Business profile was not found.';
    } elseif ($businessName === '' || $businessCategorySlug === '' || $businessLocation === '' || $contactPhone === '' || $businessDescription === '') {
        $profileError = 'Please complete all required profile fields.';
    } elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $profileError = 'Please provide a valid contact email.';
    } elseif (!db_available()) {
        $profileError = 'Database is unavailable right now. Please try again.';
    } else {
        $categoryId = db_value('SELECT id FROM categories WHERE slug = :slug AND is_active = 1 LIMIT 1', ['slug' => $businessCategorySlug]);
        if ($categoryId === null) {
            $profileError = 'Selected category does not exist.';
        } else {
            $saved = db_execute(
                'UPDATE business_profiles
                 SET business_name = :business_name,
                     category_id = :category_id,
                     city = :city,
                     contact_email = :contact_email,
                     contact_phone = :contact_phone,
                     description = :description
                 WHERE id = :id',
                [
                    'business_name' => $businessName,
                    'category_id' => (int) $categoryId,
                    'city' => $businessLocation,
                    'contact_email' => $contactEmail,
                    'contact_phone' => $contactPhone,
                    'description' => $businessDescription,
                    'id' => $businessId,
                ]
            );

            if ($saved) {
                header('Location: ' . url('pages/business/manage-profile.php?saved=1'));
                exit;
            }

            $profileError = 'Unable to save profile changes right now.';
        }
    }
}

$businessProfile = fetch_business_profile($businessId);
$categories = fetch_categories_with_counts();
$selectedCategorySlug = strtolower((string) ($businessProfile['category_slug'] ?? ''));

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
                <?php if ($profileStatus === '1'): ?>
                    <div class="notice-item" role="status">Business profile updated successfully.</div>
                <?php endif; ?>
                <?php if ($profileError !== ''): ?>
                    <div class="notice-item" role="alert"><?php echo e($profileError); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(url('pages/business/manage-profile.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="business-name">Business Name</label>
                            <input id="business-name" name="business_name" value="<?php echo e((string) ($businessProfile['business_name'] ?? '')); ?>" required>
                            <small class="field-error" data-error-for="business_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="business-category">Category</label>
                            <select id="business-category" name="business_category" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <?php $slug = strtolower((string) ($category['slug'] ?? '')); ?>
                                    <option value="<?php echo e($slug); ?>" <?php echo $selectedCategorySlug === $slug ? 'selected' : ''; ?>><?php echo e((string) ($category['name'] ?? 'Category')); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="field-error" data-error-for="business_category"></small>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="business-email">Contact Email</label>
                            <input id="business-email" type="email" name="contact_email" value="<?php echo e((string) ($businessProfile['contact_email'] ?? '')); ?>" required>
                            <small class="field-error" data-error-for="contact_email"></small>
                        </div>
                        <div class="form-field">
                            <label for="business-phone">Phone</label>
                            <input id="business-phone" name="contact_phone" value="<?php echo e((string) ($businessProfile['contact_phone'] ?? '')); ?>" required>
                            <small class="field-error" data-error-for="contact_phone"></small>
                        </div>
                        <div class="form-field">
                            <label for="business-location">Location</label>
                            <input id="business-location" name="business_location" value="<?php echo e((string) ($businessProfile['city'] ?? '')); ?>" required>
                            <small class="field-error" data-error-for="business_location"></small>
                        </div>
                    </div>

                    <div class="form-grid full">
                        <div class="form-field">
                            <label for="business-description">Description</label>
                            <textarea id="business-description" name="business_description" required data-minlength="20"><?php echo e((string) ($businessProfile['description'] ?? '')); ?></textarea>
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
