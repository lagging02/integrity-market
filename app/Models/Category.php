<?php
namespace App\Models;

use PDO;

class Category {
    public static function getAll() {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
    }
}
