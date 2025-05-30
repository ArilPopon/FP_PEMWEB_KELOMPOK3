<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Cart.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$product_id = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

if ($product_id > 0) {
    $cart = new Cart($pdo, $user_id);
    $cart->addProduct($product_id);
}

// Redirect kembali ke halaman sebelumnya
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
