<?php
require_once '../config/database.php';
session_start();

if (!isset($_GET['action'], $_GET['id'])) {
    header("Location: trade.php");
    exit;
}

$id = intval($_GET['id']);
$action = $_GET['action'];

if ($action === 'delete') {
    // Hapus transaksi emas
    $stmt = $pdo->prepare("DELETE FROM gold_transactions WHERE id = ?");
    $stmt->execute([$id]);

} elseif ($action === 'buy') {
    // Ambil data transaksi emas
    $get = $pdo->prepare("SELECT * FROM gold_transactions WHERE id = ?");
    $get->execute([$id]);
    $gold = $get->fetch();

    if ($gold && $gold['status'] === 'pending') {
        // Tandai sebagai dibeli
        $update = $pdo->prepare("UPDATE gold_transactions SET status = 'completed' WHERE id = ?");
        $update->execute([$id]);

        // Masukkan ke tabel orders (untuk konfirmasi_pembayaran)
        $insert = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, created_at)
                                 VALUES (:user_id, :total_price, 'pending', NOW())");
        $insert->execute([
            ':user_id' => $gold['user_id'],
            ':total_price' => $gold['total_price']
        ]);
    }
}

header("Location: trade.php");
exit;
