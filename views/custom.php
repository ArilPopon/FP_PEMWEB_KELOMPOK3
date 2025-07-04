<?php
session_start();
require_once "config/database.php";
require_once "classes/CustomerOrder.php";
include "template/header.php";

// Pastikan user sudah login
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id']; // Ambil user_id dari session login
$customOrder = new CustomerOrder($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        form {
            max-width: 500px;
            margin: auto;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        select,
        input[type="text"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 8px;
        }

        button {
            margin-top: 15px;
            padding: 10px 15px;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
        <div class="card p-4 shadow w-100" style="max-width: 500px;">

            <form id="customForm" action="" method="POST" enctype="multipart/form-data">
                <label>Jenis Perhiasan:</label>
                <?php
                $query = "SELECT * FROM categories";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                echo '<select name="jenis" required>';
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . "</option>";
                }
                echo '</select>';
                ?>

                <label>Bahan:</label>
                <select name="bahan" required>
                    <option value="emas_kuning">Emas Kuning</option>
                    <option value="emas_putih">Emas Putih</option>
                </select>

                <label>Kadar Emas:</label>
                <?php
                $query = 'SELECT * FROM gold';
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $kadar = $stmt->fetchAll();
                echo '<select name="kadar" required>';
                foreach ($kadar as $item) {
                    echo '<option value="' . htmlspecialchars($item['id']) . '">' . htmlspecialchars($item['type']) . '</option>';
                }
                echo '</select>';
                ?>

                <label>Ukuran:</label>
                <input type="text" name="ukuran" required>

                <label>Tulisan Ukiran (Opsional):</label>
                <textarea name="ukiran"></textarea>

                <label>Upload Gambar Referensi:</label>
                <input type="file" name="gambar" accept="image/*" required>

                <button type="submit" class="btn btn-primary ml-2">Kirim Customisasi</button>
            </form>
        </div>
    </div>

</body>

</html>

<?php include 'views/template/footer.php'; ?>