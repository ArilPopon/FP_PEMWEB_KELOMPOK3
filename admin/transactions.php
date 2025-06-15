<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$validStatuses = ['all', 'pending', 'paid', 'shipped', 'completed', 'cancelled'];

$status = $_GET['status'] ?? 'all';
$customStatus = $_GET['custom_status'] ?? 'all';
$search = $_GET['search'] ?? '';
$customSearch = $_GET['custom_search'] ?? '';

$trx_page = max(1, (int) ($_GET['trx_page'] ?? 1));
$custom_page = max(1, (int) ($_GET['custom_page'] ?? 1));

$trx_limit = 5;
$custom_limit = 5;
$trx_offset = ($trx_page - 1) * $trx_limit;
$custom_offset = ($custom_page - 1) * $custom_limit;

// ======== TRANSAKSI KATALOG ========
$params = [];
$searchClause = '';

if (!empty($search)) {
    $searchClause = " AND (u.name LIKE :search OR p.name LIKE :search)";
    $params[':search'] = "%$search%";
}

$whereClause = $status === 'all' ? '1=1' : 't.status = :status';
if ($status !== 'all') {
    $params[':status'] = $status;
}

$query = "SELECT DISTINCT t.*, u.name AS user_name 
          FROM transactions t
          JOIN users u ON t.user_id = u.id
          LEFT JOIN order_items oi ON t.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          WHERE $whereClause $searchClause
          ORDER BY t.created_at DESC
          LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $trx_limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $trx_offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countQuery = "SELECT COUNT(DISTINCT t.id) FROM transactions t
               JOIN users u ON t.user_id = u.id
               LEFT JOIN order_items oi ON t.id = oi.order_id
               LEFT JOIN products p ON oi.product_id = p.id
               WHERE $whereClause $searchClause";

$countStmt = $pdo->prepare($countQuery);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$total_trx = $countStmt->fetchColumn();
$total_trx_pages = ceil($total_trx / $trx_limit);

