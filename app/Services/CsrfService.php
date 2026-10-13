<?php
namespace App\Services;

class CsrfService {
    /**
     * Khởi tạo và lấy token CSRF an toàn theo chuẩn mật mã học
     */
    public static function getToken() {
        AuthService::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Tạo thẻ input ẩn chứa CSRF Token để chèn vào các form POST
     */
    public static function field() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Xác thực CSRF Token bằng so sánh thời gian không đổi (hash_equals chống Timing Attack)
     */
    public static function validateToken($token) {
        AuthService::startSession();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
