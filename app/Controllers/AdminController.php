<?php
namespace App\Controllers;

use App\Models\Database;
use App\Models\Order;
use App\Models\Shop;
use App\Services\AuthService;
use App\Services\EncryptionService;

class AdminController {
    public function __construct() {
        AuthService::requireAdminStealth();
    }

    public function dashboard() {
        $currentUser = AuthService::user();
        $pdo = Database::getInstance();

        $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalShops = $pdo->query("SELECT COUNT(*) FROM shops")->fetchColumn();
        $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'PAID'")->fetchColumn();

        $recentOrders = Order::getAllAdmin();
        $shops = Shop::getAll();

        require __DIR__ . '/../../views/admin/dashboard.php';
    }

    public function securityAudit() {
        $currentUser = AuthService::user();
        $pdo = Database::getInstance();

        // Lấy danh sách tài khoản để quản lý và kiểm soát thông tin tài khoản
        $users = $pdo->query("SELECT id, username, email, full_name, role, phone, address, created_at FROM users ORDER BY created_at ASC")->fetchAll();

        foreach ($users as &$u) {
            $u['phone_display'] = !empty($u['phone']) ? EncryptionService::decryptField($u['phone']) : 'Chưa cập nhật';
            $u['address_display'] = !empty($u['address']) ? EncryptionService::decryptField($u['address']) : 'Chưa cập nhật';
        }

        // Lấy danh sách đơn hàng để kiểm soát và đối soát giao dịch
        $orders = $pdo->query("SELECT id, order_code, customer_name, phone, address, payment_method, payment_status, total_amount, created_at FROM orders ORDER BY created_at DESC LIMIT 20")->fetchAll();

        foreach ($orders as &$o) {
            $o['phone_display'] = !empty($o['phone']) ? EncryptionService::decryptField($o['phone']) : 'Chưa cập nhật';
            $o['address_display'] = !empty($o['address']) ? EncryptionService::decryptField($o['address']) : 'Chưa cập nhật';
        }

        require __DIR__ . '/../../views/admin/security_audit.php';
    }
}
