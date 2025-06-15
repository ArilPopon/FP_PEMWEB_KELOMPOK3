<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Tangani penghapusan
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
  $stmt->execute([$_GET['delete']]);
  header("Location: booking.php");
  exit;
}

// Tangani perubahan status otomatis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['new_status'])) {
  $stmt = $pdo->prepare("UPDATE appointments SET status = :status WHERE id = :id");
  $stmt->execute([
    ':status' => $_POST['new_status'],
    ':id' => $_POST['appointment_id']
  ]);
}

// Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSql = '';
$params = [];

if (!empty($search)) {
  $searchSql = "AND (u.name LIKE :search OR u.email LIKE :search OR a.note LIKE :search)";
  $params[':search'] = '%' . $search . '%';
}

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Hitung total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments a LEFT JOIN users u ON a.user_id = u.id WHERE 1 $searchSql");
$countStmt->execute($params);
$total_rows = $countStmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Ambil data janji temu
$dataSql = "SELECT a.*, u.name, u.email 
            FROM appointments a 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE 1 $searchSql 
            ORDER BY a.appointment_date DESC 
            LIMIT :limit OFFSET :offset";
$dataStmt = $pdo->prepare($dataSql);

foreach ($params as $key => $val) {
  $dataStmt->bindValue($key, $val);
}
$dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->execute();
$appointments = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
  <h2>Daftar Janji Temu</h2>

  <form class="input-group mb-3" method="get" action="booking.php">
    <input type="text" name="search" class="form-control" placeholder="Cari nama, email, keperluan..." value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-outline-secondary" type="submit">Cari</button>
  </form>

  <?php if (count($appointments) > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-dark">
          <tr>
            <th>Nama Pengguna</th>
            <th>Email</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Keperluan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($appointments as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= $row['appointment_date'] ?></td>
              <td><?= $row['appointment_time'] ?></td>
              <td><?= htmlspecialchars($row['note']) ?></td>
              <td>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                  <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $row['status'] === 'confirmed' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Ditolak</option>
                  </select>
                </form>
              </td>
              <td>
                <a href="booking.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus booking ini?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <nav>
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Sebelumnya</a>
            </li>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $total_pages): ?>
            <li class="page-item">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Berikutnya</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>

  <?php else: ?>
    <p class="text-muted">Tidak ada janji temu ditemukan.</p>
  <?php endif; ?>
</div>