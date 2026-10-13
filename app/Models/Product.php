<?php
namespace App\Models;

use PDO;

class Product {
    public static function getAll($categoryId = null, $type = null, $search = null) {
        $pdo = Database::getInstance();
        $sql = "SELECT p.*, s.shop_name, s.shop_slug, c.name as category_name, c.type as category_type 
                FROM products p
                JOIN shops s ON p.shop_id = s.id
                JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'ACTIVE'";
        $params = [];

        if (!empty($categoryId)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        if (!empty($type)) {
            if ($type === 'DIY') {
                $sql .= " AND p.is_diy = 1";
            } elseif ($type === 'TECH') {
                $sql .= " AND c.type = 'TECH'";
            }
        }

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT p.*, s.shop_name, s.shop_slug, s.avatar as shop_avatar, s.rating as shop_rating, c.name as category_name 
                               FROM products p
                               JOIN shops s ON p.shop_id = s.id
                               JOIN categories c ON p.category_id = c.id
                               WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByShop($shopId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.shop_id = ? ORDER BY p.created_at DESC");
        $stmt->execute([$shopId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO products (id, shop_id, category_id, name, slug, price, original_price, stock, sku, is_diy, diy_craft_time, attributes_json, image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['id'],
            $data['shop_id'],
            $data['category_id'],
            $data['name'],
            $data['slug'],
            $data['price'],
            $data['original_price'] ?? 0,
            $data['stock'] ?? 10,
            $data['sku'] ?? null,
            $data['is_diy'] ?? 0,
            $data['diy_craft_time'] ?? null,
            $data['attributes_json'] ?? null,
            $data['image'] ?? null,
            $data['description'] ?? null
        ]);
    }

    public static function checkDuplicate($name, $image, $shopId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id FROM products WHERE shop_id = ? AND (name = ? OR image = ?)");
        $stmt->execute([$shopId, $name, $image]);
        return (bool)$stmt->fetch();
    }
}
