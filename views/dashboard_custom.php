<?php
session_start();
require_once './config/database.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$message = '';
$error = '';

// Proses upload bukti bayar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_proof'])) {
    $orderId = $_POST['order_id'];
    $uploadDir = 'uploads/';
    $fileName = time() . '_' . basename($_FILES['payment_proof']['name']);
    $targetFile = $uploadDir . $fileName;

    $stmtCheck = $pdo->prepare("SELECT * FROM custom_orders WHERE id = ? AND user_id = ?");
    $stmtCheck->execute([$orderId, $user['id']]);
    $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($order && !empty($order['estimated_price']) && empty($order['payment_proof'])) {
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetFile)) {
            $stmt = $pdo->prepare("UPDATE custom_orders SET payment_proof = ?, status = 'submitted' WHERE id = ?");
            $stmt->execute([$fileName, $orderId]);
            $message = "Bukti pembayaran berhasil diunggah.";
        } else {
            $error = "Gagal mengunggah file.";
        }
    } else {
        $error = "Gagal: belum ada harga atau bukti sudah dikirim.";
    }
}

// Proses konfirmasi barang diterima
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_received'])) {
    $orderId = $_POST['confirm_received'];
    $stmt = $pdo->prepare("UPDATE custom_orders SET status = 'arrived', arrived_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $user['id']]);
    $message = "Terima kasih! Barang telah diterima.";
}

// Pencarian dan pagination
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$params = [$user['id']];
$whereClause = "user_id = ?";
if (!empty($search)) {
    $whereClause .= " AND description LIKE ?";
    $params[] = '%' . $search . '%';
}

// Ambil total data
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM custom_orders WHERE $whereClause");
$stmtCount->execute($params);
$totalOrders = $stmtCount->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// Ambil data per halaman
$stmt = $pdo->prepare("SELECT * FROM custom_orders WHERE $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Custom Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f6fa;
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

        .img-thumb {
            width: 80px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 p-0 sidebar">
                <?php include 'template/sidebar_user.php'; ?>
            </div>
            <div class="col-md-9 content">
                <div class="section-card">
                    <h4>Custom Order</h4>

                    <form method="get" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control me-2" placeholder="Cari deskripsi..."
                                value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php elseif (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (empty($orders)): ?>
                        <p>Belum ada custom order.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th>Gambar Referensi</th>
                                        <th>Status</th>
                                        <th>Harga</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= nl2br(htmlspecialchars($order['description'])) ?></td>
                                            <td>
                                                <?php if ($order['reference_image']): ?>
                                                    <img src="uploads/<?= htmlspecialchars($order['reference_image']) ?>" class="img-thumb">
                                                <?php else: ?>
                                                    Tidak ada
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-<?= getStatusColor($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                                            <td>
                                                <?= $order['estimated_price'] ? 'Rp ' . number_format($order['estimated_price'], 0, ',', '.') : '<span class="text-muted">Belum ditentukan</span>' ?>
                                            </td>
                                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <?php if (!empty($order['estimated_price']) && empty($order['payment_proof'])): ?>
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalBayar<?= $order['id'] ?>">Bayar</button>
                                                <?php elseif ($order['status'] === 'shipped'): ?>
                                                    <form method="post">
                                                        <input type="hidden" name="confirm_received" value="<?= $order['id'] ?>">
                                                        <button class="btn btn-sm btn-success">Konfirmasi Diterima</button>
                                                    </form>
                                                <?php elseif (!empty($order['payment_proof'])): ?>
                                                    <span class="text-success">Sudah dibayar</span>
                                                <?php else: ?>
                                                    <span class="text-muted">Menunggu estimasi harga</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Modal bayar -->
                                        <div class="modal fade" id="modalBayar<?= $order['id'] ?>" tabindex="-1" aria-labelledby="modalBayarLabel<?= $order['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="post" enctype="multipart/form-data">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalBayarLabel<?= $order['id'] ?>">Upload Bukti Pembayaran</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Kirim ke BRI<br>Nomor: 320280408301830<br>Atas Nama: TOKO MAS ERISON SIREGAR</label>
                                                                <input type="file" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">Kirim</button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    include 'template/footer.php';
    function getStatusColor($status)
    {
        return match ($status) {
            'submitted' => 'secondary',
            'in_progress' => 'warning',
            'completed' => 'success',
            'waiting_payment_confirmation' => 'info',
            'shipped' => 'info',
            'arrived' => 'success',
            'cancelled' => 'danger',
            default => 'dark',
        };
    }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>