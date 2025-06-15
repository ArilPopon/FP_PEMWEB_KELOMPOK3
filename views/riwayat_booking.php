<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Konfigurasi pagination
$limit = 10;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $limit;

// Pencarian berdasarkan keperluan
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSql = '';
$params = [':user_id' => $user['id']];

if ($search !== '') {
    $searchSql = "AND note LIKE :search";
    $params[':search'] = "%$search%";
}

// Hitung total data
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = :user_id $searchSql");
$countStmt->execute($params);
$totalAppointments = $countStmt->fetchColumn();
$totalPages = ceil($totalAppointments / $limit);

// Ambil data janji temu sesuai limit
$dataSql = "SELECT * FROM appointments WHERE user_id = :user_id $searchSql ORDER BY appointment_date DESC, appointment_time DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($dataSql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
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
            overflow-y: auto;
            background-color: #343a40;
            color: #fff;
            padding-top: 30px;
            position: sticky;
            top: 0;
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
            padding: 0, 0, 0, 30px;
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

                    <!-- Form Pencarian -->
                    <form method="get" class="d-flex justify-content-end mb-3">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control me-2" placeholder="Cari keperluan...">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </form>

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

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
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