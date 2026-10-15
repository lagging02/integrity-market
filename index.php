<?php
/**
 * ======================================================================
 * NỀN TẢNG THƯƠNG MẠI ĐIỆN TỬ B2C LAI TẠO (HORIZONTAL MARKETPLACE)
 * Đồ án An Toàn Thương Mại Điện Tử (ATTMDT 2026 - Nhóm 11)
 * Kiến trúc: Clean MVC (Model - View - Controller), Service Layer, OOP
 * Tiêu chuẩn: OWASP Top 10, PCI-DSS Level 1 Tokenization, 3PL State Machine
 * ======================================================================
 */

// 0. Xóa hoàn toàn chữ ký công nghệ Backend & thiết lập Security Headers
header_remove("X-Powered-By");
header_remove("Server");
header_remove("X-Backend-Server");
header_remove("X-Load-Balancer");

// Chống SSL Strip & Downgrade Attack: Bắt buộc chuyển hướng HTTPS khi truy cập qua Domain hoặc IP mạng ngoài
$isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

if (!$isHttps && !empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false && strpos($_SERVER['HTTP_HOST'], 'localhost') === false) {
    $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . ($_SERVER['REQUEST_URI'] ?? '/');
    header("Location: " . $redirectUrl, true, 301);
    exit;
}



// 1. Khởi tạo Autoloader chuẩn PSR-4 cho namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. Khởi tạo phiên làm việc bảo mật
\App\Services\AuthService::startSession();

// 3. Front Controller Router Dispatcher
$page = $_GET['page'] ?? 'home';

switch ($page) {
    // --- BUYER STOREFRONT (TRANG MUA SẮM) ---
    case 'home':
        (new \App\Controllers\HomeController())->index();
        break;

    case 'privacy-policy':
        (new \App\Controllers\HomeController())->privacyPolicy();
        break;

    case 'terms':
        (new \App\Controllers\HomeController())->terms();
        break;

    case 'shipping-policy':
        (new \App\Controllers\HomeController())->shippingPolicy();
        break;

    case 'return-policy':
        (new \App\Controllers\HomeController())->returnPolicy();
        break;

    case 'product':
        (new \App\Controllers\ProductController())->detail();
        break;

    case 'cart':
        (new \App\Controllers\CartController())->view();
        break;

    case 'cart-add':
        (new \App\Controllers\CartController())->add();
        break;

    case 'cart-remove':
        (new \App\Controllers\CartController())->remove();
        break;

    case 'cart-clear':
        (new \App\Controllers\CartController())->clear();
        break;

    case 'checkout':
        (new \App\Controllers\CheckoutController())->index();
        break;

    case 'payment-pending':
        (new \App\Controllers\OrderController())->pending();
        break;

    case 'check-payment-status':
        (new \App\Controllers\OrderController())->checkPaymentStatus();
        break;

    case 'confirm-pending-payment':
        (new \App\Controllers\OrderController())->confirmPendingPayment();
        break;

    case 'order-success':
        (new \App\Controllers\OrderController())->success();
        break;

    case 'order-tracking':
        (new \App\Controllers\OrderController())->tracking();
        break;

    case 'simulate-delivery':
        (new \App\Controllers\OrderController())->simulateDelivery();
        break;

    case 'ghn-webhook':
        (new \App\Controllers\OrderController())->ghnWebhook();
        break;

    case 'retry-payment':
        (new \App\Controllers\OrderController())->retryPayment();
        break;

    case 'my-orders':
        (new \App\Controllers\OrderController())->myOrders();
        break;

    // --- SELLER CENTRE (KÊNH NGƯỜI BÁN & LOGISTICS 3PL) ---
    case 'seller':
        (new \App\Controllers\SellerController())->dashboard();
        break;

    case 'seller-products':
        (new \App\Controllers\SellerController())->products();
        break;

    case 'seller-add-product':
        (new \App\Controllers\SellerController())->addProduct();
        break;

    case 'seller-orders':
        (new \App\Controllers\SellerController())->orders();
        break;

    case 'shipping-label':
        (new \App\Controllers\SellerController())->shippingLabel();
        break;

    case 'seller-register':
        (new \App\Controllers\SellerController())->registerShop();
        break;

    // --- ADMIN CONTROL PANEL & PHÒNG KIỂM THỬ TẤN CÔNG BẢO MẬT ---
    case 'admin':
    case 'admin_dashboard':
    case 'admin-dashboard':
        (new \App\Controllers\AdminController())->dashboard();
        break;

    case 'admin-security':
    case 'admin_security':
    case 'admin-audit':
    case 'admin_audit':
        (new \App\Controllers\AdminController())->securityAudit();
        break;

    case 'security-lab':
        (new \App\Controllers\SecurityLabController())->index();
        break;

    case 'run-attack':
        (new \App\Controllers\SecurityLabController())->runAttack();
        break;

    case 'run-test-suite':
        (new \App\Controllers\SecurityLabController())->runTestSuite();
        break;

    // --- PAYMENT GATEWAYS & HMAC WEBHOOKS ---
    case 'payment-webhook':
        (new \App\Controllers\PaymentController())->webhook();
        break;

    case 'vnpay-return':
        (new \App\Controllers\PaymentController())->vnpayReturn();
        break;

    case 'momo-return':
        (new \App\Controllers\PaymentController())->momoReturn();
        break;

    case 'zalopay-gateway':
        (new \App\Controllers\PaymentController())->zalopayGateway();
        break;

    case 'zalopay-return':
        (new \App\Controllers\PaymentController())->zalopayReturn();
        break;

    case 'paypal-return':
        (new \App\Controllers\PaymentController())->paypalReturn();
        break;

    case 'simulate-webhook':
        (new \App\Controllers\PaymentController())->simulateWebhook();
        break;

    case 'api-check-gateway':
        (new \App\Controllers\PaymentController())->checkGatewayStatus();
        break;

    // --- AUTHENTICATION & MULTI-METHOD LOGIN ---
    case 'login':
        (new \App\Controllers\AuthController())->login();
        break;

    case 'send-otp':
        (new \App\Controllers\AuthController())->sendOtp();
        break;

    case 'forgot-password':
        (new \App\Controllers\AuthController())->forgotPassword();
        break;

    case 'reset-password':
        (new \App\Controllers\AuthController())->resetPassword();
        break;

    case 'register':
        (new \App\Controllers\AuthController())->register();
        break;

    case 'logout':
        (new \App\Controllers\AuthController())->logout();
        break;

    case '404':
    default:
        http_response_code(404);
        header("HTTP/1.1 404 Not Found");
        require __DIR__ . '/views/errors/404.php';
        break;
}
