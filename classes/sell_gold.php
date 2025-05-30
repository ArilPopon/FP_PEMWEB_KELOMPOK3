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

    $sql = "INSERT INTO gold_transactions (user_id, type, weight, price_per_gram, total_price, status)
            VALUES (:user_id, 'sell', :weight, :price_per_gram, :total_price, 'pending')";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':user_id' => $user_id,
        ':weight' => $weight,
        ':price_per_gram' => $price_per_gram,
        ':total_price' => $total_price
    ]);

    header('Location: ../gold.php?sukses=1');
    exit;
} else {
    header('Location: ../gold.php');
    exit;
}
