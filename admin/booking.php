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
?>

<div class="container mt-5">
  <h2>Daftar Janji Temu</h2>
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
      <?php
      $stmt = $pdo->query("SELECT a.*, u.name, u.email 
                           FROM appointments a 
                           LEFT JOIN users u ON a.user_id = u.id 
                           ORDER BY a.appointment_date DESC");

      foreach ($stmt as $row):
      ?>
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