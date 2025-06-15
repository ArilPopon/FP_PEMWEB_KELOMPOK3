<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Proses konfirmasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type'], $_POST['order_id'], $_POST['action'])) {
    $type = $_POST['type'];
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];

    if ($type === 'katalog') {
        if ($action === 'accept') {
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'paid', paid_at = NOW() WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'cancelled', paid_at = NOW() WHERE id = ?");
        }
        $stmt->execute([$orderId]);
    } elseif ($type === 'custom') {
        if ($action === 'accept') {
            $stmt = $pdo->prepare("UPDATE custom_orders SET status = 'in_progress' WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE custom_orders SET status = 'cancelled' WHERE id = ?");
        }
        $stmt->execute([$orderId]);
    }

    $message = "Pembayaran berhasil diproses.";
}

// Ambil transaksi katalog dengan status pending
$stmt1 = $pdo->prepare("SELECT * FROM transactions WHERE status = 'pending' ORDER BY created_at DESC");
$stmt1->execute();
$transactions = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// Ambil custom order yang punya bukti pembayaran
$stmt2 = $pdo->prepare("SELECT * FROM custom_orders WHERE payment_proof IS NOT NULL AND status = 'submitted' ORDER BY created_at DESC");
$stmt2->execute();
$customOrders = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2 class="text-center mb-4">Konfirmasi Pembayaran</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Transaksi Katalog -->
    <h4>Transaksi Katalog</h4>
    <?php if (count($transactions) > 0): ?>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Total Harga</th>
                        <th>Waktu</th>
                        <th>Bukti Transfer</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?= $tx['id'] ?></td>
                            <td><?= $tx['user_id'] ?></td>
                            <td>Rp <?= number_format($tx['total_price'], 0, ',', '.') ?></td>
                            <td><?= $tx['created_at'] ?></td>
                            <td>
                                <?php if (!empty($tx['proof'])): ?>
                                    <a href="../uploads/bukti/<?= htmlspecialchars($tx['proof']) ?>" target="_blank">Lihat Bukti</a>
                                <?php else: ?>
                                    <span class="text-muted">Belum ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="type" value="katalog">
                                    <input type="hidden" name="order_id" value="<?= $tx['id'] ?>">
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
        <p class="text-muted">Tidak ada transaksi katalog yang menunggu konfirmasi.</p>
    <?php endif; ?>

    <!-- Custom Orders -->
    <h4>Custom Orders</h4>
    <?php if (count($customOrders) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Waktu</th>
                        <th>Bukti Transfer</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customOrders as $co): ?>
                        <tr>
                            <td><?= $co['id'] ?></td>
                            <td><?= $co['user_id'] ?></td>
                            <td><?= htmlspecialchars($co['description']) ?></td>
                            <td>Rp <?= number_format($co['estimated_price'], 0, ',', '.') ?></td>
                            <td><?= $co['created_at'] ?></td>
                            <td>
                                <a href="../uploads/<?= htmlspecialchars($co['payment_proof']) ?>" target="_blank">Lihat Bukti</a>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="type" value="custom">
                                    <input type="hidden" name="order_id" value="<?= $co['id'] ?>">
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
        <p class="text-muted">Tidak ada custom order yang menunggu konfirmasi.</p>
    <?php endif; ?>
</div>