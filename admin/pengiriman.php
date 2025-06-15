<?php
require_once '../config/database.php';
require_once 'middleware/admin_auth.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Proses kirim transaksi katalog
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['order_id'])) {
        $orderId = $_POST['order_id'];
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'shipped', shipped_at = NOW() WHERE id = ?");
        $stmt->execute([$orderId]);
        $message = "Transaksi #$orderId berhasil dikirim.";
    }

    // Proses kirim custom order
    if (isset($_POST['custom_order_id'])) {
        $customId = $_POST['custom_order_id'];
        $stmt = $pdo->prepare("UPDATE custom_orders SET status = 'shipped', shipped_at = NOW() WHERE id = ?");
        $stmt->execute([$customId]);
        $message = "Custom Order #$customId berhasil dikirim.";
    }
}

// Ambil transaksi katalog yang sudah dibayar tapi belum dikirim
$stmtPaid = $pdo->prepare("SELECT t.*, u.name, u.email 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.status = 'paid'
    ORDER BY t.paid_at DESC");
$stmtPaid->execute();
$ordersPaid = $stmtPaid->fetchAll(PDO::FETCH_ASSOC);

// Ambil transaksi katalog yang sedang dikirim
$stmtShipped = $pdo->prepare("SELECT t.*, u.name, u.email 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.status = 'shipped'
    ORDER BY t.shipped_at DESC");
$stmtShipped->execute();
$ordersShipped = $stmtShipped->fetchAll(PDO::FETCH_ASSOC);

// Ambil custom orders yang sudah selesai tapi belum dikirim
$stmtCustom = $pdo->prepare("SELECT c.*, u.name, u.email 
    FROM custom_orders c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.status = 'completed' AND c.shipped_at IS NULL
    ORDER BY c.completed_at DESC");
$stmtCustom->execute();
$customOrders = $stmtCustom->fetchAll(PDO::FETCH_ASSOC);

// Ambil custom orders yang sedang dikirim
$stmtCustomShipped = $pdo->prepare("SELECT c.*, u.name, u.email 
    FROM custom_orders c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.status = 'shipped'
    ORDER BY c.shipped_at DESC");
$stmtCustomShipped->execute();
$customOrdersShipped = $stmtCustomShipped->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2 class="text-center">Pengiriman Barang</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Transaksi Katalog Perlu Dikirim -->
    <h4 class="mt-4">Transaksi Perlu Dikirim</h4>
    <?php if (count($ordersPaid) > 0): ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Total Harga</th>
                        <th>Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordersPaid as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td><?= $order['paid_at'] ? date('d-m-Y H:i', strtotime($order['paid_at'])) : '-' ?></td>
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
        <p class="text-muted">Tidak ada transaksi katalog yang perlu dikirim.</p>
    <?php endif; ?>

    <!-- Custom Order Perlu Dikirim -->
    <h4 class="mt-4">Custom Order Perlu Dikirim</h4>
    <?php if (count($customOrders) > 0): ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customOrders as $co): ?>
                        <tr>
                            <td><?= $co['id'] ?></td>
                            <td><?= htmlspecialchars($co['name']) ?></td>
                            <td>Rp <?= number_format($co['estimated_price'], 0, ',', '.') ?></td>
                            <td><span class="badge bg-success"><?= ucfirst($co['status']) ?></span></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="custom_order_id" value="<?= $co['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Kirim Custom Order #<?= $co['id'] ?>?')">Kirim</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Tidak ada custom order yang perlu dikirim.</p>
    <?php endif; ?>

    <!-- Barang Sedang Dikirim -->
    <h4 class="mt-5">Sedang Dikirim</h4>

    <!-- Transaksi Katalog -->
    <h5>Transaksi Katalog</h5>
    <?php if (count($ordersShipped) > 0): ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
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
                            <td><?= date('d-m-Y H:i', strtotime($order['shipped_at'])) ?></td>
                            <td><span class="badge bg-info">Dikirim</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Tidak ada transaksi katalog yang sedang dikirim.</p>
    <?php endif; ?>

    <!-- Custom Order -->
    <h5>Custom Order</h5>
    <?php if (count($customOrdersShipped) > 0): ?>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Harga</th>
                        <th>Waktu Kirim</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customOrdersShipped as $co): ?>
                        <tr>
                            <td><?= $co['id'] ?></td>
                            <td><?= htmlspecialchars($co['name']) ?></td>
                            <td>Rp <?= number_format($co['estimated_price'], 0, ',', '.') ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($co['shipped_at'])) ?></td>
                            <td><span class="badge bg-info">Dikirim</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Tidak ada custom order yang sedang dikirim.</p>
    <?php endif; ?>
</div>