<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);
    header("Location: katalog_tambah.php");
    exit;
}
?>

<div class="container mt-5">
    <h3>Tambah Kategori</h3>
    <form method="POST">
        <div class="mb-3">
            <label>Nama Kategori</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="katalog.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>