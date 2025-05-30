<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/CustomerOrder.php';
include __DIR__ . '/template/header.php';

$customOrder = new CustomerOrder($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id     = $_SESSION['user_id'] ?? 1;
    $jenis       = $_POST['jenis'] ?? '';
    $bahan       = $_POST['bahan'] ?? '';
    $kadar       = $_POST['kadar'] ?? '';
    $ukuran      = $_POST['ukuran'] ?? '';
    $ukiran      = $_POST['ukiran'] ?? '';
    $description = "Jenis: $jenis\nBahan: $bahan\nKadar: $kadar\nUkuran: $ukuran\nUkiran: $ukiran";

    $gambar = $_FILES['gambar']['name'];
    $tmp_name = $_FILES['gambar']['tmp_name'];
    $upload_path = __DIR__ . '/../uploads/' . basename($gambar);

    if (move_uploaded_file($tmp_name, $upload_path)) {
        if ($customOrder->create($user_id, $description, $gambar)) {
            echo "<script>alert('Custom order berhasil dikirim!'); window.location.href='custom.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan ke database');</script>";
        }
    } else {
        echo "<script>alert('Gagal upload gambar');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customisasi Perhiasan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 500px; margin: auto; }
        label { display: block; margin-top: 10px; }
        select, input[type="text"], input[type="file"], textarea { width: 100%; padding: 8px; }
        button { margin-top: 15px; padding: 10px 15px; }
    </style>
</head>
<body>

<h2>Customisasi Perhiasan</h2>

<form id="customForm" action="" method="POST" enctype="multipart/form-data">
    <label>Jenis Perhiasan:</label>
    <select name="jenis" required>
        <option value="cincin">Cincin</option>
        <option value="kalung">Kalung</option>
        <option value="gelang">Gelang</option>
    </select>

    <label>Bahan:</label>
    <select name="bahan" required>
        <option value="emas_kuning">Emas Kuning</option>
        <option value="emas_putih">Emas Putih</option>
    </select>

    <label>Kadar Emas:</label>
    <select name="kadar" required>
        <option value="18K">18K</option>
        <option value="22K">22K</option>
    </select>

    <label>Ukuran:</label>
    <input type="text" name="ukuran" required>

    <label>Tulisan Ukiran (Opsional):</label>
    <textarea name="ukiran"></textarea>

    <label>Upload Gambar Referensi:</label>
    <input type="file" name="gambar" accept="image/*" required>

    <button type="submit">Kirim Customisasi</button>
</form>

</body>
</html>

<?php include 'views/template/footer.php'; ?>
