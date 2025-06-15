<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "<p>Transaksi tidak ditemukan.</p>";
    exit;
}

$user = $_SESSION['user'];
$trx_id = $_GET['id'];

// Ambil data transaksi
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$trx_id, $user['id']]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    echo "<p>Transaksi tidak ditemukan atau bukan milik Anda.</p>";
    exit;
}

// Ambil item produk
$stmtItems = $pdo->prepare("
    SELECT oi.*, p.name AS product_name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$trx_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// Fungsi format status
function getStatusLabel($status)
{
    return match ($status) {
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Sudah Dibayar',
        'shipped' => 'Sedang Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        default => ucfirst($status),
    };
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin: 30px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            max-width: 900px;
        }
    </style>
</head>

<body>
    <div class="section-card">
        <h4>Detail Transaksi #<?= $transaction['id'] ?></h4>
        <hr>

        <p><strong>Status:</strong> <?= getStatusLabel($transaction['status']) ?></p>
        <p><strong>Total Harga:</strong> Rp <?= number_format($transaction['total_price'], 0, ',', '.') ?></p>
        <p><strong>Waktu Pesan:</strong> <?= date('d M Y H:i', strtotime($transaction['created_at'])) ?></p>

        <?php if ($transaction['paid_at']): ?>
            <p><strong>Dibayar:</strong> <?= date('d M Y H:i', strtotime($transaction['paid_at'])) ?></p>
        <?php endif; ?>
        <?php if ($transaction['shipped_at']): ?>
            <p><strong>Dikirim:</strong> <?= date('d M Y H:i', strtotime($transaction['shipped_at'])) ?></p>
        <?php endif; ?>
        <?php if ($transaction['completed_at']): ?>
            <p><strong>Diselesaikan:</strong> <?= date('d M Y H:i', strtotime($transaction['completed_at'])) ?></p>
        <?php endif; ?>

        <h5 class="mt-4">Produk Dipesan</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mt-2">
                <thead class="table-light">
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
                            <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="riwayat.php" class="btn btn-secondary mt-3">← Kembali ke Riwayat</a>
    </div>
</body>

</html>