<?php
namespace App\Controllers;

use App\Models\Database;
use App\Models\Order;
use App\Services\AuthService;
use App\Services\LogisticsService;
use App\Services\PaymentService;

class OrderController {
    /**
     * Trang Chờ Thanh Toán (Pending Payment Invoice & Dynamic VietQR)
     */
    public function pending() {
        AuthService::startSession();
        $currentUser = AuthService::user();

        $id = $_GET['id'] ?? '';
        $order = Order::getById($id, $currentUser['id'] ?? null, $currentUser['role'] ?? null);

        if (!$order) {
            header('Location: index.php');
            exit;
        }

        // Nếu đơn hàng đã được thanh toán rồi, chuyển hướng thẳng sang trang thành công
        if ($order['payment_status'] === 'PAID') {
            header("Location: index.php?page=order-success&id=" . $id . "&msg=paid");
            exit;
        }

        $vietqrUrl = PaymentService::getVietQrUrl($order['total_amount'], $order['order_code']);

        require __DIR__ . '/../../views/buyer/payment_pending.php';
    }

    /**
     * API kiểm tra realtime trạng thái đơn hàng (Polling Ajax từ trình duyệt)
     */
    public function checkPaymentStatus() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'error' => 'Missing order ID']);
            exit;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT payment_status FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $status = $stmt->fetchColumn();

        if (!$status) {
            echo json_encode(['success' => false, 'error' => 'Order not found']);
            exit;
        }

        echo json_encode([
            'success'        => true,
            'payment_status' => $status,
            'is_paid'        => ($status === 'PAID')
        ]);
        exit;
    }

    /**
     * Nút mô phỏng xác nhận tiền vào từ Webhook Napas/Bank (Dành cho Demo nhanh)
     */
    public function confirmPendingPayment() {
        AuthService::startSession();
        $id = $_POST['order_id'] ?? $_GET['id'] ?? '';
        if (!empty($id)) {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'PAID', fulfillment_status = 'READY_TO_PICK' WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: index.php?page=order-success&id=" . $id . "&msg=paid");
            exit;
        }
        header("Location: index.php");
        exit;
    }

    public function success() {
        AuthService::startSession();
        $currentUser = AuthService::user();

        $id = $_GET['id'] ?? '';
        $order = Order::getById($id, $currentUser['id'] ?? null, $currentUser['role'] ?? null);

        if (!$order) {
            header('Location: index.php');
            exit;
        }

        $vietqrUrl = PaymentService::getVietQrUrl($order['total_amount'], $order['order_code']);

        require __DIR__ . '/../../views/buyer/order_success.php';
    }

    public function tracking() {
        AuthService::startSession();
        $currentUser = AuthService::user();

        $id = $_GET['id'] ?? '';
        $orderCode = trim($_POST['order_code'] ?? $_GET['order_code'] ?? '');
        $error = '';

        $order = null;
        if (!empty($id)) {
            $order = Order::getById($id, $currentUser['id'] ?? null, $currentUser['role'] ?? null);
            if (!$order) {
                $error = 'Không tìm thấy thông tin đơn hàng này hoặc phiên làm việc đã thay đổi.';
            }
        } elseif (!empty($orderCode)) {
            $order = Order::getByOrderCode($orderCode);
            if (!$order) {
                $error = 'Không tìm thấy đơn hàng với mã [' . htmlspecialchars($orderCode) . ']. Vui lòng kiểm tra lại!';
            }
        }

        $trackingLogs = [];
        if ($order) {
            $trackingLogs = LogisticsService::getTrackingLogs($order['id']);
        }

        require __DIR__ . '/../../views/buyer/order_tracking.php';
    }

    public function myOrders() {
        AuthService::requireRole(['BUYER', 'SELLER', 'ADMIN']);
        $currentUser = AuthService::user();

        $orders = Order::getByBuyer($currentUser['id']);

        require __DIR__ . '/../../views/buyer/my_orders.php';
    }
}
