<?php
namespace App\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\AuthService;
use App\Services\EncryptionService;
use App\Services\PaymentService;

class CheckoutController {
    public function index() {
        AuthService::startSession();
        $currentUser = AuthService::user();
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            header('Location: index.php');
            exit;
        }

        $totalAmount = 0;
        $shopId = null;
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
            $shopId = $item['shop_id']; // Đơn hàng theo gian hàng
        }

        // Lấy danh sách thẻ đã Token hóa của người dùng (nếu đã đăng nhập)
        $paymentTokens = [];
        if ($currentUser) {
            $paymentTokens = PaymentService::getUserTokens($currentUser['id']);
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!\App\Services\CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $error = 'Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn (CSRF Token mismatch).';
            } else {
                $name    = trim($_POST['customer_name'] ?? '');
                $phone   = trim($_POST['phone'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $payment = $_POST['payment_method'] ?? 'COD';

                if (empty($name) || empty($phone) || empty($address)) {
                    $error = 'Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ nhận hàng!';
                } else {
                $orderId = EncryptionService::generateUuid();
                $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
                $buyerId = $currentUser ? $currentUser['id'] : 'usr-buyer-001';

                $carrier = trim($_POST['shipping_carrier'] ?? 'GHN Express - Tiêu Chuẩn');
                $shippingFee = 28000;
                if (strpos($carrier, 'Hỏa Tốc') !== false || strpos($carrier, 'TP.HCM') !== false) {
                    $shippingFee = 22000;
                } elseif (strpos($carrier, 'Tiết Kiệm') !== false) {
                    $shippingFee = 18000;
                }

                $paymentStatus = 'PENDING';
                if ($payment === 'TOKENIZATION_CARD') {
                    // Thực thi 1-Click Charge qua Tokenization API
                    $selectedToken = $_POST['selected_token'] ?? '';
                    $chargeResult = PaymentService::executeOneClickCharge($selectedToken, $totalAmount + $shippingFee, $orderCode);
                    if ($chargeResult['success']) {
                        $paymentStatus = 'PAID';
                    }
                }

                $orderData = [
                    'id'                 => $orderId,
                    'order_code'         => $orderCode,
                    'buyer_id'           => $buyerId,
                    'shop_id'            => $shopId ?: 'shp-tech-001',
                    'customer_name'      => $name,
                    'phone'              => $phone,
                    'address'            => $address,
                    'payment_method'     => $payment,
                    'payment_status'     => $paymentStatus,
                    'fulfillment_status' => 'AWAITING_FULFILLMENT',
                    'total_amount'       => $totalAmount + $shippingFee,
                    'shipping_fee'       => $shippingFee,
                    'shipping_carrier'   => $carrier
                ];

                $orderItems = [];
                foreach ($cart as $item) {
                    $orderItems[] = [
                        'product_id'   => $item['product_id'],
                        'product_name' => $item['name'],
                        'price'        => $item['price'],
                        'quantity'     => $item['quantity'],
                        'variant_info' => $item['variant_info'] ?? null
                    ];
                }

                    if (Order::create($orderData, $orderItems)) {
                        $_SESSION['cart'] = [];
                        $_SESSION['last_order_id'] = $orderId;

                        if ($payment === 'VNPAY') {
                            $vnpayUrl = PaymentService::createVnpayPaymentUrl($orderCode, $totalAmount + $shippingFee);
                            if ($vnpayUrl) {
                                header("Location: " . $vnpayUrl);
                                exit;
                            }
                        }
                        if ($payment === 'MOMO') {
                            $momoUrl = PaymentService::createMomoPaymentUrl($orderCode, $totalAmount + $shippingFee);
                            if ($momoUrl) {
                                header("Location: " . $momoUrl);
                                exit;
                            }
                        }
                        if ($payment === 'ZALOPAY') {
                            $zaloData = PaymentService::createZaloPayPaymentUrl($orderCode, $totalAmount + $shippingFee);
                            if ($zaloData) {
                                $_SESSION['zalopay_' . $orderId] = $zaloData;
                                header("Location: index.php?page=zalopay-gateway&id=" . $orderId);
                                exit;
                            }
                        }
                        if ($payment === 'PAYPAL') {
                            $paypalUrl = PaymentService::createPaypalPaymentUrl($orderCode, $totalAmount + $shippingFee);
                            if ($paypalUrl) {
                                header("Location: " . $paypalUrl);
                                exit;
                            }
                        }
                        if ($payment === 'COD') {
                            header("Location: index.php?page=order-tracking&id=" . $orderId . "&msg=success");
                            exit;
                        }

                        // Nếu chọn cổng thanh toán online nhưng API đối tác bảo trì / không phản hồi, mở cổng quét QR dự phòng
                        header("Location: index.php?page=zalopay-gateway&id=" . $orderId . "&msg=fallback");
                        exit;
                    } else {
                        $error = 'Đã có lỗi xảy ra trong quá trình khởi tạo đơn hàng. Vui lòng thử lại!';
                    }
                }
            }
        }

        require __DIR__ . '/../../views/buyer/checkout.php';
    }
}
