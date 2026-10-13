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
            $order = Order::getById($id);
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

    /**
     * Chuyển trạng thái vận chuyển 3PL GHN (Dành cho demo kịch bản chấm đồ án)
     */
    public function simulateDelivery() {
        AuthService::startSession();
        $orderId = $_POST['order_id'] ?? $_GET['id'] ?? '';
        $newStatus = $_POST['status'] ?? $_GET['status'] ?? '';
        $location = $_POST['location'] ?? $_GET['location'] ?? 'Bưu cục trung chuyển GHN Express';

        if (!empty($orderId) && !empty($newStatus)) {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("SELECT tracking_code FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $ord = $stmt->fetch();
            if ($ord && empty($ord['tracking_code'])) {
                LogisticsService::createShippingOrder($orderId);
            }
            LogisticsService::updateStatus($orderId, $newStatus, $location);
            header("Location: index.php?page=order-tracking&id=" . urlencode($orderId) . "&msg=status_updated");
            exit;
        }
        header("Location: index.php?page=order-tracking");
        exit;
    }

    /**
     * Thanh toán lại hoặc đổi phương thức thanh toán cho đơn hàng PENDING
     */
    public function retryPayment() {
        AuthService::startSession();
        $orderId = $_POST['order_id'] ?? $_GET['id'] ?? '';
        $method = $_POST['payment_method'] ?? $_GET['method'] ?? 'COD';

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order && $order['payment_status'] !== 'PAID') {
            if ($method === 'COD') {
                $upd = $pdo->prepare("UPDATE orders SET payment_method = 'COD' WHERE id = ?");
                $upd->execute([$orderId]);
                LogisticsService::addTrackingLog($orderId, 'AWAITING_FULFILLMENT', 'Chuyển sang thanh toán COD', 'Người mua đã chọn thanh toán tiền mặt khi nhận hàng (Đồng kiểm 3PL).', 'Hệ thống Sàn TMĐT');
                header("Location: index.php?page=order-tracking&id=" . urlencode($orderId) . "&msg=cod_switched");
                exit;
            } elseif ($method === 'VNPAY') {
                $upd = $pdo->prepare("UPDATE orders SET payment_method = 'VNPAY' WHERE id = ?");
                $upd->execute([$orderId]);
                $url = PaymentService::createVnpayPaymentUrl($order['order_code'], $order['total_amount']);
                if ($url) { header("Location: " . $url); exit; }
            } elseif ($method === 'MOMO') {
                $upd = $pdo->prepare("UPDATE orders SET payment_method = 'MOMO' WHERE id = ?");
                $upd->execute([$orderId]);
                $url = PaymentService::createMomoPaymentUrl($order['order_code'], $order['total_amount']);
                if ($url) { header("Location: " . $url); exit; }
            } elseif ($method === 'ZALOPAY') {
                $upd = $pdo->prepare("UPDATE orders SET payment_method = 'ZALOPAY' WHERE id = ?");
                $upd->execute([$orderId]);
                $zaloData = PaymentService::createZaloPayPaymentUrl($order['order_code'], $order['total_amount']);
                if ($zaloData) {
                    $_SESSION['zalopay_' . $orderId] = $zaloData;
                    header("Location: index.php?page=zalopay-gateway&id=" . urlencode($orderId));
                    exit;
                }
            } elseif ($method === 'PAYPAL') {
                $upd = $pdo->prepare("UPDATE orders SET payment_method = 'PAYPAL' WHERE id = ?");
                $upd->execute([$orderId]);
                $url = PaymentService::createPaypalPaymentUrl($order['order_code'], $order['total_amount']);
                if ($url) { header("Location: " . $url); exit; }
            }
            // Fallback mở cổng QR ZaloPay/VietQR
            header("Location: index.php?page=zalopay-gateway&id=" . urlencode($orderId));
            exit;
        }

        header("Location: index.php?page=order-tracking&id=" . urlencode($orderId));
        exit;
    }
}
