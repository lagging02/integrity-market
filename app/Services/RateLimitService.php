<?php
namespace App\Services;

use App\Models\Database;
use PDO;

class RateLimitService {
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900; // 15 phút

    public static function getClientIp() {
        return $_SERVER['HTTP_CF_CONNECTING_IP'] 
            ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? '127.0.0.1';
    }

    /**
     * Kiểm tra xem IP hoặc tài khoản có đang bị khóa do thử sai quá nhiều không (Kiểm tra tầng Database CSDL)
     */
    public static function isLocked($action = 'login', $username = '') {
        AuthService::startSession();
        $ip = self::getClientIp();

        try {
            $pdo = Database::getInstance();
            
            // 1. Kiểm tra khóa theo IP trong 15 phút gần nhất
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as failed_count, TIMESTAMPDIFF(SECOND, MAX(attempted_at), NOW()) as elapsed_seconds
                FROM login_attempts 
                WHERE ip_address = ? 
                  AND is_success = 0 
                  AND attempted_at >= NOW() - INTERVAL 15 MINUTE
            ");
            $stmt->execute([$ip]);
            $ipData = $stmt->fetch();
            $failedIpCount = (int)($ipData['failed_count'] ?? 0);

            if ($failedIpCount >= self::MAX_ATTEMPTS) {
                $elapsed = (int)($ipData['elapsed_seconds'] ?? 0);
                $remaining = max(1, ceil((self::LOCKOUT_SECONDS - $elapsed) / 60));
                return "Cảnh báo An ninh (OWASP A07): Địa chỉ IP [$ip] đã thử đăng nhập thất bại {$failedIpCount} lần. Hệ thống tự động khóa bảo vệ trong {$remaining} phút để phòng chống Brute-force Attack.";
            }

            // 2. Kiểm tra khóa theo Username/Tài khoản cụ thể (Chống tấn công Brute-force phân tán từ nhiều IP)
            if (!empty($username)) {
                $stmtUser = $pdo->prepare("
                    SELECT COUNT(*) as failed_count, TIMESTAMPDIFF(SECOND, MAX(attempted_at), NOW()) as elapsed_seconds
                    FROM login_attempts 
                    WHERE username = ? 
                      AND is_success = 0 
                      AND attempted_at >= NOW() - INTERVAL 15 MINUTE
                ");
                $stmtUser->execute([$username]);
                $userData = $stmtUser->fetch();
                $failedUserCount = (int)($userData['failed_count'] ?? 0);

                if ($failedUserCount >= self::MAX_ATTEMPTS) {
                    $elapsed = (int)($userData['elapsed_seconds'] ?? 0);
                    $remaining = max(1, ceil((self::LOCKOUT_SECONDS - $elapsed) / 60));
                    return "Tài khoản [$username] đang bị tấn công dò quét mật khẩu (Thử sai {$failedUserCount} lần). Hệ thống tạm thời phong tỏa tài khoản trong {$remaining} phút để bảo vệ tài sản của bạn.";
                }
            }
        } catch (\Exception $e) {
            // Fallback nếu có sự cố DB: dùng Session Rate Limiting
            $key = 'rate_limit_' . $action . '_' . md5($ip);
            if (isset($_SESSION[$key])) {
                $data = $_SESSION[$key];
                if ($data['count'] >= self::MAX_ATTEMPTS) {
                    $elapsed = time() - $data['last_attempt'];
                    if ($elapsed < self::LOCKOUT_SECONDS) {
                        $remaining = ceil((self::LOCKOUT_SECONDS - $elapsed) / 60);
                        return "Bạn đã thử sai quá " . self::MAX_ATTEMPTS . " lần. Hệ thống tự động khóa bảo vệ trong $remaining phút.";
                    }
                }
            }
        }

        return false;
    }

    /**
     * Ghi nhận 1 lần thử sai vào Database & Session
     */
    public static function recordFailedAttempt($action = 'login', $username = '') {
        AuthService::startSession();
        $ip = self::getClientIp();

        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, username, is_success) VALUES (?, ?, 0)");
            $stmt->execute([$ip, $username ?: 'unknown']);
        } catch (\Exception $e) {
            // Không làm gián đoạn nếu DB lỗi
        }

        // Cập nhật Session dự phòng
        $key = 'rate_limit_' . $action . '_' . md5($ip);
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'last_attempt' => time()
            ];
        } else {
            $_SESSION[$key]['count']++;
            $_SESSION[$key]['last_attempt'] = time();
        }
    }

    /**
     * Xóa bộ đếm và ghi nhận thành công khi đăng nhập thành công
     */
    public static function clear($action = 'login', $username = '') {
        AuthService::startSession();
        $ip = self::getClientIp();

        try {
            $pdo = Database::getInstance();
            // Ghi nhận phiên đăng nhập thành công
            if (!empty($username)) {
                $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, username, is_success) VALUES (?, ?, 1)");
                $stmt->execute([$ip, $username]);
            }
            // Xóa các lần thử sai trước đó của IP này
            $delStmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ? AND is_success = 0");
            $delStmt->execute([$ip]);
        } catch (\Exception $e) {
            // Bỏ qua nếu lỗi
        }

        $key = 'rate_limit_' . $action . '_' . md5($ip);
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}
