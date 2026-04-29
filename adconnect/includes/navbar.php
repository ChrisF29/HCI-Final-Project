<?php
$activePage = $activePage ?? '';
$isAuthenticated = is_authenticated();
$activeRole = strtolower((string) ($_SESSION['role'] ?? 'guest'));
$roleLabelMap = [
    'client' => 'Client',
    'business' => 'Business',
    'admin' => 'Admin',
];
$activeRoleLabel = $roleLabelMap[$activeRole] ?? 'Guest';
$notificationRole = $activeRole !== '' ? $activeRole : 'guest';
$notificationUserId = $isAuthenticated ? current_user_id() : null;
$navNotifications = fetch_alerts_for_role($notificationRole, $notificationUserId, 8);
?>
<header class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="<?php echo e(url('pages/home.php')); ?>">
            <span class="brand-mark">AC</span>
            <span class="brand-copy">
                <strong>AdConnect</strong>
                <small>Match. Advertise. Grow.</small>
            </span>
        </a>

        <button class="mobile-nav-toggle" type="button" aria-expanded="false" data-mobile-nav-toggle>
            Menu
        </button>

        <nav class="main-nav" data-mobile-nav>
            <a class="nav-link <?php echo $activePage === 'home' ? 'is-active' : ''; ?>" href="<?php echo e(url('pages/home.php')); ?>">Home</a>
            <a class="nav-link <?php echo $activePage === 'directory' ? 'is-active' : ''; ?>" href="<?php echo e(url('pages/directory.php')); ?>">Directory</a>
            <a class="nav-link <?php echo $activePage === 'ads' ? 'is-active' : ''; ?>" href="<?php echo e(url('pages/ads.php')); ?>">Ads</a>
            <a class="nav-link <?php echo $activePage === 'about' ? 'is-active' : ''; ?>" href="<?php echo e(url('pages/about.php')); ?>">About</a>
            <a class="nav-link <?php echo $activePage === 'help' ? 'is-active' : ''; ?>" href="<?php echo e(url('pages/help.php')); ?>">Help</a>

            <div class="nav-dropdown" data-dropdown>
                <button class="nav-link dropdown-toggle" type="button" data-dropdown-toggle>
                    Account
                </button>
                <div class="dropdown-menu" data-dropdown-menu>
                    <?php if ($isAuthenticated): ?>
                        <p class="dropdown-label">Signed in as <?php echo e($activeRoleLabel); ?></p>
                        <a href="<?php echo e(url(dashboard_path_for_role($activeRole))); ?>">Go to Dashboard</a>
                        <hr>
                        <a href="<?php echo e(url('pages/auth/logout.php')); ?>">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo e(url('pages/auth/login.php')); ?>">Login</a>
                        <a href="<?php echo e(url('pages/auth/register.php')); ?>">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <div class="topbar-actions">
            <form class="nav-search" action="<?php echo e(url('pages/directory.php')); ?>" method="GET">
                <label class="sr-only" for="global-search">Search</label>
                <input id="global-search" name="q" type="search" placeholder="Search businesses or ads">
            </form>
            <button class="icon-button" type="button" data-modal-target="alerts-modal" aria-haspopup="dialog" aria-controls="alerts-modal">
                Alerts
            </button>
            <button class="icon-button" type="button" data-theme-toggle aria-pressed="false" aria-label="Enable night mode" title="Enable night mode">
                &#9790;
            </button>
        </div>
    </div>
</header>

<div class="modal" data-modal="alerts-modal" data-alerts-endpoint="<?php echo e(url('pages/api/alerts.php')); ?>" aria-hidden="true" id="alerts-modal">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Alerts</h3>
            <button class="btn-ghost" type="button" data-modal-close>Close</button>
        </div>
        <div class="notice-list" data-alerts-list>
            <?php if (!$isAuthenticated): ?>
                <article class="notice-item">Sign in to see personalized alerts.</article>
            <?php else: ?>
                <?php foreach ($navNotifications as $notification): ?>
                    <?php echo render_notification_item($notification); ?>
                <?php endforeach; ?>
                <?php if (empty($navNotifications)): ?>
                    <article class="notice-item">No alerts available right now.</article>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
