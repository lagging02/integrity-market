<?php
// ======================================================================
// CẤU HÌNH KẾT NỐI CƠ SỞ DỮ LIỆU CHUẨN DOANH NGHIỆP
// Sử dụng PDO Prepared Statements bắt buộc (Chống SQL Injection theo OWASP A03)
// ======================================================================

return [
    'host'     => '127.0.0.1',
    'database' => 'shop_db',
    'username' => 'shop_user',
    'password' => 'Pass@123456',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Buộc dùng prepared statements thật của MySQL
    ]
];
