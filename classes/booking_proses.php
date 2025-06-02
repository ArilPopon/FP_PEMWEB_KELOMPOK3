<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    die("Anda harus login terlebih dahulu.");
}

$user_id = $_SESSION['user_id'] ?? 1;
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];
$note = $_POST['note'];

// Masukkan ke tabel appointments
$sql = "INSERT INTO appointments (user_id, appointment_date, appointment_time, note, status) 
        VALUES (?, ?, ?, ?, 'pending')";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $appointment_date, $appointment_time, $note]);

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

// Tangani penghapusan
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}