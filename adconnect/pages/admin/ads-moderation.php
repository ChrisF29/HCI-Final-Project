<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';

header('Location: ' . url('pages/admin/dashboard.php?role=admin'));
exit;
