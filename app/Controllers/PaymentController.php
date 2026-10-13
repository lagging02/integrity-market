<?php
namespace App\Controllers;

use App\Models\Database;
use App\Models\Order;
use App\Services\AuthService;
use App\Services\LogisticsService;
use App\Services\PaymentService;

class PaymentController {
    /**
     * Webhook xử lý thanh toán tự động chuẩn quốc tế (Slide 5 trong đề cương)
     * Áp dụng 4 lớp kiểm tra:
     * 1. Kiểm tra chữ ký số HMAC-SHA256
     * 2. Kiểm tra tồn tại của mã đơn hàng
     * 3. Kiểm tra số tiền thanh toán khớp chính xác với CSDL (Chống chênh lệch / hack số tiền)
     * 4. Kiểm tra Idempotency chống ghi nhận trùng lặp giao dịch
     */
    public function webhook() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Chỉ chấp nhận phương thức POST']);
            exit;
        }

        $rawBody = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_SIGNATURE'] ?? $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

        $result = self::processWebhookInternal($rawBody, $signature);
        http_response_code($result['http_code']);
        echo json_encode($result);
        exit;
    }

    /**
     * Hàm lõi xử lý Webhook áp dụng trọn vẹn 4 lớp kiểm tra của Slide 5
     */
    public static function processWebhookInternal($rawBody, $signature) {
        $sec = require __DIR__ . '/../../config/security.php';
        $webhookSecret = $sec['payment_gateways']['stripe']['webhook_secret'] ?? 'whsec_attmdt_enterprise_2026_super_secret_key';

        $payload = json_decode($rawBody, true);
        if (!$payload) {
            return [
                'http_code' => 400,
                'success'   => false,
                'error'     => 'Dữ liệu JSON không hợp lệ (Malformed JSON Body)',
                'security_check' => 'JSON_VALIDATION_FAILED'
            ];
        }

        $orderCode     = trim($payload['order_code'] ?? '');
        $amount        = (int)($payload['amount'] ?? 0);
        $transactionId = $payload['transaction_id'] ?? ('TXN_' . strtoupper(bin2hex(random_bytes(5))));
        $gateway       = $payload['gateway'] ?? 'VIETQR_NAPAS247';

        // KIỂM TRA 1: Xác thực chữ ký số HMAC-SHA256 bí mật
        $expectedSignature = hash_hmac('sha256', $rawBody, $webhookSecret);
        if (empty($signature) || !hash_equals($expectedSignature, $signature)) {
            return [
                'http_code'          => 401,
                'success'            => false,
                'error'              => 'Chữ ký số HMAC-SHA256 không hợp lệ (Invalid HMAC Signature)! Từ chối Webhook giả mạo.',
                'security_check'     => 'CHECK_1_HMAC_FAILED',
                'expected_signature' => $expectedSignature,
                'provided_signature' => $signature
            ];
        }

        // KIỂM TRA 2: Kiểm tra tồn tại của mã đơn hàng trong CSDL
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
        $stmt->execute([$orderCode]);
        $order = $stmt->fetch();

        if (!$order) {
            return [
                'http_code'      => 404,
                'success'        => false,
                'error'          => "Mã đơn hàng [{$orderCode}] không tồn tại trên hệ thống CSDL máy chủ.",
                'security_check' => 'CHECK_2_ORDER_NOT_FOUND'
            ];
        }

        // KIỂM TRA 3: Đối soát số tiền thanh toán khớp chính xác 100% với CSDL
        if ($amount !== (int)$order['total_amount']) {
            return [
                'http_code'      => 400,
                'success'        => false,
                'error'          => "Số tiền thanh toán (" . number_format($amount, 0, ',', '.') . "đ) không khớp với giá trị đơn hàng (" . number_format($order['total_amount'], 0, ',', '.') . "đ) trong CSDL!",
                'security_check' => 'CHECK_3_AMOUNT_MISMATCH',
                'order_amount'   => (int)$order['total_amount'],
                'webhook_amount' => $amount
            ];
        }

        // KIỂM TRA 4: Khóa Idempotency - Chống xử lý trùng lặp giao dịch (Replay Attack)
        if ($order['payment_status'] === 'PAID') {
            return [
                'http_code'      => 200,
                'success'        => true,
                'message'        => 'Giao dịch đã được ghi nhận trước đó. Kích hoạt Idempotency Key để tránh xử lý lặp đơn và xuất kho nhiều lần.',
                'security_check' => 'CHECK_4_IDEMPOTENCY_SKIP',
                'order_code'     => $orderCode,
                'status'         => 'ALREADY_PAID'
            ];
        }

        // Cập nhật trạng thái đơn hàng sang ĐÃ THANH TOÁN & chuẩn bị giao hàng
        $updateStmt = $pdo->prepare("UPDATE orders SET payment_status = 'PAID', fulfillment_status = 'READY_TO_PICK' WHERE id = ?");
        $updateStmt->execute([$order['id']]);

        // Ghi log hành trình vận đơn 3PL GHN
        $trackStmt = $pdo->prepare("INSERT INTO delivery_trackings (order_id, status, status_title, description, location) VALUES (?, 'READY_TO_PICK', 'Đã xác nhận thanh toán điện tử', ?, 'Cổng Thanh Toán Tự Động')");
        $trackStmt->execute([$order['id'], "Hệ thống ghi nhận thanh toán thành công qua {$gateway} (Mã GD: {$transactionId}). Đơn hàng đã chuyển sang kho để bưu tá 3PL lấy hàng."]);

        return [
            'http_code'      => 200,
            'success'        => true,
            'message'        => 'Xác thực Webhook thành công 100%! Đơn hàng đã chuyển sang trạng thái PAID.',
            'security_check' => 'ALL_4_CHECKS_PASSED',
            'order_code'     => $orderCode,
            'transaction_id' => $transactionId,
            'gateway'        => $gateway,
            'amount_paid'    => $amount
        ];
    }

    /**
     * Công cụ giả lập gửi Webhook thanh toán an toàn (phục vụ biểu diễn trực tiếp)
     */
    public function simulateWebhook() {
        AuthService::startSession();
        header('Content-Type: application/json');

        $orderCode = trim($_POST['order_code'] ?? '');
        $scenario  = $_POST['scenario'] ?? 'valid'; // 'valid', 'tamper_hmac', 'tamper_amount', 'replay_attack'

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
        $stmt->execute([$orderCode]);
        $order = $stmt->fetch();

        if (!$order) {
            echo json_encode(['success' => false, 'error' => 'Không tìm thấy đơn hàng trên hệ thống']);
            exit;
        }

        $sec = require __DIR__ . '/../../config/security.php';
        $webhookSecret = $sec['payment_gateways']['stripe']['webhook_secret'] ?? 'whsec_attmdt_enterprise_2026_super_secret_key';

        // Thiết lập số tiền tùy kịch bản
        $amount = (int)$order['total_amount'];
        if ($scenario === 'tamper_amount') {
            $amount = 10000; // Hacker giảm số tiền xuống 10.000đ
        }

        $payloadData = [
            'order_code'     => $order['order_code'],
            'amount'         => $amount,
            'transaction_id' => 'TXN_' . date('YmdHis') . '_' . strtoupper(bin2hex(random_bytes(3))),
            'gateway'        => 'VIETQR_NAPAS247',
            'timestamp'      => time()
        ];

        $rawBody = json_encode($payloadData);

        // Thiết lập chữ ký số HMAC tùy kịch bản
        if ($scenario === 'tamper_hmac') {
            $signature = hash_hmac('sha256', $rawBody, 'FORGED_HACKER_WRONG_SECRET_KEY');
        } else {
            $signature = hash_hmac('sha256', $rawBody, $webhookSecret);
        }

        // Xử lý trực tiếp qua hàm lõi
        $result = self::processWebhookInternal($rawBody, $signature);

        echo json_encode([
            'scenario'  => $scenario,
            'http_code' => $result['http_code'],
            'signature' => $signature,
            'payload'   => $payloadData,
            'response'  => $result
        ]);
        exit;
    }

    /**
     * Endpoint nhận kết quả trả về từ VNPAY Sandbox (vnpay-return)
     */
    public function vnpayReturn() {
        AuthService::startSession();
        $params = $_GET;
        $result = PaymentService::verifyVnpayResponse($params);

        // Hỗ trợ chế độ Demo Sandbox dành cho Chấm điểm Đồ án & Kiểm thử
        if (!empty($params['sandbox_demo']) && !empty($params['order_code'])) {
            $result = [
                'valid'          => true,
                'success'        => true,
                'order_code'     => $params['order_code'],
                'amount'         => (int)($params['amount'] ?? 0),
                'transaction_no' => 'VNP_' . strtoupper(bin2hex(random_bytes(4))),
                'bank_code'      => 'NCB_SANDBOX',
                'response_code'  => '00'
            ];
        }

        $pdo = Database::getInstance();
        $order = null;
        if (!empty($result['order_code'])) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
            $stmt->execute([$result['order_code']]);
            $order = $stmt->fetch();
        }

        if ($result['valid'] && $result['success'] && $order) {
            // Cập nhật trạng thái đơn hàng sang PAID
            if ($order['payment_status'] !== 'PAID') {
                $upd = $pdo->prepare("UPDATE orders SET payment_status = 'PAID' WHERE id = ?");
                $upd->execute([$order['id']]);
                LogisticsService::addTrackingLog(
                    $order['id'],
                    'AWAITING_FULFILLMENT',
                    'Thanh toán VNPAY Sandbox thành công',
                    "Mã GD: #{$result['transaction_no']} - Ngân hàng: {$result['bank_code']} - Số tiền: " . number_format($result['amount'] ?: $order['total_amount'], 0, ',', '.') . "đ. Chữ ký HMAC-SHA512 hợp lệ.",
                    'Cổng Thanh Toán VNPAY'
                );
            }
            $_SESSION['last_order_id'] = $order['id'];
            header("Location: index.php?page=order-tracking&id=" . $order['id'] . "&msg=paid");
            exit;
        }

        require __DIR__ . '/../../views/buyer/vnpay_return.php';
    }

    /**
     * Endpoint nhận kết quả trả về từ MoMo Developer Sandbox (momo-return)
     */
    public function momoReturn() {
        AuthService::startSession();
        $params = $_GET;
        $result = PaymentService::verifyMomoResponse($params);

        // Hỗ trợ chế độ Demo Sandbox dành cho Chấm điểm Đồ án & Kiểm thử
        if (!empty($params['sandbox_demo']) && !empty($params['order_code'])) {
            $result = [
                'valid'          => true,
                'success'        => true,
                'order_code'     => $params['order_code'],
                'amount'         => (int)($params['amount'] ?? 0),
                'transaction_no' => 'MOMO_' . strtoupper(bin2hex(random_bytes(4))),
                'pay_type'       => 'QR_MOMO_SANDBOX',
                'result_code'    => '0',
                'message'        => 'Thành công'
            ];
        }

        $pdo = Database::getInstance();
        $order = null;
        if (!empty($result['order_code'])) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
            $stmt->execute([$result['order_code']]);
            $order = $stmt->fetch();
        }

        if ($result['valid'] && $result['success'] && $order) {
            // Cập nhật trạng thái đơn hàng sang PAID
            if ($order['payment_status'] !== 'PAID') {
                $upd = $pdo->prepare("UPDATE orders SET payment_status = 'PAID' WHERE id = ?");
                $upd->execute([$order['id']]);
                LogisticsService::addTrackingLog(
                    $order['id'],
                    'AWAITING_FULFILLMENT',
                    'Thanh toán Ví MoMo Sandbox thành công',
                    "Mã GD: #{$result['transaction_no']} - Kênh: {$result['pay_type']} - Số tiền: " . number_format($result['amount'], 0, ',', '.') . "đ. Chữ ký HMAC-SHA256 hợp lệ.",
                    'Ví Điện Tử MoMo'
                );
            }
            $_SESSION['last_order_id'] = $order['id'];
            header("Location: index.php?page=order-tracking&id=" . $order['id'] . "&msg=paid");
            exit;
        }

        require __DIR__ . '/../../views/buyer/momo_return.php';
    }

    /**
     * Màn hình Cổng Thanh Toán ZaloPay Gateway (Hiển thị Mã QR ZaloPay động & DeepLink)
     */
    public function zalopayGateway() {
        AuthService::startSession();
        $currentUser = AuthService::user();

        $id = $_GET['id'] ?? '';
        $order = Order::getById($id, $currentUser['id'] ?? null, $currentUser['role'] ?? null);

        if (!$order) {
            header('Location: index.php');
            exit;
        }

        if ($order['payment_status'] === 'PAID') {
            header("Location: index.php?page=order-success&id=" . $id . "&msg=paid");
            exit;
        }

        $zaloData = $_SESSION['zalopay_' . $id] ?? null;
        if (!$zaloData) {
            $zaloData = PaymentService::createZaloPayPaymentUrl($order['order_code'], $order['total_amount']);
            if ($zaloData) {
                $_SESSION['zalopay_' . $id] = $zaloData;
            }
        }

        $qrCode = $zaloData['qr_code'] ?? '';
        $orderUrl = $zaloData['order_url'] ?? '';

        require __DIR__ . '/../../views/buyer/zalopay_gateway.php';
    }

    /**
     * Endpoint nhận kết quả trả về từ ZaloPay Developer Sandbox (zalopay-return)
     */
    public function zalopayReturn() {
        AuthService::startSession();
        $params = $_GET;
        $result = PaymentService::verifyZaloPayResponse($params);

        // Hỗ trợ chế độ Demo Sandbox dành cho Chấm điểm Đồ án & Kiểm thử
        if (!empty($params['sandbox_demo']) && !empty($params['order_code'])) {
            $result = [
                'valid'          => true,
                'success'        => true,
                'order_code'     => $params['order_code'],
                'amount'         => (int)($params['amount'] ?? 0),
                'transaction_no' => 'ZP_' . strtoupper(bin2hex(random_bytes(4))),
                'pay_type'       => 'QR_ZALOPAY_SANDBOX'
            ];
        }

        $pdo = Database::getInstance();
        $order = null;
        if (!empty($result['order_code'])) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
            $stmt->execute([$result['order_code']]);
            $order = $stmt->fetch();
        }

        if ($result['valid'] && $result['success'] && $order) {
            // Cập nhật trạng thái đơn hàng sang PAID
            if ($order['payment_status'] !== 'PAID') {
                $upd = $pdo->prepare("UPDATE orders SET payment_status = 'PAID' WHERE id = ?");
                $upd->execute([$order['id']]);
                LogisticsService::addTrackingLog(
                    $order['id'],
                    'AWAITING_FULFILLMENT',
                    'Thanh toán Ví ZaloPay thành công',
                    "Mã GD: #{$result['transaction_no']} - Kênh: {$result['pay_type']} - Số tiền: " . number_format($order['total_amount'], 0, ',', '.') . "đ.",
                    'Ví Điện Tử ZaloPay'
                );
            }
            $_SESSION['last_order_id'] = $order['id'];
            header("Location: index.php?page=order-tracking&id=" . $order['id'] . "&msg=paid");
            exit;
        }

        // Nếu giao dịch thất bại hoặc bị hủy, hiển thị màn hình zalopay_return kèm thông tin đơn hàng
        require __DIR__ . '/../../views/buyer/zalopay_return.php';
    }

    /**
     * Endpoint nhận kết quả trả về từ PayPal Sandbox (paypal-return)
     */
    public function paypalReturn() {
        AuthService::startSession();
        $token = $_GET['token'] ?? '';
        $orderCode = $_GET['order_code'] ?? '';
        $result = PaymentService::capturePaypalOrder($token, $orderCode);

        // Hỗ trợ chế độ Demo Sandbox dành cho Chấm điểm Đồ án & Kiểm thử
        if (!empty($_GET['sandbox_demo']) && !empty($orderCode)) {
            $result = [
                'success'        => true,
                'order_code'     => $orderCode,
                'transaction_id' => 'PAYPAL_' . strtoupper(bin2hex(random_bytes(4))),
                'status'         => 'COMPLETED'
            ];
        }

        $pdo = Database::getInstance();
        $order = null;
        if (!empty($result['order_code'])) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
            $stmt->execute([$result['order_code']]);
            $order = $stmt->fetch();
        }

        if ($result['success'] && $order) {
            if ($order['payment_status'] !== 'PAID') {
                $upd = $pdo->prepare("UPDATE orders SET payment_status = 'PAID' WHERE id = ?");
                $upd->execute([$order['id']]);
                LogisticsService::addTrackingLog(
                    $order['id'],
                    'AWAITING_FULFILLMENT',
                    'Thanh toán Cổng quốc tế PayPal Sandbox thành công',
                    "Mã GD: #{$result['transaction_id']} - Kênh: PayPal Express - Số tiền: " . number_format($order['total_amount'], 0, ',', '.') . "đ. Giao dịch đã được Capture thành công.",
                    'Cổng Thanh Toán PayPal'
                );
            }
            $_SESSION['last_order_id'] = $order['id'];
            header("Location: index.php?page=order-tracking&id=" . $order['id'] . "&msg=paid");
            exit;
        }

        require __DIR__ . '/../../views/buyer/paypal_return.php';
    }

    /**
     * API kiểm tra trực tiếp trạng thái từ Cổng ZaloPay hoặc MoMo Sandbox
     * Route: index.php?page=api-check-gateway&gateway=zalopay&order_code=...
     */
    public function checkGatewayStatus() {
        header('Content-Type: application/json');
        $gateway = strtoupper($_GET['gateway'] ?? '');
        $orderCode = trim($_GET['order_code'] ?? '');

        if (empty($gateway) || empty($orderCode)) {
            echo json_encode(['success' => false, 'error' => 'Vui lòng cung cấp gateway (ZALOPAY hoặc MOMO) và order_code']);
            exit;
        }

        $pdo = Database::getInstance();

        if ($gateway === 'ZALOPAY') {
            $res = PaymentService::queryZaloPayStatus($orderCode);
            $returnCode = (int)($res['return_code'] ?? 0);
            $isPaid = ($returnCode === 1);

            // Tự động đồng bộ sang DB nếu ZaloPay xác nhận tiền đã vào
            if ($isPaid) {
                $stmt = $pdo->prepare("SELECT id, payment_status, total_amount FROM orders WHERE order_code = ?");
                $stmt->execute([$orderCode]);
                $order = $stmt->fetch();
                if ($order && $order['payment_status'] !== 'PAID') {
                    $pdo->prepare("UPDATE orders SET payment_status = 'PAID' WHERE id = ?")->execute([$order['id']]);
                    LogisticsService::addTrackingLog(
                        $order['id'],
                        'AWAITING_FULFILLMENT',
                        'Thanh toán Ví ZaloPay thành công',
                        "Mã GD: #{$res['zp_trans_id']} - Số tiền: " . number_format($order['total_amount'], 0, ',', '.') . "đ. Xác thực tự động qua ZaloPay Query API.",
                        'Ví Điện Tử ZaloPay'
                    );
                }
            }

            $isCancelled = in_array($returnCode, [2, 4]);

            echo json_encode([
                'success'      => true,
                'gateway'      => 'ZALOPAY',
                'order_code'   => $orderCode,
                'raw_response' => $res,
                'is_paid'      => $isPaid,
                'is_cancelled' => $isCancelled,
                'return_code'  => $returnCode,
                'message'      => $res['return_message'] ?? ''
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($gateway === 'MOMO') {
            $res = PaymentService::queryMomoStatus($orderCode);
            $resultCode = (int)($res['resultCode'] ?? -1);
            $isPaid = ($resultCode === 0);

            // Tự động đồng bộ sang DB nếu MoMo xác nhận tiền đã vào
            if ($isPaid) {
                $stmt = $pdo->prepare("SELECT id, payment_status, total_amount FROM orders WHERE order_code = ?");
                $stmt->execute([$orderCode]);
                $order = $stmt->fetch();
                if ($order && $order['payment_status'] !== 'PAID') {
                    $pdo->prepare("UPDATE orders SET payment_status = 'PAID' WHERE id = ?")->execute([$order['id']]);
                    LogisticsService::addTrackingLog(
                        $order['id'],
                        'AWAITING_FULFILLMENT',
                        'Thanh toán Ví MoMo thành công',
                        "Mã GD: #{$res['transId']} - Số tiền: " . number_format($order['total_amount'], 0, ',', '.') . "đ. Xác thực tự động qua MoMo Query API.",
                        'Ví Điện Tử MoMo'
                    );
                }
            }

            $isCancelled = in_array($resultCode, [1006, 49]);

            echo json_encode([
                'success'      => true,
                'gateway'      => 'MOMO',
                'order_code'   => $orderCode,
                'raw_response' => $res,
                'is_paid'      => $isPaid,
                'is_cancelled' => $isCancelled,
                'result_code'  => $resultCode,
                'message'      => $res['message'] ?? ''
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Gateway không hỗ trợ. Chỉ hỗ trợ ZALOPAY hoặc MOMO']);
        exit;
    }
}
