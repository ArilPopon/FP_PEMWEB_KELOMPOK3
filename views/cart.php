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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Keranjang Belanja</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fefefe; margin: 0; padding: 0; }
        header { background-color: gold; padding: 20px; text-align: center; color: #000; font-weight: bold; }
        table { width: 80%; margin: 30px auto; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #f2c94c; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .total { font-weight: bold; background-color: #f6e58d; }
        .btn { padding: 10px 15px; background: gold; color: black; border: none; cursor: pointer; }
        .btn:hover { background: darkorange; }
        a { color: blue; }
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
        $total += $subtotal;?>
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

<div style="text-align:center;">
    <a href="catalog.php" class="btn">Kembali ke Katalog</a>
</div>

</body>
</html>

<?php include 'template/footer.php'; ?>
