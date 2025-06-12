<?php
session_start();
require_once './config/database.php';
require_once './classes/Cart.php';
include 'template/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$cart = new Cart($pdo, $_SESSION['user']['id']);
$items = $cart->getItems();

// Proses Checkout
if (isset($_POST['checkout_submit']) && isset($_FILES['bukti'])) {
    $userId = $_SESSION['user']['id'];
    $totalPrice = 0;

    foreach ($items as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }

    // Upload bukti pembayaran
    $proofName = time() . '_' . $_FILES['bukti']['name'];
    $targetDir = 'uploads/bukti/';
    $targetFile = $targetDir . basename($proofName);
    move_uploaded_file($_FILES['bukti']['tmp_name'], $targetFile);

    // Simpan ke transactions
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, total_price, status, proof, created_at) VALUES (?, ?, 'pending', ?, NOW())");
    $stmt->execute([$userId, $totalPrice, $proofName]);
    $transactionId = $pdo->lastInsertId();

    // Simpan ke order_items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([
            $transactionId,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    // Kosongkan keranjang
    $cart->clearCart();

    echo "<script>alert('Checkout berhasil! Menunggu konfirmasi.'); location.href='cart.php';</script>";
    exit;
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Keranjang Belanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fefefe;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: gold;
            padding: 20px;
            text-align: center;
            color: #000;
            font-weight: bold;
        }

        table {
            width: 80%;
            margin: 30px auto;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f2c94c;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total {
            font-weight: bold;
            background-color: #f6e58d;
        }

        .btn {
            padding: 10px 15px;
            background: gold;
            color: black;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: darkorange;
        }

        a {
            color: blue;
        }
    </style>
</head>

<body>

    <header>
        Keranjang Belanja Anda
    </header>

    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; ?>
            <?php foreach ($items as $item):
                $subtotal = $item['quantity'] * $item['price'];
                $total += $subtotal; ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                    <td>
                        <a href="classes/remove_from_cart.php?product_id=<?= $item['product_id'] ?>" onclick="return confirm('Hapus item ini dari keranjang?')" class="btn btn-danger btn-sm">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="total">
                <td colspan="3">Total</td>
                <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="d-flex justify-content-center gap-2">
        <a href="catalog.php" class="btn">Kembali ke Katalog</a>
        <?php if (!empty($items) && $total > 0): ?>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#checkoutModal">Checkout</button>
        <?php endif; ?>
    </div>

    <!-- Modal Checkout -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" enctype="multipart/form-data" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Upload Bukti Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bukti" class="form-label">Kirim Ke BRI
                            <br> Nomor Rekening : 320280408301830
                            <br> Atas Nama : TOKO MAS ERISON SIREGAR
                        </label>
                    </div>
                    <div class="mb-3">
                        <label for="bukti" class="form-label">Bukti Transfer (JPG/PNG)</label>
                        <input type="file" name="bukti" id="bukti" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="checkout_submit" class="btn btn-primary">Kirim</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>