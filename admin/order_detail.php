<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='content'><p>ID transaksi tidak valid.</p></div>";
    exit;
}

$transactionId = $_GET['id'];

// Ambil detail transaksi
$transactionStmt = $pdo->prepare("
    SELECT t.*, u.name AS user_name, u.email 
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = :id
");
$transactionStmt->execute([':id' => $transactionId]);
$transaction = $transactionStmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    echo "<div class='content'><p>Transaksi tidak ditemukan.</p></div>";
    exit;
}

// Ambil item dalam transaksi
$itemStmt = $pdo->prepare("
    SELECT oi.*, p.name AS product_name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :order_id
");
$itemStmt->execute([':order_id' => $transactionId]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2>Detail Transaksi #<?= $transaction['id'] ?></h2>
    <p><strong>Nama Pengguna:</strong> <?= htmlspecialchars($transaction['user_name']) ?> (<?= $transaction['email'] ?>)</p>
    <p><strong>Status:</strong> <?= ucfirst($transaction['status']) ?></p>
    <p><strong>Tanggal:</strong> <?= $transaction['created_at'] ?></p>
    <p><strong>Total:</strong> Rp <?= number_format($transaction['total_price'], 0, ',', '.') ?></p>

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
        <p class="text-muted">Tidak ada item dalam transaksi ini.</p>
    <?php endif; ?>

    <a href="transactions.php" class="btn btn-secondary mt-3">Kembali ke Transaksi</a>
</div>