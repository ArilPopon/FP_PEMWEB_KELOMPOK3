<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='content'><p>ID pesanan tidak valid.</p></div>";
    exit;
}

$orderId = $_GET['id'];

// Ambil detail order
$orderStmt = $pdo->prepare("
    SELECT o.*, u.name AS user_name, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = :id
");
$orderStmt->execute([':id' => $orderId]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='content'><p>Pesanan tidak ditemukan.</p></div>";
    exit;
}

// Ambil item dalam order
$itemStmt = $pdo->prepare("
    SELECT oi.*, p.name AS product_name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :order_id
");
$itemStmt->execute([':order_id' => $orderId]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2>Detail Pesanan #<?= $order['id'] ?></h2>
    <p><strong>Nama Pengguna:</strong> <?= htmlspecialchars($order['user_name']) ?> (<?= $order['email'] ?>)</p>
    <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
    <p><strong>Tanggal:</strong> <?= $order['created_at'] ?></p>
    <p><strong>Total:</strong> Rp <?= number_format($order['total_price'], 0, ',', '.') ?></p>

    <h4 class="mt-4">Daftar Produk:</h4>
    <?php if (count($items) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Tidak ada item dalam pesanan ini.</p>
    <?php endif; ?>

    <a href="transactions.php" class="btn btn-secondary mt-3">Kembali ke Transaksi</a>
</div>