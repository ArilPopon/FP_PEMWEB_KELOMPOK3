<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Riwayat katalog
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND status IN ('completed', 'cancelled') ORDER BY completed_at DESC");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Riwayat custom order
$stmtCustom = $pdo->prepare("SELECT * FROM custom_orders WHERE user_id = ? AND status = 'arrived' ORDER BY received_at DESC");
$stmtCustom->execute([$user['id']]);
$customOrders = $stmtCustom->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #fff;
            padding-top: 30px;
        }

        .sidebar a {
            color: #ccc;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }

        .content {
            padding: 0, 30px, 0, 30px;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        img.thumbnail {
            max-width: 100px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 p-0 sidebar">
                <?php include 'template/sidebar_user.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 content">
                <div class="section-card">
                    <h4>Riwayat Transaksi Katalog</h4>

                    <?php if (count($transactions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Produk</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $trx): ?>
                                        <?php
                                        $stmtItems = $pdo->prepare("
                                            SELECT oi.*, p.name AS product_name
                                            FROM order_items oi
                                            JOIN products p ON oi.product_id = p.id
                                            WHERE oi.order_id = ?
                                        ");
                                        $stmtItems->execute([$trx['id']]);
                                        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                                        $produk = '';
                                        foreach ($items as $item) {
                                            $produk .= htmlspecialchars($item['product_name']) . ' (' . $item['quantity'] . 'x)<br>';
                                        }
                                        ?>
                                        <tr>
                                            <td><?= $trx['id'] ?></td>
                                            <td><?= $produk ?></td>
                                            <td>Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></td>
                                            <td><?= ucfirst($trx['status']) ?></td>
                                            <td><?= date('d M Y H:i', strtotime($trx['completed_at'])) ?></td>
                                            <td>
                                                <a href="detail_transaksi.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-primary">
                                                    Lihat Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada transaksi katalog.</p>
                    <?php endif; ?>
                </div>

                <!-- Riwayat Custom Order -->
                <div class="section-card">
                    <h4>Riwayat Custom Order</h4>

                    <?php if (count($customOrders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                        <th>Tanggal Diterima</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customOrders as $order): ?>
                                        <tr>
                                            <td><?= nl2br(htmlspecialchars($order['description'])) ?></td>
                                            <td>Rp <?= number_format($order['estimated_price'], 0, ',', '.') ?></td>
                                            <td><span class="badge bg-success"><?= ucfirst($order['status']) ?></span></td>
                                            <td><?= date('d M Y H:i', strtotime($order['received_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada custom order yang selesai.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php include 'template/footer.php'; ?>
</body>

</html>