<?php
$pageTitle = $pageTitle ?? 'AdConnect';
$pageClass = $pageClass ?? '';

global $appName, $appDescription;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo e($appDescription); ?>">
    <title><?php echo e($pageTitle . ' | ' . $appName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(url('assets/css/main.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(url('assets/css/components.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(url('assets/css/responsive.css')); ?>">
</head>
<body class="<?php echo e($pageClass); ?>" data-role="<?php echo e((string) ($_SESSION['role'] ?? 'guest')); ?>">
<div class="bg-orb orb-a" aria-hidden="true"></div>
<div class="bg-orb orb-b" aria-hidden="true"></div>
<div id="notification-root" class="toast-stack" aria-live="polite" aria-atomic="true"></div>
