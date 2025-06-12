<?php
session_start();
require_once '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    die("Anda harus login terlebih dahulu.");
}

$user_id = $_SESSION['user']['id'];

// Tangani penghapusan janji temu
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete'], $user_id]);
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Tangani penambahan janji temu (via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $note = $_POST['note'] ?? '';

    if (!$appointment_date || !$appointment_time) {
        die("Tanggal dan waktu janji temu wajib diisi.");
    }

    $sql = "INSERT INTO appointments (user_id, appointment_date, appointment_time, note, status) 
            VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $appointment_date, $appointment_time, $note]);

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
