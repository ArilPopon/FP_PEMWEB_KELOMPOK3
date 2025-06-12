<?php
// Mulai session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Toko mas Erison Siregar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm("Apakah Anda yakin ingin logout?")) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">Toko mas Erison Siregar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'catalog.php') ? 'active' : '' ?>" href="catalog.php">Katalog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'custom.php') ? 'active' : '' ?>" href="custom.php">Custom</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'gold.php') ? 'active' : '' ?>" href="gold.php">Jual/Beli Emas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'booking.php') ? 'active' : '' ?>" href="booking.php">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'active' : '' ?>" href="cart.php">Keranjang</a>
                    </li>

                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                        <li class="nav-item">
                            <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['dashboard.php', 'riwayat.php', 'pengiriman.php', 'profil.php', 'detail_transaksi.php']) ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
                        </li>

                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php" onclick="confirmLogout(event)">Logout</a>

                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : '' ?>" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>



    <main class="container py-4">