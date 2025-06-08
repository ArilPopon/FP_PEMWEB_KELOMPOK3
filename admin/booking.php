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

?>

<div class="container mt-5">
  <h2>Daftar Temu Janji</h2>
  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
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

      foreach ($stmt as $row) {
        echo "<tr>";
        echo "<td>" . $row['appointment_date'] . "</td>";
        echo "<td>" . $row['appointment_time'] . "</td>";
        echo "<td>" . htmlspecialchars($row['note']) . "</td>";
        echo "<td>" . ucfirst($row['status']) . "</td>";
        echo "<td>
          <a href='booking.php?delete={$row['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus booking ini?')\">Hapus</a>
        </td>";
        echo "</tr>";
      }
      ?>
    </tbody>
  </table>
</div>
