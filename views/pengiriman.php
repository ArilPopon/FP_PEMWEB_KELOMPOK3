<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$message = '';

// Konfirmasi katalog diterima
if (isset($_GET['id_konfirmasi'])) {
    $id = $_GET['id_konfirmasi'];
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ? AND status = 'shipped'");
    $stmt->execute([$id, $user['id']]);
    $order = $stmt->fetch();

    if ($order) {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'arrived', completed_at = ? WHERE id = ?");
        $stmt->execute([$now, $id]);
        $message = "Barang dari katalog telah dikonfirmasi diterima.";
    }
}

// Konfirmasi custom order diterima
if (isset($_GET['id_konfirmasi_custom'])) {
    $custom_id = $_GET['id_konfirmasi_custom'];
    $stmt = $pdo->prepare("SELECT * FROM custom_orders WHERE id = ? AND user_id = ? AND status = 'shipped'");
    $stmt->execute([$custom_id, $user['id']]);
    $custom = $stmt->fetch();

    if ($custom) {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE custom_orders SET status = 'arrived', received_at = ? WHERE id = ?");
        $stmt->execute([$now, $custom_id]);
        $message = "Barang custom telah dikonfirmasi diterima.";
    }
}

// Ambil data pengiriman dari katalog
$stmt1 = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND status IN ('shipped', 'arrived') ORDER BY shipped_at DESC");
$stmt1->execute([$user['id']]);
$transactions = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// Ambil data pengiriman dari custom order
$stmt2 = $pdo->prepare("SELECT * FROM custom_orders WHERE user_id = ? AND status = 'shipped' ORDER BY shipped_at DESC");
$stmt2->execute([$user['id']]);
$customOrders = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pengiriman</title>
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
                    <h4>Pengiriman Produk Katalog</h4>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (count($transactions) > 0): ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Transaksi</th>
                                        <th>Produk</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
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

                                        $productList = '';
                                        foreach ($items as $item) {
                                            $productList .= htmlspecialchars($item['product_name']) . ' (' . $item['quantity'] . 'x)<br>';
                                        }

                                        $waktu = ($trx['status'] === 'shipped') ? $trx['shipped_at'] : $trx['completed_at'];
                                        ?>
                                        <tr>
                                            <td><?= $trx['id'] ?></td>
                                            <td><?= $productList ?></td>
                                            <td>Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></td>
                                            <td><?= ucfirst($trx['status']) ?></td>
                                            <td><?= $waktu ? date('d M Y H:i', strtotime($waktu)) : '-' ?></td>
                                            <td>
                                                <?php if ($trx['status'] === 'shipped'): ?>
                                                    <a href="pengiriman.php?id_konfirmasi=<?= $trx['id'] ?>"
                                                        class="btn btn-success btn-sm"
                                                        onclick="return confirm('Apakah Anda yakin ingin mengonfirmasi barang ini telah diterima?')">
                                                        Konfirmasi Diterima
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada pengiriman dari katalog.</p>
                    <?php endif; ?>

                    <h4 class="mt-5">Pengiriman Custom Order</h4>

                    <?php if (count($customOrders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Order</th>
                                        <th>Deskripsi</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customOrders as $custom): ?>
                                        <tr>
                                            <td><?= $custom['id'] ?></td>
                                            <td><?= nl2br(htmlspecialchars($custom['description'])) ?></td>
                                            <td>Rp <?= number_format($custom['estimated_price'], 0, ',', '.') ?></td>
                                            <td><?= ucfirst($custom['status']) ?></td>
                                            <td>
                                                <?php
                                                $waktu = ($custom['status'] === 'shipped') ? $custom['shipped_at'] : $custom['received_at'];
                                                echo $waktu ? date('d M Y H:i', strtotime($waktu)) : '-';
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($custom['status'] === 'shipped'): ?>
                                                    <a href="pengiriman.php?id_konfirmasi_custom=<?= $custom['id'] ?>"
                                                        class="btn btn-success btn-sm"
                                                        onclick="return confirm('Apakah Anda yakin ingin mengonfirmasi barang custom telah diterima?')">
                                                        Konfirmasi Diterima
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada pengiriman dari custom order.</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <?php include 'template/footer.php'; ?>
</body>

</html>