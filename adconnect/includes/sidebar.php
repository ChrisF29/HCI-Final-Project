<?php
$sidebarRole = $sidebarRole ?? 'user';
$sidebarPage = $sidebarPage ?? '';

$menus = [
    'user' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'pages/user/dashboard.php?role=client'],
        ['key' => 'favorites', 'label' => 'Favorites', 'href' => 'pages/user/favorites.php?role=client'],
        ['key' => 'messages', 'label' => 'Messages', 'href' => 'pages/user/messages.php?role=client'],
        ['key' => 'reviews', 'label' => 'Reviews', 'href' => 'pages/user/reviews.php?role=client'],
        ['key' => 'profile-settings', 'label' => 'Profile Settings', 'href' => 'pages/user/profile-settings.php?role=client'],
    ],
    'business' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'pages/business/dashboard.php?role=business'],
        ['key' => 'manage-profile', 'label' => 'Manage Profile', 'href' => 'pages/business/manage-profile.php?role=business'],
        ['key' => 'manage-ads', 'label' => 'Manage Ads', 'href' => 'pages/business/manage-ads.php?role=business'],
        ['key' => 'campaigns', 'label' => 'Campaigns', 'href' => 'pages/business/campaigns.php?role=business'],
        ['key' => 'analytics', 'label' => 'Analytics', 'href' => 'pages/business/analytics.php?role=business'],
        ['key' => 'inquiries', 'label' => 'Inquiries', 'href' => 'pages/business/inquiries.php?role=business'],
    ],
    'admin' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'pages/admin/dashboard.php?role=admin'],
        ['key' => 'users', 'label' => 'Users', 'href' => 'pages/admin/users.php?role=admin'],
        ['key' => 'approvals', 'label' => 'Approvals', 'href' => 'pages/admin/approvals.php?role=admin'],
        ['key' => 'ads-moderation', 'label' => 'Ads Moderation', 'href' => 'pages/admin/ads-moderation.php?role=admin'],
        ['key' => 'categories', 'label' => 'Categories', 'href' => 'pages/admin/categories.php?role=admin'],
        ['key' => 'reports', 'label' => 'Reports', 'href' => 'pages/admin/reports.php?role=admin'],
    ],
];

$roleLabels = [
    'user' => 'Client Workspace',
    'business' => 'Business Workspace',
    'admin' => 'Admin Workspace',
];

if (!array_key_exists($sidebarRole, $menus)) {
    $sidebarRole = 'user';
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2><?php echo e($roleLabels[$sidebarRole]); ?></h2>
        <p>Role-aware navigation</p>
    </div>
    <nav class="sidebar-nav" aria-label="Sidebar Navigation">
        <?php foreach ($menus[$sidebarRole] as $item): ?>
            <a class="sidebar-link <?php echo $sidebarPage === $item['key'] ? 'is-active' : ''; ?>" href="<?php echo e(url($item['href'])); ?>">
                <?php echo e($item['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
