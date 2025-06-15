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

// Konfirmasi diterima (katalog & custom)
if (isset($_GET['id_konfirmasi'])) {
    $id = $_GET['id_konfirmasi'];
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ? AND status = 'shipped'");
    $stmt->execute([$id, $user['id']]);
    if ($stmt->fetch()) {
        $now = date('Y-m-d H:i:s');
        $pdo->prepare("UPDATE transactions SET status = 'completed', completed_at = ? WHERE id = ?")->execute([$now, $id]);
        $message = "Barang dari katalog telah dikonfirmasi diterima.";
    }
}
if (isset($_GET['id_konfirmasi_custom'])) {
    $id = $_GET['id_konfirmasi_custom'];
    $stmt = $pdo->prepare("SELECT * FROM custom_orders WHERE id = ? AND user_id = ? AND status = 'shipped'");
    $stmt->execute([$id, $user['id']]);
    if ($stmt->fetch()) {
        $now = date('Y-m-d H:i:s');
        $pdo->prepare("UPDATE custom_orders SET status = 'arrived', received_at = ? WHERE id = ?")->execute([$now, $id]);
        $message = "Barang custom telah dikonfirmasi diterima.";
    }
}

// Search & Pagination untuk katalog
$search_katalog = isset($_GET['search_katalog']) ? $_GET['search_katalog'] : '';
$page_katalog = isset($_GET['page_katalog']) ? (int)$_GET['page_katalog'] : 1;
$limit = 5;
$offset_katalog = ($page_katalog - 1) * $limit;

// Total katalog
$countStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT t.id)
    FROM transactions t
    JOIN order_items oi ON t.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE t.user_id = ? AND t.status IN ('shipped', 'arrived')
    AND p.name LIKE ?
");
$countStmt->execute([$user['id'], "%$search_katalog%"]);
$total_katalog = $countStmt->fetchColumn();
$total_pages_katalog = ceil($total_katalog / $limit);

// Data katalog
$stmt1 = $pdo->prepare("
    SELECT DISTINCT t.*
    FROM transactions t
    JOIN order_items oi ON t.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE t.user_id = ? AND t.status IN ('shipped', 'arrived')
    AND p.name LIKE ?
    ORDER BY t.shipped_at DESC
    LIMIT $limit OFFSET $offset_katalog
");
$stmt1->execute([$user['id'], "%$search_katalog%"]);
$transactions = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// Search & Pagination untuk custom order
$search_custom = isset($_GET['search_custom']) ? $_GET['search_custom'] : '';
$page_custom = isset($_GET['page_custom']) ? (int)$_GET['page_custom'] : 1;
$offset_custom = ($page_custom - 1) * $limit;

// Total custom
$countCustomStmt = $pdo->prepare("
    SELECT COUNT(*) FROM custom_orders
    WHERE user_id = ? AND status = 'shipped'
    AND description LIKE ?
");
$countCustomStmt->execute([$user['id'], "%$search_custom%"]);
$total_custom = $countCustomStmt->fetchColumn();
$total_pages_custom = ceil($total_custom / $limit);

// Data custom order
$stmt2 = $pdo->prepare("
    SELECT * FROM custom_orders
    WHERE user_id = ? AND status = 'shipped'
    AND description LIKE ?
    ORDER BY shipped_at DESC
    LIMIT $limit OFFSET $offset_custom
");
$stmt2->execute([$user['id'], "%$search_custom%"]);
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
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f6fa;
        }

        .sidebar {
            height: 100vh;
            overflow-y: auto;
            background-color: #343a40;
            color: #fff;
            padding-top: 30px;
            position: sticky;
            top: 0;
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
            padding: 0, 0, 0, 30px;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .img-thumb {
            width: 80px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 p-0 sidebar">
                <?php include 'template/sidebar_user.php'; ?></div>
            <div class="col-md-9 content">
                <div class="section-card">
                    <h4>Pengiriman Produk Katalog</h4>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form class="mb-3" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search_katalog" placeholder="Cari produk..."
                                value="<?= htmlspecialchars($search_katalog) ?>">
                            <button class="btn btn-outline-secondary" type="submit">Cari</button>
                        </div>
                    </form>

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
                                                    <a href="?id_konfirmasi=<?= $trx['id'] ?>" class="btn btn-success btn-sm"
                                                        onclick="return confirm('Konfirmasi barang telah diterima?')">Konfirmasi Diterima</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- Pagination katalog -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages_katalog; $i++): ?>
                                        <li class="page-item <?= ($i == $page_katalog) ? 'active' : '' ?>">
                                            <a class="page-link"
                                                href="?search_katalog=<?= urlencode($search_katalog) ?>&page_katalog=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada pengiriman dari katalog.</p>
                    <?php endif; ?>

                    <h4 class="mt-5">Pengiriman Custom Order</h4>

                    <form class="mb-3" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search_custom" placeholder="Cari deskripsi custom..."
                                value="<?= htmlspecialchars($search_custom) ?>">
                            <button class="btn btn-outline-secondary" type="submit">Cari</button>
                        </div>
                    </form>

                    <?php if (count($customOrders) > 0): ?>
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
                                        <td><?= $custom['shipped_at'] ? date('d M Y H:i', strtotime($custom['shipped_at'])) : '-' ?></td>
                                        <td>
                                            <a href="?id_konfirmasi_custom=<?= $custom['id'] ?>" class="btn btn-success btn-sm"
                                                onclick="return confirm('Konfirmasi barang custom telah diterima?')">Konfirmasi Diterima</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination custom -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages_custom; $i++): ?>
                                    <li class="page-item <?= ($i == $page_custom) ? 'active' : '' ?>">
                                        <a class="page-link"
                                            href="?search_custom=<?= urlencode($search_custom) ?>&page_custom=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
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