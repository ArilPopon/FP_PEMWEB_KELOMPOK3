<?php
session_start();
require_once "config/database.php";
require_once "classes/CustomerOrder.php";
include "template/header.php";

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$customOrder = new CustomerOrder($pdo);

// Tangani penawaran harga
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nego_price'], $_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE custom_orders SET nego_price = ?, status = 'nego_diajuakan' WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['nego_price'], $_POST['id'], $user_id]);
}

// Setujui harga
if (isset($_POST['setuju']) && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE custom_orders SET status = 'harga_disetujui' WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $user_id]);
    header("Location: pembayaran_custom.php?id=" . $_POST['id']);
    exit;
}

// Tolak harga
if (isset($_POST['tidak_setuju']) && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE custom_orders SET status = 'ditolak_user' WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['id'], $user_id]);
}

$orders = $customOrder->getByUser($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="container mt-5">
    <h3 class="mb-4">Riwayat Customisasi Perhiasan Anda</h3>
    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Deskripsi</th>
                        <th>Gambar</th>
                        <th>Harga dari Admin</th>
                        <th>Penawaran Anda</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= nl2br(htmlspecialchars($order['description'])) ?></td>
                            <td>
                                <?php if ($order['reference_image']): ?>
                                    <img src="uploads/<?= htmlspecialchars($order['reference_image']) ?>" style="width:60px; height:60px; object-fit:cover;">
                                <?php else: ?>
                                    Tidak ada
                                <?php endif; ?>
                            </td>
                            <td><?= $order['estimated_price'] ? 'Rp' . number_format($order['estimated_price'], 0, ',', '.') : '-' ?></td>
                            <td><?= $order['nego_price'] ? 'Rp' . number_format($order['nego_price'], 0, ',', '.') : '-' ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></td>
                            <td>
                                <?php if ($order['estimated_price'] && $order['status'] == 'submitted'): ?>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                        <input type="number" name="nego_price" placeholder="Tawar Harga" required class="form-control form-control-sm">
                                        <button type="submit" class="btn btn-sm btn-warning">Tawar</button>
                                    </form>
                                    <form method="POST" class="mt-1 d-flex gap-2">
                                        <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                        <button type="submit" name="setuju" class="btn btn-sm btn-success">Setuju</button>
                                        <button type="submit" name="tidak_setuju" class="btn btn-sm btn-danger">Tolak</button>
                                    </form>
                                <?php elseif ($order['status'] == 'harga_disetujui'): ?>
                                    <span class="text-success">Menunggu pembayaran</span>
                                <?php elseif ($order['status'] == 'in_progress' || $order['status'] == 'completed'): ?>
                                    <span class="text-primary">Sedang diproses</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Tidak ada custom order ditemukan.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php include "template/footer.php"; ?>