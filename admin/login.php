<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Email atau password salah, atau bukan admin.';
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
    <div class="card p-4 shadow w-100" style="max-width: 400px;">
        <h4 class="text-center mb-3">Login Admin</h4>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email Admin" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Masuk</button>
        </form>
    </div>
</div>