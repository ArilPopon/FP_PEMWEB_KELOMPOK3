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

function transactions_in_proses($pdo)
{
    $query1 = "SELECT COUNT(*) FROM transactions WHERE status NOT IN ('completed', 'cancelled')";
    $query2 = "SELECT COUNT(*) FROM custom_orders WHERE status NOT IN ('arrived', 'cancelled')";

    $stmt1 = $pdo->query($query1);
    $stmt2 = $pdo->query($query2);

    return $stmt1->fetchColumn() + $stmt2->fetchColumn();
}


function failed_transactions($pdo)
{
    $query1 = "SELECT COUNT(*) FROM transactions WHERE status = 'cancelled'";
    $query2 = "SELECT COUNT(*) FROM custom_orders WHERE status = 'cancelled'";

    $stmt1 = $pdo->query($query1);
    $stmt2 = $pdo->query($query2);

    return $stmt1->fetchColumn() + $stmt2->fetchColumn();
}


function completed_transactions($pdo)
{
    $query1 = "SELECT COUNT(*) FROM transactions WHERE status = 'completed'";
    $query2 = "SELECT COUNT(*) FROM custom_orders WHERE status = 'arrived'";

    $stmt1 = $pdo->query($query1);
    $stmt2 = $pdo->query($query2);

    return $stmt1->fetchColumn() + $stmt2->fetchColumn();
}


function appointments($pdo)
{
    $query = "SELECT COUNT(*) FROM appointments "; //WHERE status ='pending'

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $appointmens = $stmt->fetchColumn();
    return $appointmens;
};

// Data transaksi bulan ini (gabungan dari transactions dan custom_orders)
$startOfMonth = date('Y-m-01 00:00:00');
$endOfMonth = date('Y-m-t 23:59:59');

// Transaksi katalog
$stmt1 = $pdo->prepare("SELECT SUM(total_price) FROM transactions WHERE status IN ('paid','shipped','completed') AND paid_at BETWEEN :start AND :end");
$stmt1->execute(['start' => $startOfMonth, 'end' => $endOfMonth]);
$totalKatalog = $stmt1->fetchColumn() ?: 0;

// Transaksi custom
$stmt2 = $pdo->prepare("SELECT SUM(estimated_price) FROM custom_orders WHERE payment_proof IS NOT NULL AND created_at BETWEEN :start AND :end");
$stmt2->execute(['start' => $startOfMonth, 'end' => $endOfMonth]);
$totalCustom = $stmt2->fetchColumn() ?: 0;

// Data status pengiriman dari transaksi katalog
$query_katalog = "
    SELECT 
        SUM(CASE WHEN status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) AS belum_dikirim,
        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) AS dikirim,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS diterima
    FROM transactions
";
$status_katalog = $pdo->query($query_katalog)->fetch(PDO::FETCH_ASSOC);

// Data status pengiriman dari custom_orders
$query_custom = "
    SELECT 
        SUM(CASE WHEN status NOT IN ('arrived', 'cancelled') THEN 1 ELSE 0 END) AS belum_dikirim,
        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) AS dikirim,
        SUM(CASE WHEN status = 'arrived' THEN 1 ELSE 0 END) AS diterima
    FROM custom_orders
";
$status_custom = $pdo->query($query_custom)->fetch(PDO::FETCH_ASSOC);

// Gabungan total pengiriman
$total_belum_dikirim = $status_katalog['belum_dikirim'] + $status_custom['belum_dikirim'];
$total_dikirim = $status_katalog['dikirim'] + $status_custom['dikirim'];
$total_diterima = $status_katalog['diterima'] + $status_custom['diterima'];

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
    <div class="row my-4">
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Transaksi Bulan Ini</h5>
                    <canvas id="chartBulanan" height="207"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Status Pengiriman</h5>
                    <canvas id="pengirimanChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartBulanan = new Chart(document.getElementById('chartBulanan'), {
        type: 'bar',
        data: {
            labels: ['Transaksi Katalog', 'Transaksi Custom'],
            datasets: [{
                label: 'Total Transaksi Bulan Ini',
                data: [<?= $totalKatalog ?>, <?= $totalCustom ?>],
                backgroundColor: ['#007bff', '#28a745']
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    const pengirimanChart = new Chart(document.getElementById('pengirimanChart'), {
        type: 'doughnut',
        data: {
            labels: ['Belum Dikirim', 'Dikirim', 'Diterima'],
            datasets: [{
                label: 'Status Pengiriman',
                data: [<?= $total_belum_dikirim ?>, <?= $total_dikirim ?>, <?= $total_diterima ?>],
                backgroundColor: ['#f39c12', '#3498db', '#2ecc71'],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return `${label}: ${value} pengiriman`;
                        }
                    }
                }
            }
        }
    });
</script>
</body>

</html>