<?php
session_start();
require_once 'config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'address' => $user['address'],
            'role' => $user['role']
        ];
        header("Location: index.php");
        exit;
    } else {
        $error = 'Email atau password salah.';
    }
}
?>

<?php include 'views/template/header.php'; ?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
    <div class="card p-4 shadow w-100" style="max-width: 500px;">
        <h4 class="text-center mb-3">Masuk ke Akun Anda</h4>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <button class="btn btn-primary w-100" type="submit">Masuk</button>
        </form>
        <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</div>
<?php include 'views/template/footer.php'; ?>