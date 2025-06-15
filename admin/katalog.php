<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Tangani pencarian
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$params = [];
$count_sql = "SELECT COUNT(*) FROM products";
$sql = "SELECT * FROM products";
$where = "";

if (!empty($keyword)) {
    $where = " WHERE name LIKE :keyword OR description LIKE :keyword";
    $params[':keyword'] = '%' . $keyword . '%';
}

$count_stmt = $pdo->prepare($count_sql . $where);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

$sql .= $where . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind parameter manual (karena :limit dan :offset harus integer)
if (!empty($keyword)) {
    $stmt->bindValue(':keyword', $params[':keyword'], PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Katalog Perhiasan</h2>
        <div>
            <a href="kategori_tambah.php" class="btn btn-primary">+ Tambah Kategori</a>
            <a href="katalog_tambah.php" class="btn btn-primary">+ Tambah Produk</a>
        </div>
    </div>

    <form class="input-group mb-4" method="get" action="katalog.php">
        <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="<?= htmlspecialchars($keyword) ?>">
        <button class="btn btn-outline-secondary" type="submit">Cari</button>
    </form>

    <?php if (count($products) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Deskripsi</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="Gambar" style="width: 60px; height: 60px; object-fit:cover;"></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</td>
                            <td><?= date('d-m-Y', strtotime($product['created_at'])) ?></td>
                            <td>
                                <a href="katalog_edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="katalog_hapus.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
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
        <p class="text-muted">Produk tidak ditemukan.</p>
    <?php endif; ?>
</div>

</body>

</html>