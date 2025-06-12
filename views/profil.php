<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$success = '';
$error = '';

// Ambil data terbaru dari DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

// Update profil jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    if ($stmt->execute([$name, $phone, $address, $user['id']])) {
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['address'] = $address;
        $success = "Profil berhasil diperbarui.";
        $currentUser = array_merge($currentUser, ['name' => $name, 'phone' => $phone, 'address' => $address]);
    } else {
        $error = "Gagal memperbarui profil.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profil Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f6fa;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #fff;
            padding-top: 30px;
        }

        .sidebar a {
            color: #ccc;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }

        .content {
            padding: 0 0 30px 0;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 p-0 sidebar">
                <?php include 'template/sidebar_user.php'; ?>
            </div>

            <!-- Konten -->
            <div class="col-md-9 content">
                <div class="section-card">
                    <h4>Profil Pengguna</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="post" id="profilForm">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($currentUser['name']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (tidak dapat diubah)</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="3" readonly><?= htmlspecialchars($currentUser['address'] ?? '') ?></textarea>
                        </div>

                        <!-- Tombol aksi -->
                        <button type="button" id="editBtn" class="btn btn-warning">Edit Profil</button>
                        <button type="submit" id="saveBtn" class="btn btn-primary d-none">Simpan Perubahan</button>
                        <button type="button" id="cancelBtn" class="btn btn-secondary d-none">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'template/footer.php'; ?>

    <script>
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const inputs = document.querySelectorAll('#profilForm input:not([type="email"]), #profilForm textarea');

        editBtn.addEventListener('click', () => {
            inputs.forEach(input => input.removeAttribute('readonly'));
            editBtn.classList.add('d-none');
            saveBtn.classList.remove('d-none');
            cancelBtn.classList.remove('d-none');
        });

        cancelBtn.addEventListener('click', () => {
            // Reload halaman untuk mengembalikan nilai asli
            window.location.reload();
        });
    </script>
</body>

</html>