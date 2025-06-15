<?php
// Mulai session jika diperlukan
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Customisasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        .data {
            margin-top: 20px;
        }
        .data p {
            margin: 8px 0;
        }
        .image-preview {
            margin-top: 15px;
        }
        .image-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .back-link {
            margin-top: 20px;
            display: block;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Data Customisasi Anda</h2>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $jenis = $_POST['jenis'] ?? '-';
        $bahan = $_POST['bahan'] ?? '-';
        $kadar = $_POST['kadar'] ?? '-';
        $ukuran = $_POST['ukuran'] ?? '-';
        $ukiran = $_POST['ukiran'] ?? '-';

        // Menangani upload gambar
        $gambar_path = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
            $nama_file = basename($_FILES['gambar']['name']);
            $tmp_name = $_FILES['gambar']['tmp_name'];
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $gambar_path = $upload_dir . $nama_file;
            move_uploaded_file($tmp_name, $gambar_path);
        }
    ?>

    <div class="data">
        <p><strong>Jenis Perhiasan:</strong> <?= htmlspecialchars($jenis) ?></p>
        <p><strong>Bahan:</strong> <?= htmlspecialchars($bahan) ?></p>
        <p><strong>Kadar Emas:</strong> <?= htmlspecialchars($kadar) ?></p>
        <p><strong>Ukuran:</strong> <?= htmlspecialchars($ukuran) ?></p>
        <p><strong>Tulisan Ukiran:</strong> <?= nl2br(htmlspecialchars($ukiran)) ?></p>

        <?php if ($gambar_path): ?>
            <div class="image-preview">
                <p><strong>Gambar Desain/Referensi:</strong></p>
                <img src="<?= $gambar_path ?>" alt="Gambar Desain">
            </div>
        <?php else: ?>
            <p><strong>Gambar:</strong> Tidak ada gambar yang diupload.</p>
        <?php endif; ?>
    </div>

    <a class="back-link" href="customisasi.php">← Kembali ke Form</a>

    <?php
    } else {
        echo "<p>Form belum disubmit dengan benar.</p>";
    }
    ?>
</div>

</body>
</html>