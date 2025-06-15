<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Ambil data transaksi
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total belanja hanya dari transaksi yang selesai
$totalCompleted = array_filter($transactions, fn($t) => $t['status'] === 'completed');
$totalBelanja = array_sum(array_column($totalCompleted, 'total_price'));

// Ambil total booking (janji temu)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalBookings = $stmt->fetchColumn();

// Ambil total permintaan custom
$stmt = $pdo->prepare("SELECT COUNT(*) FROM custom_orders WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalCustoms = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f6fa;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #fff;
            padding-top: 30px;
        }

        .sidebar a {
            color: #ccc;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }

        .content {
            padding: 0, 30px, 0, 30px;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .card-text {
            font-size: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 p-0 sidebar">
                <?php include 'template/sidebar_user.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 content">
                <!-- Ringkasan -->
                <div class="section-card mb-4">
                    <h4>Ringkasan Akun</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nama:</strong> <?= htmlspecialchars($user['name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <p><strong>No Telepon:</strong> <?= htmlspecialchars($user['phone'] ?? '-') ?></p>
                            <p><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($user['address'] ?? '-')) ?></p>
                        </div>
                    </div>

                    <!-- Grid Cards -->
                    <div class="row mt-4 g-3">
                        <div class="col-md-4">
                            <div class="card text-bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Transaksi</h5>
                                    <p class="card-text"><?= count($transactions) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card text-bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Berhasil (Completed)</h5>
                                    <p class="card-text"><?= count($totalCompleted) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card text-bg-danger text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Dibatalkan</h5>
                                    <p class="card-text"><?= count(array_filter($transactions, fn($t) => $t['status'] === 'cancelled')) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Janji Temu</h5>
                                    <p class="card-text"><?= $totalBookings ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-bg-secondary text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Permintaan Custom</h5>
                                    <p class="card-text"><?= $totalCustoms ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Belanja</h5>
                                    <p class="card-text">Rp <?= number_format($totalBelanja, 0, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Grid Cards -->
                </div>
            </div>
        </div>
    </div>

    <?php include 'template/footer.php'; ?>