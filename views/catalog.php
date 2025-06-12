<?php
require_once './config/database.php';
include 'template/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Hitung total produk
$count_sql = "SELECT COUNT(*) FROM products";
$count_params = [];

if (!empty($search)) {
    $count_sql .= " WHERE name LIKE :search";
    $count_params[':search'] = "%$search%";
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Ambil data produk dengan LIMIT dan OFFSET
$sql = "SELECT products.*, categories.name AS category_name 
        FROM products 
        JOIN categories ON products.category_id = categories.id";

$params = [];

if (!empty($search)) {
    $sql .= " WHERE products.name LIKE :search";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Katalog Produk</h2>
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">Cari</button>
        </form>
    </div>

    <div class="row">
        <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="./uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="text-muted mb-1"><?= htmlspecialchars($product['category_name']) ?></p>
                            <h6 class="text-primary">Rp <?= number_format($product['price'], 0, ',', '.') ?></h6>
                            <div class="d-flex justify-content-between">
                                <a href="detail.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary">Detail</a>
                                <a href="classes/add_to_cart.php?product_id=<?= $product['id'] ?>" class="btn btn-outline-primary mt-auto">
                                    <i class="fas fa-cart-plus"></i> Masukkan Keranjang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-muted">Produk tidak ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Sebelumnya</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Berikutnya</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include 'template/footer.php'; ?>