// Ambil detail produk untuk masing-masing transaksi
$orderItems = [];
if ($transactions) {
    $orderIds = array_column($transactions, 'id');
    $inQuery = implode(',', array_fill(0, count($orderIds), '?'));
    $itemStmt = $pdo->prepare("SELECT oi.order_id, p.name, oi.quantity, oi.price
                               FROM order_items oi
                               JOIN products p ON oi.product_id = p.id
                               WHERE oi.order_id IN ($inQuery)");
    $itemStmt->execute($orderIds);
    foreach ($itemStmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $orderItems[$item['order_id']][] = $item;
    }
}

// ======== CUSTOM ORDER ========
$customParams = [];
$customSearchClause = '';

if (!empty($customSearch)) {
    $customSearchClause = " AND (u.name LIKE :search OR c.description LIKE :search)";
    $customParams[':search'] = "%$customSearch%";
}

$customWhereClause = $customStatus === 'all' ? '1=1' : 'c.status = :status';
if ($customStatus !== 'all') {
    $customParams[':status'] = $customStatus;
}

$customQuery = "SELECT c.*, u.name AS user_name 
                FROM custom_orders c 
                JOIN users u ON c.user_id = u.id
                WHERE $customWhereClause $customSearchClause
                ORDER BY c.created_at DESC 
                LIMIT :limit OFFSET :offset";
$customStmt = $pdo->prepare($customQuery);
foreach ($customParams as $key => $value) {
    $customStmt->bindValue($key, $value);
}
$customStmt->bindValue(':limit', $custom_limit, PDO::PARAM_INT);
$customStmt->bindValue(':offset', $custom_offset, PDO::PARAM_INT);
$customStmt->execute();
$customOrders = $customStmt->fetchAll(PDO::FETCH_ASSOC);

$customCountQuery = "SELECT COUNT(*) FROM custom_orders c 
                     JOIN users u ON c.user_id = u.id 
                     WHERE $customWhereClause $customSearchClause";
$customCountStmt = $pdo->prepare($customCountQuery);
foreach ($customParams as $key => $value) {
    $customCountStmt->bindValue($key, $value);
}
$customCountStmt->execute();
$total_custom = $customCountStmt->fetchColumn();
$total_custom_pages = ceil($total_custom / $custom_limit);


function getStatusTimestamp($row, $status)
{
    return $status === 'paid' ? $row['paid_at']
        : ($status === 'shipped' ? $row['shipped_at']
            : ($status === 'completed' ? $row['completed_at']
                : $row['created_at']));
}
?>

<div class="content">
    <h2 class="text-center">Data Transaksi</h2>
    <h3 class="text-center">Katalog</h3>

    <form method="GET" class="d-flex align-items-center justify-content-end gap-3 mb-3">
        <label>Filter Katalog:</label>
        <select name="status" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ($validStatuses as $s): ?>
                <option value="<?= $s ?>" <?= $s === $status ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="search" placeholder="Cari nama, user, produk" value="<?= htmlspecialchars($search) ?>" class="form-control w-25">
        <input type="hidden" name="custom_status" value="<?= $customStatus ?>">
        <input type="submit" class="btn btn-primary" value="Cari">
    </form>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Barang</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Waktu</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $trx): ?>
                <tr>
                    <td><?= $trx['id'] ?></td>
                    <td><?= htmlspecialchars($trx['user_name']) ?></td>
                    <td>
                        <?php if (!empty($orderItems[$trx['id']])): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($orderItems[$trx['id']] as $item): ?>
                                    <li class=""><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?> (Rp<?= number_format($item['price'], 0, ',', '.') ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            Tidak ada item
                        <?php endif; ?>
                    </td>
                    <td>Rp <?= number_format($trx['total_price'], 0, ',', '.') ?></td>
                    <td><?= ucfirst($trx['status']) ?></td>
                    <td><?= getStatusTimestamp($trx, $trx['status']) ?></td>
                    <td><a href="order_detail.php?id=<?= $trx['id'] ?>" class="btn btn-sm btn-primary">Lihat</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination katalog -->
    <?php if ($total_trx_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_trx_pages; $i++): ?>
                    <li class="page-item <?= ($i == $trx_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?status=<?= $status ?>&trx_page=<?= $i ?>&custom_status=<?= $customStatus ?>&custom_page=<?= $custom_page ?>&search=<?= urlencode($search) ?>&custom_search=<?= urlencode($customSearch) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <h3 class="text-center mt-5">Custom</h3>
    <form method="GET" class="d-flex align-items-center justify-content-end gap-3 mb-3">
        <label>Filter Custom:</label>
        <select name="custom_status" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ($validStatuses as $s): ?>
                <option value="<?= $s ?>" <?= $s === $customStatus ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="custom_search" placeholder="Cari nama, Deskripsi" value="<?= htmlspecialchars($customSearch) ?>" class="form-control w-25">
        <input type="hidden" name="status" value="<?= $status ?>">
        <input type="submit" class="btn btn-primary" value="Cari">
    </form>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Deskripsi</th>
                <th>Referensi</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customOrders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['user_name']) ?></td>
                    <td><?= htmlspecialchars($order['description']) ?></td>
                    <td>
                        <?php if (!empty($order['reference_image'])): ?>
                            <img src="../uploads/<?= $order['reference_image'] ?>" style="width: 80px;">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>Rp <?= number_format($order['estimated_price'], 0, ',', '.') ?></td>
                    <td><?= ucfirst($order['status']) ?></td>
                    <td><?= getStatusTimestamp($order, $order['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination custom -->
    <?php if ($total_custom_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_custom_pages; $i++): ?>
                    <li class="page-item <?= ($i == $custom_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?status=<?= $status ?>&trx_page=<?= $trx_page ?>&custom_status=<?= $customStatus ?>&custom_page=<?= $i ?>&search=<?= urlencode($search) ?>&custom_search=<?= urlencode($customSearch) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>