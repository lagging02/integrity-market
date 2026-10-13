<?php
// ======================================================================
// CẤU HÌNH BẢO MẬT HỆ THỐNG (OWASP TOP 10 & PCI-DSS SAQ-A)
// ======================================================================

return [
    // Khóa mã hóa đối xứng AES-256 (32 bytes) cho PII (Phone, Address, Identity)
    'db_encryption_key' => 'ATTMDT_Enterprise_AES256_SecKey!',
    
    // Thuật toán mã hóa PII
    'cipher_algorithm'  => 'AES-256-CBC',

    // Cấu hình Session bảo mật chống Session Hijacking & XSS Cookie Theft
    'session' => [
        'lifetime' => 86400,
        'httponly' => true,
        'secure'   => true,
        'samesite' => 'Lax'
    ],

    // Cấu hình Cổng Thanh Toán Tokenization (PCI-DSS)
    'payment_gateways' => [
        'stripe' => [
            'merchant_id' => 'MERCHANT_PROD_9981',
            'public_key'  => 'pk_test_51MzXYZ1234567890',
        ],
        'vietqr' => [
            'bank_id'      => 'MB',
            'account_no'   => '0359876543',
            'account_name' => 'NGUYEN VAN A'
        ]
    ]
];
