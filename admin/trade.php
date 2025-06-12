<?php
require_once '../config/database.php';
session_start();

include 'includes/header.php';
include 'includes/sidebar.php';

// Tangani aksi beli dan hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM gold_transactions WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (isset($_POST['buy'])) {
        // Tandai sebagai dibeli
        $stmt = $pdo->prepare("UPDATE gold_transactions SET status = 'completed' WHERE id = ?");
        $stmt->execute([$id]);

        // Ambil data transaksi emas
        $get = $pdo->prepare("SELECT * FROM gold_transactions WHERE id = ?");
        $get->execute([$id]);
        $gold = $get->fetch();

        // Masukkan ke tabel transactions
        $insert = $pdo->prepare("INSERT INTO transactions (user_id, total_price, status, created_at, gold_transaction_id)
                                 VALUES (:user_id, :total_price, 'completed', NOW(), :gold_transaction_id)");
        $insert->execute([
            ':user_id' => $gold['user_id'],
            ':total_price' => $gold['total_price'],
            ':gold_transaction_id' => $id
        ]);
    }
    
    header("Location: gold_admin.php");
    exit;
}

// Ambil semua transaksi emas jual
$stmt = $pdo->query("SELECT gold_transactions.*, users.name AS user_name 
                     FROM gold_transactions 
                     JOIN users ON gold_transactions.user_id = users.id 
                     WHERE gold_transactions.type = 'sell' 
                     ORDER BY gold_transactions.created_at DESC");
$transactions = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 class="mb-4">Transaksi Penjualan Emas</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nama User</th>
                <th>Berat (gram)</th>
                <th>Harga per gram</th>
                <th>Total</th>
                <th>Status</th>
                <th>Waktu</th>
                <th>Aksi</th>
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
                <td>
                    <?php if ($trx['status'] === 'pending'): ?>
                        <a href="trade_action.php?action=buy&id=<?= $trx['id'] ?>" class="btn btn-success btn-sm"
                            onclick="return confirm('Yakin ingin membeli emas ini?')">Beli</a>
                        <a href="trade_action.php?action=delete&id=<?= $trx['id'] ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus</a>
                    <?php else: ?>
                        <span class="text-muted">Sudah dibeli</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
