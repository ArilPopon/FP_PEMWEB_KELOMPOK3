<?php
session_start();
require_once "config/database.php";
include "template/header.php";

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'] ?? null;
if (!$user_id) {
    die("User ID tidak ditemukan dalam session.");
}

// Ambil data janji temu
$appointment_stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = :user_id ORDER BY created_at DESC");
$appointment_stmt->execute(['user_id' => $user_id]);
$appointment_data = $appointment_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data pesanan custom
$custom_stmt = $pdo->prepare("SELECT * FROM custom_orders WHERE user_id = :user_id ORDER BY created_at DESC");
$custom_stmt->execute(['user_id' => $user_id]);
$custom_data = $custom_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data transaksi emas
$gold_stmt = $pdo->prepare("SELECT * FROM gold_transactions WHERE user_id = :user_id ORDER BY created_at DESC");
$gold_stmt->execute(['user_id' => $user_id]);
$gold_data = $gold_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data pemesanan produk
$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
$order_stmt->execute(['user_id' => $user_id]);
$order_data = $order_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengguna</title>
    <style>
        .section {
            margin-bottom: 40px;
        }
        .section h2 {
            margin-bottom: 15px;
            color: #333;
        }
        .box {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .box img {
            max-width: 100px;
            height: auto;
        }
        .label {
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Selamat datang di Dashboard</h1>

<!-- Janji Temu -->
<div class="section">
    <h2>Riwayat Janji Temu</h2>
    <?php if (empty($appointment_data)): ?>
        <p>Tidak ada janji temu yang tercatat.</p>
    <?php else: ?>
        <?php foreach ($appointment_data as $app): ?>
            <div class="box">
                <div><span class="label">Tanggal:</span> <?= $app['appointment_date'] ?></div>
                <div><span class="label">Waktu:</span> <?= $app['appointment_time'] ?></div>
                <div><span class="label">Catatan:</span> <?= htmlspecialchars($app['note']) ?></div>
                <div><span class="label">Status:</span> <?= ucfirst($app['status']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pesanan Custom -->
<div class="section">
    <h2>Pesanan Custom</h2>
    <?php if (empty($custom_data)): ?>
        <p>Belum ada pesanan custom.</p>
    <?php else: ?>
        <?php foreach ($custom_data as $order): ?>
            <div class="box">
                <div><span class="label">Deskripsi:</span> <?= nl2br(htmlspecialchars($order['description'])) ?></div>
                <div><span class="label">Status:</span> <?= ucfirst(str_replace('_', ' ', $order['status'])) ?></div>
                <div><span class="label">Estimasi Harga:</span> Rp<?= number_format($order['estimated_price'], 2, ',', '.') ?></div>
                <div><span class="label">Gambar Referensi:</span><br>
                    <?php if ($order['reference_image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($order['reference_image']) ?>">
                    <?php else: ?>
                        Tidak ada
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Transaksi Emas -->
<div class="section">
    <h2>Transaksi Emas</h2>
    <?php if (empty($gold_data)): ?>
        <p>Belum ada transaksi emas.</p>
    <?php else: ?>
        <?php foreach ($gold_data as $tx): ?>
            <div class="box">
                <div><span class="label">Jenis:</span> <?= ucfirst($tx['type']) ?></div>
                <div><span class="label">Berat:</span> <?= $tx['weight'] ?> g</div>
                <div><span class="label">Harga/gram:</span> Rp<?= number_format($tx['price_per_gram'], 2, ',', '.') ?></div>
                <div><span class="label">Total:</span> Rp<?= number_format($tx['total_price'], 2, ',', '.') ?></div>
                <div><span class="label">Status:</span> <?= ucfirst($tx['status']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pemesanan Produk -->
<div class="section">
    <h2>Riwayat Pemesanan Produk</h2>
    <?php if (empty($order_data)): ?>
        <p>Belum ada riwayat pemesanan produk.</p>
    <?php else: ?>
        <?php foreach ($order_data as $order): ?>
            <div class="box">
                <div><span class="label">Total Harga:</span> Rp<?= number_format($order['total_price'], 2, ',', '.') ?></div>
                <div><span class="label">Status:</span> <?= ucfirst($order['status']) ?></div>
                <div><span class="label">Bukti:</span><br>
                    <?php if ($order['proof']): ?>
                        <img src="../uploads/<?= htmlspecialchars($order['proof']) ?>">
                    <?php else: ?>
                        Belum upload
                    <?php endif; ?>
                </div>
                <div><span class="label">Tanggal:</span> <?= $order['created_at'] ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>

<?php include "template/footer.php"; ?>
