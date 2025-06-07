<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Menentukan status
$validStatuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
if (isset($_GET['status']) && in_array($_GET['status'], $validStatuses)) {
    $status = $_GET['status'];
} else {
    $status = 'pending'; // default status
}

// Query untuk mengabil filter status transaksi
$status_query = "SHOW COLUMNS FROM transactions";
$statusStmt = $pdo->prepare($status_query);
$statusStmt->execute();
$column = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

// Mencari kolom 'status'
$statusColumn = null;
foreach ($column as $column) {
    if ($column['Field'] === 'status') {
        $statusColumn = $column;
        break;
    }
}

// Mengambil nilai ENUM dari kolom
if ($statusColumn && isset($statusColumn['Type'])) {
    preg_match("/^enum\((.*)\)$/", $statusColumn['Type'], $matches);
    if (isset($matches[1])) {
        // Mengambil nilai ENUM dan menghapus tanda kutip
        $enumValues = array_map('trim', explode(',', $matches[1]));
        $enumValues = array_map(function($value) {
            return trim($value, "'");
        }, $enumValues);
    } else {
        $enumValues = []; // Jika tidak ada nilai ENUM ditemukan
    }
} else {
    $enumValues = []; // Jika kolom tidak ditemukan
}

// Query untuk mengabil data transaksi berdasarkan status
$query = "SELECT * FROM transactions WHERE status = :status";
$params = [':status' => $status];

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="content">
    <h2 style="text-align: center;">Data Transaksi</h2>
    <form method="GET" class="d-flex">
        <label for="status-select" class="me-2 d-flex align-items-center">Filter by Status:</label>
        <select name="status" id="status-select" class="form-select w-auto" onchange="this.form.submit()">
            <?php 
                foreach ($enumValues as $enumValue) {
                    echo '<option value="' . htmlspecialchars($enumValue) . '">' . htmlspecialchars($enumValue) . '</option>';
                }
            ?>
        </select>
    </form>
    <?php if (count($transactions) > 0) : ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Create At</th>
                    <th>id</th>
                    <th>User id</th>
                    <th>Product id</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transactions): ?>
                <tr>
                    <td><?= htmlspecialchars($transactions['created_at']); ?></td>
                    <td><?= htmlspecialchars($transactions['id']); ?></td>
                    <td><?= htmlspecialchars($transactions['user_id']); ?></td>
                    <td><?= htmlspecialchars($transactions['product_id']); ?></td>
                    <td><?= htmlspecialchars($transactions['quantity']); ?></td>
                    <td><?= htmlspecialchars($transactions['total_price']); ?></td>
                    <td><?= htmlspecialchars($transactions['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-muted" style="text-align: center; font-size:20px;">Data Kosong!</p>
        <?php endif; ?>
    </div>
</div>