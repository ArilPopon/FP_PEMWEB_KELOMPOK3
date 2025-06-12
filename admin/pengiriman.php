<?php
require_once '../config/database.php';
require_once 'middleware/admin_auth.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Proses kirim (ubah status ke 'shipped' dan isi shipped_at)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];

    $stmt = $pdo->prepare("UPDATE transactions SET status = 'shipped', shipped_at = NOW() WHERE id = ?");
    $stmt->execute([$orderId]);

    $message = "Transaksi #$orderId berhasil dikirim.";
}

// Ambil transaksi dengan status 'paid' (Perlu Dikirim)
$stmtPaid = $pdo->prepare("SELECT t.*, u.name, u.email 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.status = 'paid'
    ORDER BY t.paid_at DESC");
$stmtPaid->execute();
$ordersPaid = $stmtPaid->fetchAll(PDO::FETCH_ASSOC);

// Ambil transaksi dengan status 'shipped' (Sedang Dikirim)
$stmtShipped = $pdo->prepare("SELECT t.*, u.name, u.email 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.status = 'shipped'
    ORDER BY t.shipped_at DESC");
$stmtShipped->execute();
$ordersShipped = $stmtShipped->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2 style="text-align: center;">Pengiriman Barang</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Bagian 1: Perlu Dikirim -->
    <h4 class="mt-4 mb-2">Perlu Dikirim</h4>
    <?php if (count($ordersPaid) > 0): ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID Transaksi</th>
                        <th>User</th>
                        <th>Total Harga</th>
                        <th>Pembayaran Dikonfirmasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordersPaid as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td>
                                <?= $order['paid_at'] ? date('d-m-Y H:i', strtotime($order['paid_at'])) : '<em class="text-muted">-</em>' ?>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Kirim barang untuk Transaksi #<?= $order['id'] ?>?')">Kirim</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Tidak ada transaksi yang perlu dikirim.</p>
    <?php endif; ?>

    <!-- Bagian 2: Sedang Dikirim -->
    <h4 class="mt-5 mb-2">Sedang Dikirim</h4>
    <?php if (count($ordersShipped) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID Transaksi</th>
                        <th>User</th>
                        <th>Total Harga</th>
                        <th>Waktu Kirim</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordersShipped as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td>
                                <?= $order['shipped_at'] ? date('d-m-Y H:i', strtotime($order['shipped_at'])) : '<em class="text-muted">-</em>' ?>
                            </td>
                            <td><span class="badge bg-info">Dikirim</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Tidak ada transaksi yang sedang dikirim.</p>
    <?php endif; ?>
</div>