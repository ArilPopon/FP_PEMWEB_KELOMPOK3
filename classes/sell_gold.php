<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil user_id yang benar (integer)
    $user_id = is_array($_SESSION['user']) ? $_SESSION['user']['id'] : $_SESSION['user'];

    $weight = floatval($_POST['weight']);
    $price_per_gram = floatval($_POST['price_per_gram']);
    $total_price = $weight * $price_per_gram;

    // Proses upload file
    $photo_filename = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $photo_tmp = $_FILES['photo']['tmp_name'];
        $original_name = basename($_FILES['photo']['name']);
        $photo_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
        $destination = $upload_dir . $photo_filename;

        if (!move_uploaded_file($photo_tmp, $destination)) {
            die("Gagal mengunggah foto emas.");
        }
    } else {
        die("Foto emas wajib diunggah.");
    }

    // Simpan ke database
    $sql = "INSERT INTO gold_transactions (user_id, type, weight, price_per_gram, total_price, photo, status)
            VALUES (:user_id, 'sell', :weight, :price_per_gram, :total_price, :photo, 'pending')";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':user_id' => $user_id,
        ':weight' => $weight,
        ':price_per_gram' => $price_per_gram,
        ':total_price' => $total_price,
        ':photo' => $photo_filename
    ]);

    header('Location: ../gold.php?sukses=1');
    exit;
} else {
    header('Location: ../gold.php');
    exit;
}
