<?php
/**
 * CẤU HÌNH CỔNG THANH TOÁN BÊN THỨ 3 (SANDBOX & DEV PORTAL)
 * Đồ án An Toàn Thương Mại Điện Tử - Nhóm 11
 * Hỗ trợ: VNPAY Sandbox, PayOS VietQR, MoMo Developer Sandbox
 */

return [
    // =========================================================================
    // 1. CỔNG THANH TOÁN VNPAY SANDBOX (Chuẩn đồ án Đại học & Ngân hàng Nhà nước)
    // Cổng đăng ký Merchant Sandbox miễn phí: https://sandbox.vnpayment.vn/devreg/
    // Thẻ test NCB: 9704198526191432198 | Tên: NGUYEN VAN A | Ngày: 07/15 | OTP: 123456
    // =========================================================================
    'vnpay' => [
        'enabled'      => true,
        'tmn_code'     => 'I25YOYNN',
        'hash_secret'  => 'CZXUZBJEIEXPSXUTFNMJKSVSOJQJSQZM',
        'payment_url'  => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
        'return_url'   => 'http://192.168.1.54:8080/index.php?page=vnpay-return',
        'api_url'      => 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction',
    ],

    // =========================================================================
    // 2. VÍ ĐIỆN TỬ ZALOPAY DEVELOPER SANDBOX (Chuẩn ZaloPay API v2)
    // =========================================================================
    'zalopay' => [
        'enabled'      => true,
        'app_id'       => 2553,
        'key1'         => 'PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL',
        'key2'         => 'kLtgPl8YESDmyABkQgeZByOUJsbcpNI2',
        'endpoint'     => 'https://sb-openapi.zalopay.vn/v2/create',
        'return_url'   => 'http://192.168.1.54:8080/index.php?page=zalopay-return',
        'notify_url'   => 'http://192.168.1.54:8080/index.php?page=payment-webhook',
    ],

    // =========================================================================
    // 3. CỔNG THANH TOÁN MOMO DEVELOPER SANDBOX
    // Mã nguồn chuẩn chính thức từ GitHub: https://github.com/momo-wallet/payment
    // =========================================================================
    'momo' => [
        'enabled'      => true,
        'partner_code' => 'MOMOBKUN20180529',
        'access_key'   => 'klm05TvNBzhg7h7j',
        'secret_key'   => 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa',
        'endpoint'     => 'https://test-payment.momo.vn/v2/gateway/api/create',
        'return_url'   => 'http://192.168.1.54:8080/index.php?page=momo-return',
        'notify_url'   => 'http://192.168.1.54:8080/index.php?page=payment-webhook',
    ],

    // =========================================================================
    // 4. CỔNG THANH TOÁN QUỐC TẾ PAYPAL SANDBOX (OAuth2 & Smart Checkout)
    // =========================================================================
    'paypal' => [
        'enabled'      => true,
        'client_id'    => 'BAArLb0vA72Dxn_qLjcs5mNJL3hFeidO7s_SKCpHCKVqtqXRsLASuEaQJSd9GOZT1w7pyEc1MhYqErsO1A',
        'secret'       => 'EBQXD_o31UAYjbQIg9uepc0yExF9d80JXxlhR-k5G5TBh6KRCT8yXTfq9NLY81JLgljTm8IqNpETB4Zl',
        'mode'         => 'sandbox',
        'currency'     => 'USD',
        'exchange_rate'=> 25000, // 25.000 VND = 1 USD
        'return_url'   => 'http://192.168.1.54:8080/index.php?page=paypal-return',
        'cancel_url'   => 'http://192.168.1.54:8080/index.php?page=cart',
    ]
];
