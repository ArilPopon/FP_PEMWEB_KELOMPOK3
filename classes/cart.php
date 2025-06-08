<?php
class Cart
{
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function addProduct($productId, $quantity = 1)
    {
        // Cek apakah produk sudah ada di keranjang
        $stmt = $this->pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->userId, $productId]);
        $item = $stmt->fetch();

        if ($item) {
            // Jika sudah ada, update quantity
            $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $this->userId, $productId]);
        } else {
            // Jika belum, insert baru
            $stmt = $this->pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$this->userId, $productId, $quantity]);
        }
    }

    public function getItems()
    {
        $stmt = $this->pdo->prepare("SELECT cart_items.*, products.name, products.price, products.image 
                                     FROM cart_items 
                                     JOIN products ON cart_items.product_id = products.id 
                                     WHERE cart_items.user_id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }

    public function removeItem($productId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->userId, $productId]);
    }

    public function clearCart()
    {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        return $stmt->execute([$this->userId]);
    }
}
