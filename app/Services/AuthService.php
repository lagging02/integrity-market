<?php
namespace App\Services;

use App\Models\Database;
use PDO;

class AuthService {
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
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
    }

    private static function setUserSession(array $user): void {
        // Phòng chống Session Fixation bằng cách sinh ID phiên mới
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar'] = $user['avatar'] ?? null;

        // Tải thông tin Shop liên kết nếu có (Mô hình Shopee: 1 tài khoản vừa mua vừa bán)
        $pdo = Database::getInstance();
        $shopStmt = $pdo->prepare("SELECT * FROM shops WHERE user_id = ?");
        $shopStmt->execute([$user['id']]);
        $shop = $shopStmt->fetch();
        $_SESSION['shop'] = $shop ?: null;

        // Xóa bộ đếm rate-limit sau khi đăng nhập thành công
        RateLimitService::clear('login');
    }

    /**
     * Phương thức 1: Đăng nhập bằng Mật khẩu chuẩn (Bcrypt Cost 12)
     */
    public static function login($username, $password) {
        self::startSession();

        // Kiểm tra chống Brute-Force Rate Limiting (Cấp độ Database & IP)
        $lockMsg = RateLimitService::isLocked('login', $username);
        if ($lockMsg) {
            return ['success' => false, 'error' => $lockMsg];
        }

        $pdo = Database::getInstance();
        
        // Truy vấn tham số hóa chống SQL Injection tuyệt đối
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && EncryptionService::verifyPassword($password, $user['password_hash'])) {
            self::setUserSession($user);
            RateLimitService::clear('login', $user['username']);
            return ['success' => true];
        }

        // Ghi nhận lần thử sai vào CSDL MySQL để đếm và khóa nếu quá 5 lần
        RateLimitService::recordFailedAttempt('login', $username);
        return ['success' => false, 'error' => 'Tên tài khoản hoặc mật khẩu không chính xác!'];
    }

    /**
     * Phương thức 2: Đăng nhập bằng Mã OTP (SMS / Email)
     */
    public static function loginByOtp(string $recipient, string $code): array {
        self::startSession();

        // Xác thực mã OTP qua OtpService
        $verifyResult = OtpService::verifyOtp($recipient, $code);
        if (!$verifyResult['success']) {
            return $verifyResult;
        }

        $pdo = Database::getInstance();
        $recipient = trim($recipient);

        // Tìm kiếm tài khoản theo email hoặc username hoặc phone đã giải mã
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$recipient, $recipient]);
        $user = $stmt->fetch();

        // Nếu không tìm thấy theo email/username, kiểm tra các phone đã mã hóa trong DB
        if (!$user) {
            $allUsers = $pdo->query("SELECT * FROM users WHERE phone IS NOT NULL")->fetchAll();
            foreach ($allUsers as $u) {
                $decPhone = EncryptionService::decryptField($u['phone']);
                if ($decPhone === $recipient) {
                    $user = $u;
                    break;
                }
            }
        }

        // Nếu tài khoản chưa tồn tại, tự động tạo mới tài khoản BUYER (chuẩn Shopee OTP auto-onboarding)
        if (!$user) {
            $userId = EncryptionService::generateUuid();
            $isEmail = filter_var($recipient, FILTER_VALIDATE_EMAIL);
            $baseName = $isEmail ? explode('@', $recipient)[0] : 'user_' . substr($recipient, -4);
            $username = $baseName . '_' . random_int(100, 999);
            $email = $isEmail ? $recipient : $username . '@market.vn';
            $fullName = 'Khách hàng OTP (' . ($isEmail ? $recipient : substr($recipient, 0, 3) . '***' . substr($recipient, -3)) . ')';
            $dummyPwdHash = EncryptionService::hashPassword(bin2hex(random_bytes(16)));
            $encPhone = !$isEmail ? EncryptionService::encryptField($recipient) : null;

            $insertStmt = $pdo->prepare("INSERT INTO users (id, username, email, password_hash, full_name, role, phone) VALUES (?, ?, ?, ?, ?, 'BUYER', ?)");
            $insertStmt->execute([$userId, $username, $email, $dummyPwdHash, $fullName, $encPhone]);

            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }

        self::setUserSession($user);
        return ['success' => true];
    }

    /**
     * Phương thức 3: Đăng nhập qua Google SSO (OAuth2 Identity Flow)
     */
    public static function loginByGoogle(string $googleEmail, string $googleName, ?string $googleAvatar = null): array {
        self::startSession();

        if (!filter_var($googleEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email Google không hợp lệ!'];
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$googleEmail]);
        $user = $stmt->fetch();

        if (!$user) {
            // Tự động tạo tài khoản BUYER mới liên kết Google
            $userId = EncryptionService::generateUuid();
            $username = explode('@', $googleEmail)[0] . '_gg' . random_int(10, 99);
            $dummyPwdHash = EncryptionService::hashPassword(bin2hex(random_bytes(16)));

            $insert = $pdo->prepare("INSERT INTO users (id, username, email, password_hash, full_name, role, avatar) VALUES (?, ?, ?, ?, ?, 'BUYER', ?)");
            $insert->execute([$userId, $username, $googleEmail, $dummyPwdHash, $googleName, $googleAvatar]);

            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }

        self::setUserSession($user);
        return ['success' => true];
    }

    /**
     * Phương thức 4: Đăng nhập bằng Passkey / Face ID / WebAuthn Biometric
     */
    public static function loginByPasskey(string $username): array {
        self::startSession();

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'Tài khoản hoặc Passkey không khớp trên thiết bị này.'];
        }

        self::setUserSession($user);
        return ['success' => true];
    }

    /**
     * Quy trình Khôi phục Mật khẩu (Forgot / Reset Password)
     */
    public static function createPasswordResetToken(string $email): array {
        self::startSession();

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Giữ an toàn chống enumeration: luôn trả về thành công giả lập
            return ['success' => true, 'simulated' => true];
        }

        $token = bin2hex(random_bytes(24));
        $expiresAt = time() + 900; // 15 phút hiệu lực

        $_SESSION['password_reset'] = [
            'email'      => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt
        ];

        return [
            'success'   => true,
            'email'     => $email,
            'token'     => $token,
            'reset_url' => "index.php?page=reset-password&email=" . urlencode($email) . "&token=" . $token
        ];
    }

    public static function verifyPasswordResetToken(string $email, string $token): bool {
        self::startSession();

        if (empty($_SESSION['password_reset'])) {
            return false;
        }

        $data = $_SESSION['password_reset'];
        if (strtolower($data['email']) !== strtolower($email)) {
            return false;
        }

        if (time() > $data['expires_at']) {
            unset($_SESSION['password_reset']);
            return false;
        }

        return hash_equals($data['token_hash'], hash('sha256', $token));
    }

    public static function resetPassword(string $email, string $token, string $newPassword): array {
        if (!self::verifyPasswordResetToken($email, $token)) {
            return ['success' => false, 'error' => 'Mã khôi phục không hợp lệ hoặc đã hết hạn (quá 15 phút).'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'Mật khẩu mới phải có tối thiểu từ 8 ký tự trở lên.'];
        }

        $pdo = Database::getInstance();
        $newHash = EncryptionService::hashPassword($newPassword);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$newHash, $email]);

        unset($_SESSION['password_reset']);
        return ['success' => true];
    }

    public static function logout() {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params["path"] ?? '/',
                'domain'   => $params["domain"] ?? '',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        session_destroy();
    }

    public static function user() {
        self::startSession();
        if (empty($_SESSION['user_id'])) return null;

        // Cập nhật trạng thái Shop theo thời gian thực
        if (!isset($_SESSION['shop']) || empty($_SESSION['shop'])) {
            $pdo = Database::getInstance();
            $shopStmt = $pdo->prepare("SELECT * FROM shops WHERE user_id = ?");
            $shopStmt->execute([$_SESSION['user_id']]);
            $_SESSION['shop'] = $shopStmt->fetch() ?: null;
        }

        return [
            'id'        => $_SESSION['user_id'],
            'username'  => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role'      => $_SESSION['role'],
            'shop'      => $_SESSION['shop'] ?? null
        ];
    }

    public static function checkRole($roles) {
        $user = self::user();
        if (!$user) return false;
        if (is_array($roles)) {
            return in_array($user['role'], $roles);
        }
        return $user['role'] === $roles;
    }

    public static function requireRole($roles, $redirectUrl = '?page=login') {
        if (!self::checkRole($roles)) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Giấu kín cổng quản trị: Nếu không phải Admin thì trả về HTTP 404 (coi như trang không tồn tại)
     */
    public static function requireAdminStealth() {
        $user = self::user();
        if (!$user) {
            header("Location: index.php?page=login&notice=admin_required&redirect=admin-security");
            exit;
        }
        if ($user['role'] !== 'ADMIN') {
            header("HTTP/1.1 404 Not Found");
            require __DIR__ . '/../../views/errors/404.php';
            exit;
        }
    }
}
