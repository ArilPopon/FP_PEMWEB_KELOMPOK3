<?php
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/CustomerOrder.php';

$customOrder = new CustomerOrder($pdo);

// Tangani penghapusan
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $customOrder->delete($_GET['delete']);
    header("Location: custom.php");
    exit;
}

// Tangani pembaruan status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $customOrder->updateStatus($_POST['id'], $_POST['status']);
}

// tangani pembaruan harga
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update harga
    if (isset($_POST['id'], $_POST['harga']) && isset($_POST['update_harga'])) {
        $customOrder->updatePrice($_POST['id'], $_POST['harga']);
        header("Location: custom.php");
        exit;
    }
}



// Tangani pencarian
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$orders = $customOrder->getAll($keyword);
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Customisasi Perhiasan</h2>
        <div>
            <!-- Tambahkan tombol jika butuh, misalnya tambah pesanan custom -->
        </div>
    </div>

    <form class="input-group mb-4" method="get" action="custom.php">
        <input type="text" name="search" class="form-control" placeholder="Cari deskripsi atau status..." value="<?= htmlspecialchars($keyword) ?>">
        <button class="btn btn-outline-secondary" type="submit">Cari</button>
    </form>

    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Deskripsi</th>
                        <th>Gambar Referensi</th>
                        <th>Status</th>
                        <th>Ubah Status</th>
                        <th>Harga</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['user_id'] ?></td>
                            <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                            <td>
                                <?php if (!empty($row['reference_image'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($row['reference_image']) ?>" alt="gambar" style="width: 60px; height: 60px; object-fit:cover;">
                                <?php else: ?>
                                    Tidak ada
                                <?php endif; ?>
                            </td>
                            <td class="status-<?= strtolower($row['status']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" class="form-select">
                                        <option value="submitted" <?= $row['status'] == 'submitted' ? 'selected' : '' ?>>Submitted</option>
                                        <option value="in_progress" <?= $row['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed" <?= $row['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $row['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form method="POST" class="d-flex" style="gap: 4px;">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="text" name="harga" value="<?= $row['estimated_price'] ?>" class="form-control form-control-sm" placeholder="Harga" min="0">
                                    <button type="submit" name="update_harga" class="btn btn-sm btn-success">Simpan</button>
                                </form>
                            </td>
                            <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <a href="custom.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus custom order ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Tidak ada data ditemukan.</p>
    <?php endif; ?>
</div>

</body>

</html>