<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Proses konfirmasi diterima jika ada parameter id_konfirmasi
if (isset($_GET['id_konfirmasi'])) {
    $order_id = $_GET['id_konfirmasi'];

    // Cek validitas transaksi
    $stmtCheck = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ? AND status = 'shipped'");
    $stmtCheck->execute([$order_id, $user['id']]);
    $trxCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($trxCheck) {
        $now = date('Y-m-d H:i:s');
        $update = $pdo->prepare("UPDATE transactions SET status = 'completed', completed_at = ? WHERE id = ?");
        $update->execute([$now, $order_id]);

        header("Location: pengiriman.php?konfirmasi=berhasil");
        exit;
    }
}

// Ambil transaksi user dengan status 'shipped'
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND status IN ('shipped') ORDER BY shipped_at DESC");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            padding: 30px;
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
                    <h4>Sedang Dikirim / Selesai</h4>

                    <?php if (isset($_GET['konfirmasi']) && $_GET['konfirmasi'] === 'berhasil'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Barang berhasil dikonfirmasi sebagai diterima. Terima kasih!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (count($transactions) > 0): ?>
                        <div class="table-responsive">
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
                        <p class="text-muted">Belum ada pengiriman saat ini.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'template/footer.php'; ?>


</body>

</html>