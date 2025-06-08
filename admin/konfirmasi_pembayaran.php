<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Proses konfirmasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];

    $newStatus = ($action === 'accept') ? 'paid' : 'cancelled';

    $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $newStatus,
        ':id' => $orderId
    ]);

    $message = "Pesanan #$orderId berhasil diperbarui menjadi '$newStatus'.";
}

// Ambil semua pesanan yang statusnya pending
$stmt = $pdo->prepare("SELECT * FROM orders WHERE status = 'pending' ORDER BY created_at DESC");
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
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Total Harga</th>
                        <th>Tanggal</th>
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
        <p class="text-center text-muted" style="font-size: 18px;">Tidak ada pesanan yang perlu dikonfirmasi.</p>
    <?php endif; ?>
</div>