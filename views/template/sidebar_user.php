<div class="text-center my-4">
    <h5>Dashboard</h5>
</div>
<a href="dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : '' ?>">Ringkasan</a>
<a href="riwayat.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'riwayat.php') ? 'active' : '' ?>">Riwayat Transaksi</a>
<a href="riwayat_booking.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'riwayat_booking.php') ? 'active' : '' ?>">Riwayat Booking</a>
<a href="dashboard_custom.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'dashboard_custom.php') ? 'active' : '' ?>">Custom</a>
<a href="pengiriman.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'pengiriman.php') ? 'active' : '' ?>">Pengiriman</a>
<a href="profil.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'profil.php') ? 'active' : '' ?>">Profil</a>