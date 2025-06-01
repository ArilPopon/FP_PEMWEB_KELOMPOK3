<?php
require_once 'config/database.php';
include 'views/template/header.php';

// Ambil 4 produk terbaru
$stmt = $pdo->prepare("SELECT id, name, price, image FROM products ORDER BY created_at DESC LIMIT 4");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- HERO BANNER -->
<section class="text-white text-center d-flex align-items-center" style="
    background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
    url('assets/images/banner2.jpg') no-repeat center center;
    background-size: cover;
    height: 500px;
"
>
    <div class="container">
        <h1 class="display-4 fw-bold">Temukan Keindahan & Investasi Emas di Satu Tempat</h1>
        <p class="lead">Perhiasan elegan, layanan custom eksklusif, dan jual beli emas terpercaya.</p>
        <p class="lead">Toko Emas Erison Siregar.</p>
        <a href="catalog.php" class="btn btn-lg btn-primary me-2">Lihat Katalog</a>
        <a href="gold.php" class="btn btn-lg btn-outline-light">Jual Beli Emas</a>
    </div>
</section>

<!-- PERHIASAN UNGGULAN -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Perhiasan Unggulan</h2>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="./uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" style="height:200px; object-fit:cover;" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                            <a href="#" class="btn btn-sm btn-outline-primary">Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="catalog.php" class="btn btn-primary mt-3">Lihat Semua</a>
    </div>
</section>

<!-- CUSTOM PERHIASAN -->
<section class="py-5 text-white" style="background-color: #d4af37;">
    <div class="container text-center">
        <h2 class="mb-4">Custom Perhiasan Eksklusif</h2>
        <p class="mb-4 fs-5">Buat perhiasan sesuai gaya dan cerita pribadi Anda. Kami bantu wujudkan keinginan Anda dengan sentuhan ahli.</p>
        <a href="/custom.php" class="btn btn-light btn-lg">Konsultasi Custom</a>
    </div>
</section>

<!-- JUAL BELI EMAS -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Jual Beli Emas</h2>
        <p class="mb-4 fs-5">Nikmati kemudahan jual beli emas batangan atau perhiasan dengan harga terkini dan transparan.</p>
        <a href="/gold.php" class="btn btn-primary btn-lg">Transaksi Sekarang</a>
    </div>
</section>

<!-- BOOKING / JANJI TEMU -->
<section class="py-5" style="background-color: #f8f9fa;">
    <div class="container text-center">
        <h2 class="mb-4">Buat Janji Temu</h2>
        <p class="mb-3 fs-5">Ingin konsultasi langsung atau melihat koleksi kami? Silakan buat janji temu secara online!</p>
        <a href="/booking.php" class="btn btn-outline-primary btn-lg">Buat Janji Sekarang</a>
    </div>
</section>

<?php include 'views/template/footer.php'; ?>