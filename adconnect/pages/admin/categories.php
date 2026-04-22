<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

$pageTitle = 'Categories';
$activePage = '';
$sidebarRole = 'admin';
$sidebarPage = 'categories';
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
                <form action="#" method="POST" data-validate class="section-stack">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="category-name">Category Name</label>
                            <input id="category-name" name="category_name" required>
                            <small class="field-error" data-error-for="category_name"></small>
                        </div>
                        <div class="form-field">
                            <label for="category-slug">Slug</label>
                            <input id="category-slug" name="category_slug" required>
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
