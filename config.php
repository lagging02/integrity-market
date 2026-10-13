<?php
// Cấu hình kết nối cơ sở dữ liệu MySQL an toàn
$host = 'localhost';
$db   = 'shop_db';
$user = 'shop_user';
$pass = 'Pass@123456';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Buộc dùng prepared statements thật của MySQL (chống SQLi)
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Lỗi kết nối cơ sở dữ liệu an toàn. Vui lòng liên hệ quản trị viên hệ thống.");
}

if (session_status() === PHP_SESSION_NONE) {
    // Cấu hình cookie session an toàn chống đánh cắp phiên (Burp Suite Session Hijacking)
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_set_cookie_params([
        'lifetime' => 86400,
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ============================================================
// HỆ THỐNG MÃ HÓA CƠ SỞ DỮ LIỆU (DATABASE FIELD ENCRYPTION)
// Chuẩn mã hóa quân sự: AES-256-CBC
// Đáp ứng tiêu chuẩn: Bảo mật cơ sở dữ liệu Web (Bảo vệ thông tin cá nhân PII)
// ============================================================
define('DB_ENCRYPT_KEY', 'ATTMDT_Shop_Security_Key_2026!#$'); // Khóa bí mật 32-bit

/**
 * Mã hóa dữ liệu trước khi INSERT vào MySQL
 */
function encrypt_field($plaintext) {
    if (empty($plaintext)) return '';
    $cipher = "AES-256-CBC";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, DB_ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv);
    // Ghép IV và Ciphertext thành chuỗi Base64 lưu vào database
    return 'ENC:' . base64_encode($iv . $ciphertext_raw);
}

/**
 * Giải mã dữ liệu khi hiển thị trên trang Quản trị (Admin)
 */
function decrypt_field($ciphertext_base64) {
    if (empty($ciphertext_base64)) return '';
    if (strpos($ciphertext_base64, 'ENC:') !== 0) {
        return $ciphertext_base64; // Dữ liệu cũ chưa mã hóa
    }
    $raw_data = base64_decode(substr($ciphertext_base64, 4));
    $cipher = "AES-256-CBC";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($raw_data, 0, $ivlen);
    $ciphertext_raw = substr($raw_data, $ivlen);
    return openssl_decrypt($ciphertext_raw, $cipher, DB_ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv);
}
?>
