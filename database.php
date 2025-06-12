<?php
// Konfigurasi database
$host     = 'localhost';
$dbname   = 'emas';
$username = 'root';
$password = '';

// Koneksi menggunakan PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Set mode error untuk menampilkan exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Gagal koneksi
    die("Koneksi database gagal: " . $e->getMessage());
}