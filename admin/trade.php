<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$stmt = $pdo->query("
    SELECT gold_transactions.*, users.name AS user_name 
    FROM gold_transactions
    JOIN users ON gold_transactions.user_id = users.id
    WHERE gold_transactions.type = 'sell'
    ORDER BY gold_transactions.created_at DESC
");
$transactions = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 class="mb-4">Transaksi Penjualan Emas</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nama User</th>
                <th>Berat (gram)</th>
                <th>Harga per gram</th>
                <th>Total</th>
                <th>Status</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $trx): ?>
            <tr>
                <td><?= htmlspecialchars($trx['user_name']) ?></td>
                <td><?= htmlspecialchars($trx['weight']) ?> g</td>
                <td>Rp <?= number_format($trx['price_per_gram'], 0, ',', '.') ?></td>
                <td>Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></td>
                <td><?= ucfirst($trx['status']) ?></td>
                <td><?= $trx['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
