<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: katalog.php');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data produk
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='alert alert-danger'>Produk tidak ditemukan.</div>";
    exit;
}

// Ambil kategori
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $weight = (float) $_POST['weight'];
    $material = trim($_POST['material']);
    $stock = (int) $_POST['stock'];
    $price = (int) $_POST['price'];
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];

    $imageName = $product['image'];
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/$imageName");
    }

    $stmt = $pdo->prepare("UPDATE products SET name = ?, weight = ?, material = ?, stock = ?, price = ?, description = ?, image = ?, category_id = ? WHERE id = ?");
    if ($stmt->execute([$name, $weight, $material, $stock, $price, $description, $imageName, $category_id, $id])) {
        header("Location: katalog.php");
        exit;
    } else {
        $error = "Gagal mengupdate produk.";
    }
}
?>

<div class="content">
    <h2>Edit Produk</h2>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-1">
            <label>Nama Produk</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div class="mb-1">
            <label>Berat (gram)</label>
            <input type="number" step="0.01" name="weight" class="form-control" value="<?= $product['weight'] ?>" required>
        </div>
        <div class="mb-1">
            <label>Material</label>
            <input type="text" name="material" class="form-control" value="<?= htmlspecialchars($product['material']) ?>" required>
        </div>
        <div class="mb-1">
            <label>Stok</label>
            <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
        </div>
        <div class="mb-1">
            <label>Harga (Rp)</label>
            <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required>
        </div>
        <div class="mb-1">
            <label>Kategori</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-1">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-2">
            <label>Gambar</label><br>
            <?php if ($product['image']): ?>
                <img src="../uploads/<?= $product['image'] ?>" alt="Preview" width="150" class="mb-2"><br>
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="katalog.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

</body>

</html>