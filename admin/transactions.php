<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Status yang valid + tambahan 'all'
$validStatuses = ['all', 'pending', 'paid', 'shipped', 'completed', 'cancelled'];
$status = isset($_GET['status']) && in_array($_GET['status'], $validStatuses) ? $_GET['status'] : 'pending';

// Query transaksi berdasarkan status (atau semua)
if ($status === 'all') {
    $query = "SELECT t.*, u.name AS user_name 
              FROM transactions t 
              JOIN users u ON t.user_id = u.id 
              ORDER BY t.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
} else {
    $query = "SELECT t.*, u.name AS user_name 
              FROM transactions t 
              JOIN users u ON t.user_id = u.id 
              WHERE t.status = :status 
              ORDER BY t.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':status' => $status]);
}

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fungsi bantu ambil waktu berdasarkan status
function getStatusTimestamp($trx, $status)
{
    switch ($status) {
        case 'paid':
            return $trx['paid_at'];
        case 'shipped':
            return $trx['shipped_at'];
        case 'completed':
            return $trx['completed_at'];
        default:
            return $trx['created_at'];
    }
}
?>

<div class="content">
    <h2 style="text-align: center;">Data Transaksi</h2>

    <form method="GET" class="d-flex align-items-center mb-3">
        <label for="status-select" class="me-2">Filter Status:</label>
        <select name="status" id="status-select" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ($validStatuses as $enumValue): ?>
                <option value="<?= $enumValue ?>" <?= $status === $enumValue ? 'selected' : '' ?>>
                    <?= $enumValue === 'all' ? 'Semua' : ucfirst($enumValue) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (count($transactions) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Transaksi ID</th>
                        <th>User</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th>Detail Produk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trx): ?>
                        <tr>
                            <td><?= $trx['id'] ?></td>
                            <td><?= htmlspecialchars($trx['user_name']) ?></td>
                            <td>Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></td>
                            <td><?= ucfirst($trx['status']) ?></td>
                            <td><?= getStatusTimestamp($trx, $trx['status']) ?></td>
                            <td>
                                <a href="order_detail.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-primary">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center" style="font-size: 18px;">Tidak ada transaksi dengan status <strong><?= htmlspecialchars($status) ?></strong>.</p>
    <?php endif; ?>
</div>