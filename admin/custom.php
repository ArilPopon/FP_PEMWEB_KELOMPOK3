<?php
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/CustomerOrder.php';

$customOrder = new CustomerOrder($pdo);

// Tangani aksi status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['id']) && is_numeric($_POST['id'])) {
        $id = $_POST['id'];

        if ($_POST['action'] === 'konfirmasi_pembayaran') {
            $customOrder->updateStatus($id, 'in_progress');
        } elseif ($_POST['action'] === 'selesaikan') {
            $customOrder->updateStatus($id, 'completed');
        } elseif ($_POST['action'] === 'kirim') {
            $customOrder->updateStatus($id, 'shipped', ['shipped_at' => date('Y-m-d H:i:s')]);
        }
    }

    // Tangani pembaruan harga
    if (isset($_POST['id'], $_POST['harga'], $_POST['update_harga'])) {
        $customOrder->updatePrice($_POST['id'], $_POST['harga']);
        header("Location: custom.php");
        exit;
    }
}

// Tangani penghapusan
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $customOrder->delete($_GET['delete']);
    header("Location: custom.php");
    exit;
}

// Tangani pencarian
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$orders = $customOrder->getAll($keyword);
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Customisasi Perhiasan</h2>
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
                        <th>Bukti Bayar</th>
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
                            <td><?= ucfirst(str_replace('_', ' ', $row['status'])) ?></td>
                            <td>
                                <?php if (!empty($row['payment_proof'])): ?>
                                    <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">Lihat Bukti</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
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
                                <?php if ($row['status'] === 'waiting_payment_confirmation' && !empty($row['payment_proof'])): ?>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="action" value="konfirmasi_pembayaran" class="btn btn-sm btn-primary">Konfirmasi</button>
                                    </form>
                                <?php elseif ($row['status'] === 'in_progress'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="action" value="selesaikan" class="btn btn-sm btn-success">Selesai</button>
                                    </form>
                                <?php elseif ($row['status'] === 'completed'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="action" value="kirim" class="btn btn-sm btn-info">Kirim</button>
                                    </form>
                                <?php else: ?>
                                    <a href="custom.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus custom order ini?')">Hapus</a>
                                <?php endif; ?>
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