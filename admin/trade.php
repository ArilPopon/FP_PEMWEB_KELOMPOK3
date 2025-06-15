<?php
require_once '../config/database.php';
session_start();

include 'includes/header.php';
include 'includes/sidebar.php';

// Tangani aksi hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM gold_transactions WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: trade.php");
    exit;
}

// Ambil semua transaksi emas jual beserta data user
$stmt = $pdo->query("SELECT 
                        gt.*, 
                        u.name AS user_name, 
                        u.email, 
                        u.phone, 
                        u.address 
                    FROM gold_transactions gt
                    JOIN users u ON gt.user_id = u.id
                    WHERE gt.type = 'sell' 
                    ORDER BY gt.created_at DESC");
$transactions = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 class="mb-4">Transaksi Penjualan Emas</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nama User</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Berat (gram)</th>
                <th>Harga per gram</th>
                <th>Total</th>
                <th>Waktu</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $trx): ?>
            <tr>
                <td><?= htmlspecialchars($trx['user_name']) ?></td>
                <td><?= htmlspecialchars($trx['email']) ?></td>
                <td><?= htmlspecialchars($trx['phone']) ?></td>
                <td><?= nl2br(htmlspecialchars($trx['address'])) ?></td>
                <td><?= htmlspecialchars($trx['weight']) ?> g</td>
                <td>Rp <?= number_format($trx['price_per_gram'], 0, ',', '.') ?></td>
                <td>Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></td>
                <td><?= $trx['created_at'] ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                        <input type="hidden" name="id" value="<?= $trx['id'] ?>">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
