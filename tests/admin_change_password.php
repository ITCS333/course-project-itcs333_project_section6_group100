<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

require_admin();

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new === '' || $confirm === '' || $current === '') {
        $error = 'Please fill in all fields.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        // Fetch user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current, $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            $new_hash = password_hash($new, PASSWORD_BCRYPT);
            $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update->execute([$new_hash, $_SESSION['user_id']]);
            $message = 'Password updated successfully.';
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<section class="card small-card">
    <h2>Change My Password</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form">
        <label>
            Current Password
            <input type="password" name="current_password" required>
        </label>

        <label>
            New Password
            <input type="password" name="new_password" required>
        </label>

        <label>
            Confirm New Password
            <input type="password" name="confirm_password" required>
        </label>

        <button type="submit" class="btn full-width">Update Password</button>
    </form>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
