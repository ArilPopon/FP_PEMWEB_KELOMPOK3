<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total belanja hanya dari transaksi yang selesai
$totalCompleted = array_filter($transactions, fn($t) => $t['status'] === 'completed');
$totalBelanja = array_sum(array_column($totalCompleted, 'total_price'));
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
            padding: 0 0 30px 0;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .table th,
        .table td {
            vertical-align: middle;
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

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card text-bg-primary text-white h-100">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h5 class="card-title">Total Transaksi</h5>
                                    <p class="card-text fs-4 mb-0"><?= count($transactions) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card text-bg-success text-white h-100">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h5 class="card-title">Berhasil (Completed)</h5>
                                    <p class="card-text fs-4 mb-0"><?= count($totalCompleted) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card text-bg-danger text-white h-100">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h5 class="card-title">Dibatalkan</h5>
                                    <p class="card-text fs-4 mb-0"><?= count(array_filter($transactions, fn($t) => $t['status'] === 'cancelled')) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card text-bg-warning text-dark h-100">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h5 class="card-title">Total Belanja</h5>
                                    <p class="card-text fs-4 mb-0">Rp <?= number_format($totalBelanja, 0, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php
    function getStatusColor($status)
    {
        return match ($status) {
            'pending' => 'secondary',
            'paid' => 'info',
            'shipped' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'dark',
        };
    }
    ?>
</body>

</html>

<?php include 'template/footer.php'; ?>