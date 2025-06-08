<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Status yang valid
$validStatuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
$status = isset($_GET['status']) && in_array($_GET['status'], $validStatuses) ? $_GET['status'] : 'pending';

// Ambil semua status untuk filter dropdown
$enumValues = $validStatuses;

// Ambil data transaksi dari tabel orders
$query = "SELECT o.*, u.name AS user_name 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE o.status = :status 
          ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([':status' => $status]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2 style="text-align: center;">Data Transaksi</h2>

    <form method="GET" class="d-flex align-items-center mb-3">
        <label for="status-select" class="me-2">Filter Status:</label>
        <select name="status" id="status-select" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ($enumValues as $enumValue): ?>
                <option value="<?= $enumValue ?>" <?= $status === $enumValue ? 'selected' : '' ?>>
                    <?= ucfirst($enumValue) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (count($orders) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Detail Produk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['user_name']) ?> (ID: <?= $order['user_id'] ?>)</td>
                            <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td><?= ucfirst($order['status']) ?></td>
                            <td><?= $order['created_at'] ?></td>
                            <td>
                                <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center" style="font-size: 18px;">Tidak ada transaksi dengan status <strong><?= htmlspecialchars($status) ?></strong>.</p>
    <?php endif; ?>
</div>