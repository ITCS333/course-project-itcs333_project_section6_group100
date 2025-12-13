<?php
require_once __DIR__ . '/config.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        // Not an admin → back to homepage
        header('Location: index.php');
        exit;
    }
}
