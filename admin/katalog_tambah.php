<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Ambil data kategori
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $weight = $_POST['weight'];
    $material = $_POST['material'];
    $stock = $_POST['stock'];
    $price = (int) $_POST['price'];
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];

    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = time() . '_' . $_FILES['image']['name'];
        $imageTmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($imageTmp, "../uploads/$imageName");

        $stmt = $pdo->prepare("INSERT INTO products (name, weight, material, stock, price, description, image, category_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$name,  $weight, $material, $stock, $price, $description, $imageName, $category_id])) {
            // $success = "Produk berhasil ditambahkan.";
            header('Location: katalog.php');
        } else {
            $error = "Gagal menambahkan produk.";
        }
    } else {
        $error = "Gambar wajib diunggah.";
    }
}
?>

<div class="content">
    <h2>Tambah Produk</h2>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-1">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-1">
            <label>Berat (gram)</label>
            <input type="number" step="0.01" name="weight" class="form-control" required>
        </div>
        <div class="mb-1">
            <label>Material</label>
            <input type="text" name="material" class="form-control" required>
        </div>
        <div class="mb-1">
            <label>Stok</label>
            <input type="number" name="stock" class="form-control" required>
        </div>

        <div class="mb-1">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" name="price" class="form-control" required>
        </div>
        <div class="mb-1">
            <label>Kategori</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-1">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" required></textarea>
        </div>
        <div class="mb-2">
            <label class="form-label">Gambar</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
        </div>
        <div class="">
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="katalog.php" class="btn btn-secondary">Kembali</a>
        </div>

    </form>
</div>

</body>

</html>