<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

require_admin();

include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h2>Admin Portal</h2>
    <p>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>!</p>

    <div class="btn-group">
        <a class="btn primary" href="admin_students.php">Manage Students</a>
        <a class="btn secondary" href="admin_change_password.php">Change My Password</a>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
