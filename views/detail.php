<?php
require_once './config/database.php';
include 'template/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT products.*, categories.name AS category_name 
        FROM products 
        JOIN categories ON products.category_id = categories.id
        WHERE products.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container py-5'><p class='text-danger'>Produk tidak ditemukan.</p></div>";
    include 'template/footer.php';
    exit;
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Gambar Produk -->
        <div class="col-md-6">
            <div class="border p-3 bg-white">
                <img src="./uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid w-100" style="object-fit: cover; max-height: 500px;">
                <div class="mt-3 text-center">
                    <img src="./uploads/<?= htmlspecialchars($product['image']) ?>" class="img-thumbnail me-2" style="width: 80px; height: 80px; object-fit: cover;">
                    <!-- Tambahkan gambar tambahan jika ada -->
                </div>
            </div>
        </div>

        <!-- Informasi Produk -->
        <div class="col-md-6">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p class="text-muted"><?= htmlspecialchars($product['category_name']) ?></p>
            <h4 class="text-primary">Rp <?= number_format($product['price'], 0, ',', '.') ?></h4>

            <hr>

            <ul class="list-unstyled mb-4">
                <li><strong>Kondisi:</strong> Bekas</li>
                <li><strong>Fungsi:</strong> 100% berfungsi</li>
                <li><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($product['description'] ?? 'Tidak ada deskripsi.')) ?></li>
            </ul>

            <a href="classes/add_to_cart.php?product_id=<?= $product['id'] ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-cart-plus"></i> Masukkan Keranjang
            </a>
        </div>
    </div>
</div>

<?php include 'template/footer.php'; ?>
