<?php
session_start();

// Fungsi untuk menambahkan transaksi
function addTransaction($type, $amount, $price) {
    $transaction = [
        'type' => $type,
        'amount' => $amount,
        'price' => $price,
        'total' => $amount * $price,
        'date' => date('Y-m-d H:i:s')
    ];
    
    if (!isset($_SESSION['transactions'])) {
        $_SESSION['transactions'] = [];
    }
    
    $_SESSION['transactions'][] = $transaction;
}

// Fungsi untuk mendapatkan semua transaksi
function getTransactions() {
    return isset($_SESSION['transactions']) ? $_SESSION['transactions'] : [];
}
?>
