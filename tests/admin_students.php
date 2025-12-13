<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

require_admin();

$DEFAULT_STUDENT_PASSWORD = 'student123';

$error = '';
$message = '';

// Handle create / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $name       = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $id         = $_POST['id'] ?? null;

    if ($name === '' || $student_id === '' || $email === '') {
        $error = 'Please fill in all fields.';
    } else {
        if ($action === 'create') {
            $password_hash = password_hash($DEFAULT_STUDENT_PASSWORD, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, student_id, email, password_hash, role)
                 VALUES (?, ?, ?, ?, 'student')"
            );
            $stmt->execute([$name, $student_id, $email, $password_hash]);
            $message = "Student created with default password: {$DEFAULT_STUDENT_PASSWORD}";
        } elseif ($action === 'update' && $id) {
            $stmt = $pdo->prepare(
                "UPDATE users SET name = ?, student_id = ?, email = ? WHERE id = ? AND role = 'student'"
            );
            $stmt->execute([$name, $student_id, $email, $id]);
            $message = 'Student updated successfully.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $del = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $del->execute([$delete_id]);
    $message = 'Student deleted.';
}

// Fetch all students
$students_stmt = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
$students = $students_stmt->fetchAll();

// If editing
$edit_student = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    foreach ($students as $s) {
        if ((int)$s['id'] === $edit_id) {
            $edit_student = $s;
            break;
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h2>Manage Students</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="grid two-columns">
        <div>
            <h3><?= $edit_student ? 'Edit Student' : 'Add New Student' ?></h3>
            <form method="post" class="form">
                <input type="hidden" name="action" value="<?= $edit_student ? 'update' : 'create' ?>">
                <?php if ($edit_student): ?>
                    <input type="hidden" name="id" value="<?= (int)$edit_student['id'] ?>">
                <?php endif; ?>

                <label>
                    Name
                    <input type="text" name="name" required
                           value="<?= htmlspecialchars($edit_student['name'] ?? '') ?>">
                </label>

                <label>
                    Student ID
                    <input type="text" name="student_id" required
                           value="<?= htmlspecialchars($edit_student['student_id'] ?? '') ?>">
                </label>

                <label>
                    Email
                    <input type="email" name="email" required
                           value="<?= htmlspecialchars($edit_student['email'] ?? '') ?>">
                </label>

                <button type="submit" class="btn full-width">
                    <?= $edit_student ? 'Update Student' : 'Create Student' ?>
                </button>

                <?php if (!$edit_student): ?>
                    <p class="hint">
                        Default password for new students: <strong><?= htmlspecialchars($DEFAULT_STUDENT_PASSWORD) ?></strong>
                    </p>
                <?php endif; ?>
            </form>
        </div>

        <div>
            <h3>All Students</h3>
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" class="empty">No students yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= (int)$s['id'] ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['student_id']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td>
                                <a class="link" href="admin_students.php?edit=<?= (int)$s['id'] ?>">Edit</a>
                                |
                                <a class="link danger"
                                   href="admin_students.php?delete=<?= (int)$s['id'] ?>"
                                   onclick="return confirm('Delete this student?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
