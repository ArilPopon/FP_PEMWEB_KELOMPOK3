<?php
session_start();
include 'template/header.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>

<div class="container mt-5">
  <h2>Form Booking Pertemuan</h2>
  <form action="classes/booking_proses.php" method="POST">
    <div class="mb-3">
      <label for="tanggal" class="form-label">Tanggal Pertemuan</label>
      <input type="date" class="form-control" id="tanggal" name="appointment_date" required>
    </div>
    <div class="mb-3">
      <label for="waktu" class="form-label">Waktu Pertemuan</label>
      <input type="time" class="form-control" id="waktu" name="appointment_time" required>
    </div>
    <div class="mb-3">
      <label for="note" class="form-label">Keperluan</label>
      <textarea class="form-control" id="note" name="note" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
  </form>
</div>

<?php include 'template/footer.php'; ?>
