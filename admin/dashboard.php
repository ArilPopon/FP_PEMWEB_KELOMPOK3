<?php
require_once 'middleware/admin_auth.php';
include 'includes/header.php';
include 'includes/sidebar.php';
require_once './../config/database.php';

function total_products($pdo)
{
    $query = "SELECT COUNT(*) FROM products";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $total_products = $stmt->fetchColumn();
    return $total_products;
};

function pengguna($pdo)
{
    $query = 'SELECT COUNT(*) FROM users WHERE role LIKE "customer"';

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $pengguna = $stmt->fetchColumn();
    return $pengguna;
};

function custom($pdo)
{
    $query = "SELECT COUNT(*) FROM custom_orders";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $custom = $stmt->fetchColumn();
    return $custom;
};

function paid_transactions($pdo)
{
    $query = "SELECT COUNT(*) FROM transactions WHERE status = 'paid'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $paid_transactions = $stmt->fetchColumn();
    return $paid_transactions;
};

function transactions_in_proses($pdo)
{
    $query = "SELECT COUNT(*) FROM transactions WHERE status != 'completed'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $transactions_in_proses = $stmt->fetchColumn();
    return $transactions_in_proses;
};

function failed_transactions($pdo)
{
    $query = "SELECT COUNT(*) FROM transactions WHERE status = 'cancelled'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $failed_transactions = $stmt->fetchColumn();
    return $failed_transactions;
};

function completed_transactions($pdo)
{
    $query = "SELECT COUNT(*) FROM transactions WHERE status = 'completed'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $failed_transactions = $stmt->fetchColumn();
    return $failed_transactions;
};

function appointments($pdo)
{
    $query = "SELECT COUNT(*) FROM appointments "; //WHERE status ='pending'

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $appointmens = $stmt->fetchColumn();
    return $appointmens;
};

?>
<div class="content">
    <h2>Selamat Datang, Admin</h2>
    <p class="text-muted">Berikut ringkasan aktivitas toko emas:</p>

    <div class="row my-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Produk</h5>
                    <p class="card-text fs-4"><?php echo total_products($pdo); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Pengguna</h5>
                    <p class="card-text fs-4"><?php echo pengguna($pdo); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Custom</h5>
                    <p class="card-text fs-4"><?php echo custom($pdo); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Janji Temu</h5>
                    <p class="card-text fs-4"><?php echo appointments($pdo); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row my-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Transaksi Dalam Proses</h5>
                    <p class="card-text fs-4"><?php echo transactions_in_proses($pdo); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Transaksi Selesai</h5>
                    <p class="card-text fs-4"><?php echo completed_transactions($pdo); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Transaksi Gagal</h5>
                    <p class="card-text fs-4"><?php echo failed_transactions($pdo); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>