<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= $BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <?= htmlspecialchars($SITE_NAME) ?>
    </div>
    <nav class="navbar-right">
        <a href="<?= $BASE_URL ?>/index.php">Home</a>

        <?php if (!empty($_SESSION['user_id'])): ?>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?= $BASE_URL ?>/admin_dashboard.php">Admin</a>
            <?php endif; ?>
            <a href="<?= $BASE_URL ?>/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= $BASE_URL ?>/login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<main class="page-main">
