<?php
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/CustomerOrder.php';

$customOrder = new CustomerOrder($pdo);

// Tangani pembaruan harga
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'], $_POST['harga'], $_POST['update_harga'])) {
        $customOrder->updatePrice($_POST['id'], $_POST['harga']);
        header("Location: custom.php");
        exit;
    }
}

// update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action === 'selesaikan') {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE custom_orders SET status = 'completed', completed_at = ? WHERE id = ?");
        $stmt->execute([$now, $id]);
    }
    header("Location: custom.php");
    exit;
}


// Tangani hapus
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $customOrder->delete($_GET['delete']);
    header("Location: custom.php");
    exit;
}

// Pagination
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_records = $customOrder->countAll($keyword);
$total_pages = ceil($total_records / $limit);


$orders = $customOrder->getAll($keyword, $limit, $offset);


// Ambil data sesuai halaman
$orders = $customOrder->getAll($keyword, $limit, $offset);
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Customisasi Perhiasan</h2>
    </div>

    <form class="input-group mb-4" method="get" action="custom.php">
        <input type="text" name="search" class="form-control" placeholder="Cari deskripsi atau status..." value="<?= htmlspecialchars($keyword) ?>">
        <button class="btn btn-outline-secondary" type="submit">Cari</button>
    </form>

    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Deskripsi</th>
                        <th>Gambar Referensi</th>
                        <th>Status</th>
                        <th>Harga</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['user_id'] ?></td>
                            <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                            <td>
                                <?php if (!empty($row['reference_image'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($row['reference_image']) ?>" alt="gambar" style="width: 60px; height: 60px; object-fit:cover;">
                                <?php else: ?>
                                    Tidak ada
                                <?php endif; ?>
                            </td>
                            <td><?= ucfirst(str_replace('_', ' ', $row['status'])) ?></td>

                            <td>
                                <form method="POST" class="d-flex" style="gap: 4px;">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="text" name="harga" value="<?= $row['estimated_price'] ?>" class="form-control form-control-sm" placeholder="Harga" min="0">
                                    <button type="submit" name="update_harga" class="btn btn-sm btn-success">Simpan</button>
                                </form>
                            </td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <?php if ($row['status'] === 'in_progress'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="action" value="selesaikan" class="btn btn-sm btn-success">Selesai</button>
                                    </form>
                                <?php else: ?>
                                    <a href="custom.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus custom order ini?')">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?search=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">Sebelumnya</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?search=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?search=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">Berikutnya</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-muted">Tidak ada data ditemukan.</p>
    <?php endif; ?>
</div>

</body>

</html>