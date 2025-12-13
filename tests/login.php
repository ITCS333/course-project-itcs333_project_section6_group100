<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

$error = '';
$email_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $email_value = $email;

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Store basic info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: index.php'); // later you can add student dashboard
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<section class="card small-card">
    <h2>Login</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php" class="form">
        <label>
            Email
            <input type="email" name="email" value="<?= htmlspecialchars($email_value) ?>" required>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <button type="submit" class="btn full-width">Login</button>
    </form>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
