<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];

$limit = 5;

// ===== PAGINATION & SEARCH KATALOG =====
$page_katalog = isset($_GET['page_katalog']) ? (int)$_GET['page_katalog'] : 1;
$search_katalog = isset($_GET['search_katalog']) ? trim($_GET['search_katalog']) : '';
$offset_katalog = ($page_katalog - 1) * $limit;

$params_katalog = [$userId];
$search_clause_katalog = '';

if (!empty($search_katalog)) {
    $search_clause_katalog = "AND t.id IN (
        SELECT oi.order_id 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE p.name LIKE ?
    )";
    $params_katalog[] = "%$search_katalog%";
}

$query_katalog = "SELECT * FROM transactions t 
                  WHERE user_id = ? AND status IN ('completed', 'cancelled') 
                  $search_clause_katalog 
                  ORDER BY completed_at DESC 
                  LIMIT $limit OFFSET $offset_katalog";
$stmt = $pdo->prepare($query_katalog);
$stmt->execute($params_katalog);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total data katalog
$count_query_katalog = "SELECT COUNT(*) FROM transactions t 
                        WHERE user_id = ? AND status IN ('completed', 'cancelled') 
                        $search_clause_katalog";
$stmt = $pdo->prepare($count_query_katalog);
$stmt->execute($params_katalog);
$total_katalog = $stmt->fetchColumn();
$total_pages_katalog = ceil($total_katalog / $limit);

// ===== PAGINATION & SEARCH CUSTOM ORDER =====
$page_custom = isset($_GET['page_custom']) ? (int)$_GET['page_custom'] : 1;
$search_custom = isset($_GET['search_custom']) ? trim($_GET['search_custom']) : '';
$offset_custom = ($page_custom - 1) * $limit;

$params_custom = [$userId];
$search_clause_custom = '';

if (!empty($search_custom)) {
    $search_clause_custom = "AND description LIKE ?";
    $params_custom[] = "%$search_custom%";
}

$query_custom = "SELECT * FROM custom_orders 
                 WHERE user_id = ? AND status = 'arrived' 
                 $search_clause_custom 
                 ORDER BY received_at DESC 
                 LIMIT $limit OFFSET $offset_custom";
$stmt = $pdo->prepare($query_custom);
$stmt->execute($params_custom);
$customOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total data custom order
$count_query_custom = "SELECT COUNT(*) FROM custom_orders 
                       WHERE user_id = ? AND status = 'arrived' 
                       $search_clause_custom";
$stmt = $pdo->prepare($count_query_custom);
$stmt->execute($params_custom);
$total_custom = $stmt->fetchColumn();
$total_pages_custom = ceil($total_custom / $limit);
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
                <div class="section-card mb-3">
                    <h4>Riwayat Transaksi Katalog</h4>

                    <form class="row mb-3" method="GET">
                        <div class="col-8">
                            <input type="text" class="form-control" name="search_katalog" placeholder="Cari berdasarkan produk" value="<?= htmlspecialchars($search_katalog) ?>">
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>

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
                                            <td><a href="detail_transaksi.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-primary">Lihat Detail</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages_katalog; $i++): ?>
                                    <li class="page-item <?= $i == $page_katalog ? 'active' : '' ?>">
                                        <a class="page-link" href="?page_katalog=<?= $i ?>&search_katalog=<?= urlencode($search_katalog) ?>"> <?= $i ?> </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <p class="text-muted">Belum ada transaksi katalog.</p>
                    <?php endif; ?>
                </div>

                <!-- Riwayat Custom Order -->
                <div class="section-card">
                    <h4>Riwayat Custom Order</h4>

                    <form class="row mb-3 " method="GET">
                        <div class="col-8">
                            <input type="text" class="form-control" name="search_custom" placeholder="Cari berdasarkan deskripsi" value="<?= htmlspecialchars($search_custom) ?>">
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>

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
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages_custom; $i++): ?>
                                    <li class="page-item <?= $i == $page_custom ? 'active' : '' ?>">
                                        <a class="page-link" href="?page_custom=<?= $i ?>&search_custom=<?= urlencode($search_custom) ?>"> <?= $i ?> </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
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