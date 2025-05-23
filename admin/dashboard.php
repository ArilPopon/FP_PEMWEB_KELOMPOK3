<?php
require_once 'middleware/admin_auth.php';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content">
    <h2>Selamat Datang, Admin</h2>
    <p class="text-muted">Berikut ringkasan aktivitas toko emas:</p>

    <div class="row my-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Produk</h5>
                    <p class="card-text fs-4"><?= 123
                                                ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Pengguna</h5>
                    <p class="card-text fs-4"><?= 50
                                                ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Custom</h5>
                    <p class="card-text fs-4"><?= 50
                                                ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Transaksi</h5>
                    <p class="card-text fs-4"><?= 87
                                                ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row my-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Janji Temu</h5>
                    <p class="card-text fs-4"><?= 12
                                                ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>