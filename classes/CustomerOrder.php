<?php
class CustomerOrder
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $description, $image)
    {
        $sql = "INSERT INTO custom_orders (user_id, description, reference_image) VALUES (:user_id, :description, :image)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':description' => $description,
            ':image' => $image
        ]);
    }

    public function getAll($keyword = '')
    {
        $sql = "SELECT * FROM custom_orders";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " WHERE description LIKE :keyword OR status LIKE :keyword";
            $params[':keyword'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status)
    {
        if ($status === 'completed') {
            $stmt = $this->pdo->prepare("UPDATE custom_orders SET status = ?, completed_at = NOW() WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } elseif ($status === 'shipped') {
            $stmt = $this->pdo->prepare("UPDATE custom_orders SET status = ?, shipped_at = NOW() WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } elseif ($status === 'arrived') {
            $stmt = $this->pdo->prepare("UPDATE custom_orders SET status = ?, arrived_at = NOW() WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE custom_orders SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        }
    }


    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM custom_orders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updatePrice($id, $price)
    {
        $stmt = $this->pdo->prepare("UPDATE custom_orders SET estimated_price = ? WHERE id = ?");
        return $stmt->execute([$price, $id]);
    }
}
