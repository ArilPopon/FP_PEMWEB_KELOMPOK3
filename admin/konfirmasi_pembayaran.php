<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Proses konfirmasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        // Jika diterima, ubah status menjadi paid dan set paid_at
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'paid', paid_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $orderId]);
        $message = "Transaksi #$orderId berhasil diperbarui menjadi 'paid'.";
    } else {
        // Jika ditolak, ubah status menjadi cancelled dan set paid_at (opsional)
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'cancelled', paid_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $orderId]);
        $message = "Transaksi #$orderId berhasil diperbarui menjadi 'cancelled'.";
    }
}

// Ambil semua transaksi dengan status pending
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE status = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2 class="text-center">Konfirmasi Pembayaran</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (count($orders) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Transaksi ID</th>
                        <th>User ID</th>
                        <th>Total Harga</th>
                        <th>Waktu</th>
                        <th>Bukti Transfer</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= $order['user_id'] ?></td>
                            <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td><?= $order['created_at'] ?></td>
                            <td>
                                <?php if (!empty($order['proof'])): ?>
                                    <a href="../uploads/bukti/<?= htmlspecialchars($order['proof']) ?>" target="_blank">Lihat Bukti</a>
                                <?php else: ?>
                                    <span class="text-muted">Belum ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="action" value="accept" class="btn btn-sm btn-success">Terima</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-muted" style="font-size: 18px;">Tidak ada transaksi yang perlu dikonfirmasi.</p>
    <?php endif; ?>
</div>