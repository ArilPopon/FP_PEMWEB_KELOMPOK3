<script>
    function confirmLogout(event) {
        event.preventDefault();
        if (confirm("Apakah Anda yakin ingin logout?")) {
            window.location.href = 'logout.php';
        }
    }
</script>

<div class="sidebar d-flex flex-column p-3">
    <h4 class="text-center mt-3 mb-4">Admin Panel</h4>
    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a href="booking.php" class="<?= basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : '' ?>"><i class="fas fa-home me-2"></i> Janji Temu</a>
    <a href="katalog.php" class="<?= basename($_SERVER['PHP_SELF']) == 'katalog.php' ? 'active' : '' ?>"><i class="fas fa-gem me-2"></i> Katalog Perhiasan</a>
    <a href="custom.php" class="<?= basename($_SERVER['PHP_SELF']) == 'custom.php' ? 'active' : '' ?>"><i class="fas fa-gear me-2"></i> Custom Perhiasan</a>
    <a href="trade.php" class="<?= basename($_SERVER['PHP_SELF']) == 'trade.php' ? 'active' : '' ?>"><i class="fas fa-exchange me-2"></i> Jual/Beli Emas</a>
    <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>"><i class="fas fa-users me-2"></i> Manajemen Pengguna</a>
    <a href="transactions.php" class="<?= basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : '' ?>"><i class="fas fa-exchange-alt me-2"></i> Transaksi</a>
    <a href="konfirmasi_pembayaran.php" class="<?= basename($_SERVER['PHP_SELF']) == 'konfirmasi_pembayaran.php' ? 'active' : '' ?>"><i class="fa-solid fa-file-circle-check me-2"></i> Konfirmasi Pembayaran</a>
    <a href="logout.php" onclick="confirmLogout(event)"> <i class=" fas fa-sign-out-alt me-2"></i> Keluar</a>
</div>