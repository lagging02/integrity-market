<?php
namespace App\Models;

use App\Services\EncryptionService;
use App\Services\LogisticsService;
use PDO;

class Order {
    public static function create($orderData, $items) {
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            // Mã hóa dữ liệu định danh cá nhân (PII Encryption - AES-256)
            $encPhone = EncryptionService::encryptField($orderData['phone']);
            $encAddress = EncryptionService::encryptField($orderData['address']);

            $stmt = $pdo->prepare("INSERT INTO orders (id, order_code, buyer_id, shop_id, customer_name, phone, address, payment_method, payment_status, fulfillment_status, total_amount, shipping_fee, shipping_carrier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderData['id'],
                $orderData['order_code'],
                $orderData['buyer_id'],
                $orderData['shop_id'],
                $orderData['customer_name'],
                $encPhone,
                $encAddress,
                $orderData['payment_method'],
                $orderData['payment_status'] ?? 'PENDING',
                $orderData['fulfillment_status'] ?? 'AWAITING_FULFILLMENT',
                $orderData['total_amount'],
                $orderData['shipping_fee'] ?? 30000,
                $orderData['shipping_carrier'] ?? 'GHN Express'
            ]);

            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, variant_info) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $itemStmt->execute([
                    $orderData['id'],
                    $item['product_id'],
                    $item['product_name'],
                    $item['price'],
                    $item['quantity'],
                    $item['variant_info'] ?? null
                ]);
            }

            // Tạo log vận chuyển khởi đầu
            LogisticsService::addTrackingLog(
                $orderData['id'],
                'AWAITING_FULFILLMENT',
                'Đơn hàng đã được tạo thành công',
                'Chờ Người Bán xác nhận và đóng gói sản phẩm.',
                'Hệ thống Sàn TMĐT'
            );

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public static function getById($id, $authUserId = null, $authRole = null) {
        $pdo = Database::getInstance();
        
        $sql = "SELECT o.*, s.shop_name, s.shop_slug, u.username as buyer_username 
                FROM orders o 
                LEFT JOIN shops s ON o.shop_id = s.id 
                LEFT JOIN users u ON o.buyer_id = u.id 
                WHERE o.id = ?";
        $params = [$id];

        // Kiểm tra chống IDOR theo Chương 5.1 trong docx:
        // Nếu là phiên vừa tạo đơn (chính chủ session hiện tại) thì luôn cho phép xem trạng thái đơn
        $isCurrentSessionOrder = (isset($_SESSION['last_order_id']) && $_SESSION['last_order_id'] === $id);

        if (!$isCurrentSessionOrder && $authRole === 'BUYER' && $authUserId) {
            $sql .= " AND o.buyer_id = ?";
            $params[] = $authUserId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch();

        if ($order) {
            $order['phone_decrypted'] = EncryptionService::decryptField($order['phone']);
            $order['address_decrypted'] = EncryptionService::decryptField($order['address']);

            // Lấy items
            $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemStmt->execute([$id]);
            $order['items'] = $itemStmt->fetchAll();
        }

        return $order;
    }

    public static function getByOrderCode($orderCode) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE order_code = ?");
        $stmt->execute([trim($orderCode)]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return self::getById($id);
        }
        return null;
    }

    public static function getByShop($shopId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT o.*, u.username as buyer_username 
                               FROM orders o 
                               JOIN users u ON o.buyer_id = u.id 
                               WHERE o.shop_id = ? 
                               ORDER BY o.created_at DESC");
        $stmt->execute([$shopId]);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$o) {
            $o['phone_decrypted'] = EncryptionService::decryptField($o['phone']);
            $o['address_decrypted'] = EncryptionService::decryptField($o['address']);
        }
        return $orders;
    }

    public static function getByBuyer($buyerId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT o.*, s.shop_name FROM orders o JOIN shops s ON o.shop_id = s.id WHERE o.buyer_id = ? ORDER BY o.created_at DESC");
        $stmt->execute([$buyerId]);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$o) {
            $o['phone_decrypted'] = EncryptionService::decryptField($o['phone']);
            $o['address_decrypted'] = EncryptionService::decryptField($o['address']);
        }
        return $orders;
    }

    public static function getAllAdmin() {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT o.*, s.shop_name, u.username as buyer_username FROM orders o JOIN shops s ON o.shop_id = s.id JOIN users u ON o.buyer_id = u.id ORDER BY o.created_at DESC");
        $orders = $stmt->fetchAll();

        foreach ($orders as &$o) {
            $o['phone_decrypted'] = EncryptionService::decryptField($o['phone']);
            $o['address_decrypted'] = EncryptionService::decryptField($o['address']);
        }
        return $orders;
    }
}
