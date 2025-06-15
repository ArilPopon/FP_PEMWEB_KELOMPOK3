<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC, appointment_time DESC");
$stmt->execute([$user['id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', sans-serif;
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
            padding: 30px;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .table td,
        .table th {
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
                <div class="section-card">
                    <h4>Riwayat Janji Temu</h4>

                    <?php if (count($appointments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Keperluan</th>
                                        <th>Status</th>
                                        <th>Waktu Buat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appt): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($appt['appointment_date']) ?></td>
                                            <td><?= htmlspecialchars($appt['appointment_time']) ?></td>
                                            <td><?= htmlspecialchars($appt['note']) ?></td>
                                            <td>
                                                <?php
                                                switch ($appt['status']) {
                                                    case 'pending':
                                                        echo '<span class="badge bg-warning text-dark">Menunggu</span>';
                                                        break;
                                                    case 'confirmed':
                                                        echo '<span class="badge bg-success">Disetujui</span>';
                                                        break;
                                                    case 'cancelled':
                                                        echo '<span class="badge bg-danger">Ditolak</span>';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td><?= date('d M Y H:i', strtotime($appt['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada janji temu yang tercatat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'template/footer.php'; ?>
</body>
</html>
