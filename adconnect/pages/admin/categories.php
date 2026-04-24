<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$categoryError = '';
$categoryStatus = (string) ($_GET['saved'] ?? '');
$categoryForm = [
    'category_name' => '',
    'category_slug' => '',
];

$pageTitle = 'Categories';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryForm['category_name'] = trim((string) ($_POST['category_name'] ?? ''));
    $categoryForm['category_slug'] = strtolower(trim((string) ($_POST['category_slug'] ?? '')));

    if ($categoryForm['category_name'] === '' || $categoryForm['category_slug'] === '') {
        $categoryError = 'Category name and slug are required.';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $categoryForm['category_slug'])) {
        $categoryError = 'Slug may only contain lowercase letters, numbers, and hyphens.';
    } elseif (!db_available()) {
        $categoryError = 'Database is unavailable right now. Please try again.';
    } else {
        $saved = db_execute(
            'INSERT INTO categories (name, slug, is_active) VALUES (:name, :slug, 1)',
            [
                'name' => $categoryForm['category_name'],
                'slug' => $categoryForm['category_slug'],
            ]
        );

        if ($saved) {
            header('Location: ' . url('pages/admin/categories.php?saved=1'));
            exit;
        }

        $categoryError = 'Unable to save category. It may already exist.';
    }
}

$categories = fetch_categories_with_counts();

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
                    <a href="<?php echo e(url('pages/admin/dashboard.php?role=admin')); ?>">Admin Dashboard</a>
                    <span>/</span>
                    <span>Categories</span>
                </nav>
                <h1>Manage category taxonomy</h1>
                <p>Control available listing and ad categories for consistent indexing.</p>
            </section>

            <section class="card section-stack">
                <h3>Add category</h3>
                <?php if ($categoryStatus === '1'): ?>
                    <div class="notice-item" role="status">Category added successfully.</div>
                <?php endif; ?>
                <?php if ($categoryError !== ''): ?>
                    <div class="notice-item" role="alert"><?php echo e($categoryError); ?></div>
                <?php endif; ?>
                <form action="<?php echo e(url('pages/admin/categories.php')); ?>" method="POST" data-validate data-allow-submit class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="category-name">Category Name</label>
                            <input id="category-name" name="category_name" value="<?php echo e($categoryForm['category_name']); ?>" required>
                            <small class="field-error" data-error-for="category_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="category-slug">Slug</label>
                            <input id="category-slug" name="category_slug" value="<?php echo e($categoryForm['category_slug']); ?>" required>
                            <small class="field-error" data-error-for="category_slug"></small>
                        </div>
                    </div>
                    <button class="btn" type="submit">Save Category</button>
                </form>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Active Listings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo e((string) ($category['name'] ?? 'Category')); ?></td>
                                <td><?php echo e((string) ($category['slug'] ?? '')); ?></td>
                                <td><?php echo e((string) ($category['active_listings'] ?? '0')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="3">No categories configured yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</main>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
