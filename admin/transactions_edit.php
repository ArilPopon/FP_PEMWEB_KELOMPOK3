<?php
require_once 'middleware/admin_auth.php';
require_once './../config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: transactions.php');
    exit;
}

$validStatuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];

$id = (int) $_GET['id'];

$query = "SELECT * FROM transactions WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$transactions = $stmt->fetch(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $id])) {
        header("Location: transactions.php");
        exit;
    } else {
        $error = "Gagal mengupdate status transaksi!";
    }
}

$currentStatus = $transactions['status'];
?>

<div class="content">
    <h2>Edit Produk</h2>
    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-1">
            <label>Status</label>
            <select name="status" class="form-control">
                <?php foreach ($validStatuses as $validStatus): ?>
                    <option value="<?= htmlspecialchars($validStatus); ?>" <?= ($validStatus === $currentStatus) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($validStatus); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="transactions.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>