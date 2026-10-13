<?php
namespace App\Models;

use PDO;

class Shop {
    public static function getById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM shops WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByUserId($userId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM shops WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function getAll() {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM shops WHERE status = 'ACTIVE' ORDER BY rating DESC")->fetchAll();
    }
}
