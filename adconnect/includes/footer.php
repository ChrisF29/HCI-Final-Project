<footer class="site-footer">
    <div class="container footer-grid">
        <section>
            <h3>AdConnect</h3>
            <p>Frontend-ready architecture for future PHP and MySQL integration.</p>
        </section>

        <section>
            <h4>Explore</h4>
            <a href="<?php echo e(url('pages/home.php')); ?>">Home</a>
            <a href="<?php echo e(url('pages/directory.php')); ?>">Directory</a>
            <a href="<?php echo e(url('pages/ads.php')); ?>">Ads</a>
            <a href="<?php echo e(url('pages/about.php')); ?>">About</a>
        </section>

        <section>
            <h4>Account</h4>
            <a href="<?php echo e(url('pages/auth/login.php')); ?>">Login</a>
            <a href="<?php echo e(url('pages/auth/register.php')); ?>">Register</a>
            <a href="<?php echo e(url('pages/help.php')); ?>">Help Center</a>
        </section>

        <section>
            <h4>Dashboards</h4>
            <a href="<?php echo e(url('pages/user/dashboard.php?role=client')); ?>">Client</a>
            <a href="<?php echo e(url('pages/business/dashboard.php?role=business')); ?>">Business</a>
            <a href="<?php echo e(url('pages/admin/dashboard.php?role=admin')); ?>">Admin</a>
        </section>
    </div>
    <div class="container footer-base">
        <small>&copy; <?php echo date('Y'); ?> AdConnect. Built with scalable frontend architecture.</small>
    </div>
</footer>

<script src="<?php echo e(url('assets/js/main.js')); ?>" defer></script>
<script src="<?php echo e(url('assets/js/search.js')); ?>" defer></script>
<script src="<?php echo e(url('assets/js/filter.js')); ?>" defer></script>
<script src="<?php echo e(url('assets/js/dashboard.js')); ?>" defer></script>
</body>
</html>
