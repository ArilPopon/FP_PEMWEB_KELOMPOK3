<?php
session_start();
require_once '../config/database.php';
require_once 'Cart.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

if ($productId > 0) {
    $cart = new Cart($pdo, $userId);
    $cart->removeItem($productId);
}

// Redirect kembali ke halaman keranjang
header("Location: ../cart.php");
exit;
