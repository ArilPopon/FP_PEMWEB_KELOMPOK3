<?php
session_start();
require_once './config/database.php';
require_once './classes/Cart.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$cart = new Cart($pdo, $user_id);
$items = $cart->getItems();

$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $kontak = $_POST['kontak'];
    $bukti = $_FILES['bukti'];

    // Upload bukti transfer
    $upload_dir = 'uploads/bukti/';
    $bukti_name = time() . '_' . basename($bukti['name']);
    $upload_path = $upload_dir . $bukti_name;
    move_uploaded_file($bukti['tmp_name'], $upload_path);

    // Masukkan semua item ke transaksi (bisa dimodifikasi jika multi-record)
    foreach ($items as $item) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, product_id, quantity, total_price, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([
            $user_id,
            $item['product_id'],
            $item['quantity'],
            $item['quantity'] * $item['price']
        ]);
    }

    // Bersihkan keranjang
    $cart->clearCart();

    // Redirect ke halaman sukses
    header("Location: transaksi_sukses.php");
    exit;
}
?>

<?php include 'template/header.php'; ?>

<div class="container py-5">
    <h2>Checkout Manual</h2>
    <p>Isi form berikut dan unggah bukti transfer untuk menyelesaikan transaksi.</p>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Alamat Pengiriman</label>
            <textarea name="alamat" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label>No. Kontak</label>
            <input type="text" name="kontak" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Bukti Transfer</label>
            <input type="file" name="bukti" class="form-control" accept="image/*" required>
        </div>
        <div class="mb-3">
            <strong>Total: Rp <?= number_format($total, 0, ',', '.') ?></strong>
        </div>
        <button type="submit" class="btn btn-success">Kirim dan Proses</button>
    </form>
</div>

<?php include 'template/footer.php'; ?>