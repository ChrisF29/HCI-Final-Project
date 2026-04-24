<?php
$activePage = $activePage ?? '';
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
                    <a href="<?php echo e(url('pages/auth/login.php')); ?>">Login</a>
                    <a href="<?php echo e(url('pages/auth/register.php')); ?>">Register</a>
                </div>
            </div>
        </nav>

        <div class="topbar-actions">
            <form class="nav-search" action="<?php echo e(url('pages/directory.php')); ?>" method="GET">
                <label class="sr-only" for="global-search">Search</label>
                <input id="global-search" name="q" type="search" placeholder="Search businesses or ads">
            </form>
            <button class="icon-button" type="button" data-notify="You have 3 new alerts waiting.">
                Alerts
            </button>
        </div>
    </div>
</header>
