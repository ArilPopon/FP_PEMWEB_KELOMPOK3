<?php
require_once 'middleware/admin_auth.php';
include 'includes/header.php';
include 'includes/sidebar.php';
require_once './../config/database.php';

$query_admin = "SELECT * FROM users WHERE role LIKE 'admin'";
$query_customer = "SELECT * FROM users WHERE role LIKE 'customer'";

$stmt_admin = $pdo->prepare($query_admin);
$stmt_customer = $pdo->prepare($query_customer);

$stmt_admin->execute();
$stmt_customer->execute();

$admin = $stmt_admin->fetchAll();
$customer = $stmt_customer->fetchAll();
?>

<div class="content">
    <h2 style="text-align: center;">MANAJEMEN PENGGUNA</h2><hr/>
    <h3>Data Admin</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>id</th>
                    <th>Name</th>
                    <th>E-Mail</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($admin): ?>
                    <?php foreach ($admin as $admin): ?>
                        <tr>
                            <td><?= htmlspecialchars($admin['id']); ?></td>
                            <td><?= htmlspecialchars($admin['name']); ?></td>
                            <td><?= htmlspecialchars($admin['email']); ?></td>
                            <td><?= htmlspecialchars($admin['created_at']);?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Data kosong / Data tidak ditemukan!</p>
                <?php endif; ?>
            </tbody>
        </table>
        <h3>Data Pengguna</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped talign-middle">
                <thead class="table-dark">
                    <tr>
                        <th>id</th>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Phone</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($customer): ?>
                        <?php foreach($customer as $customer): ?>
                            <tr>
                                <td><?= htmlspecialchars($customer['id']); ?></td>
                                <td><?= htmlspecialchars($customer['name']); ?></td>
                                <td><?= htmlspecialchars($customer['email']); ?></td>
                                <td><?= htmlspecialchars($customer['phone']); ?></td>
                                <td><?= htmlspecialchars($customer['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Data kosong / Data tidak ditemukan!</p>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>