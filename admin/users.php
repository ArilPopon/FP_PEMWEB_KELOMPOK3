<?php
require_once 'middleware/admin_auth.php';
include 'includes/header.php';
include 'includes/sidebar.php';
require_once './../config/database.php';

class Users
{
    public function tampilkan_tabel($pdo, $role = null)
    {
        $query = "SELECT * FROM users";
        $params = [];

        if ($role) {
            $query .= " WHERE role LIKE :role";
            $params[':role'] = $role;
        }
        $query .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pencarian($pdo, $keyword)
    {
        $query = "SELECT * FROM users";
        $params = [];

        if (!empty($keyword)) {
            $query .= " WHERE name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword";
            $params[':keyword'] = '%' . $keyword . '%';
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$usersObj = new Users();

// Determine which data to display based on search keyword
if (!empty($keyword)) {
    // If a search keyword is present, display filtered results for both roles
    $allUsers = $usersObj->pencarian($pdo, $keyword);
    $adminData = array_filter($allUsers, function ($user) {
        return $user['role'] === 'admin';
    });
    $customerData = array_filter($allUsers, function ($user) {
        return $user['role'] === 'customer';
    });
} else {
    // If no search keyword, display all admin and customer data separately
    $adminData = $usersObj->tampilkan_tabel($pdo, 'admin');
    $customerData = $usersObj->tampilkan_tabel($pdo, 'customer');
}

?>

<div class="content">
    <h2 style="text-align: center;">MANAJEMEN PENGGUNA</h2>
    <hr />
    <div>
        <form action="users.php" method="get" class="input-group mb-4">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau telepon" value="<?= htmlspecialchars($keyword); ?>">
            <button class="btn btn-outline-secondary" type="submit">Cari</button>
        </form>
    </div>
    <!-- <h3>Data Admin</h3>
    <?php if (count($adminData) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>id</th>
                    <th>Name</th>
                    <th>E-Mail</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adminData as $admin): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['id']); ?></td>
                        <td><?= htmlspecialchars($admin['name']); ?></td>
                        <td><?= htmlspecialchars($admin['email']); ?></td>
                        <td><?= htmlspecialchars($admin['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-muted">Data kosong / Data tidak ditemukan!</p>
    <?php endif; ?> -->
    <h3>Data Pengguna</h3>
    <?php if (count($customerData) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped talign-middle">
                <thead class="table-dark">
                    <tr>
                        <th>id</th>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Phone</th>
                        <th>Gabung</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customerData as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['id']); ?></td>
                            <td><?= htmlspecialchars($customer['name']); ?></td>
                            <td><?= htmlspecialchars($customer['email']); ?></td>
                            <td><?= htmlspecialchars($customer['phone']); ?></td>
                            <td><?= htmlspecialchars($customer['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Data kosong / Data tidak ditemukan!</p>
    <?php endif; ?>
</div>