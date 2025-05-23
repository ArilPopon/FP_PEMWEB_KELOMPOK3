<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Tangani pencarian
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM products";
$params = [];

if (!empty($keyword)) {
    $sql .= " WHERE name LIKE :keyword OR description LIKE :keyword";
    $params[':keyword'] = '%' . $keyword . '%';
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
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
    <?php else: ?>
        <p class="text-muted">Produk tidak ditemukan.</p>
    <?php endif; ?>
</div>

</body>

</html>