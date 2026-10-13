<?php
namespace App\Controllers;

use App\Models\Database;
use App\Models\Shop;
use App\Services\AuthService;
use App\Services\CsrfService;
use App\Services\EncryptionService;
use App\Services\OtpService;
use App\Services\RateLimitService;

class AuthController {
    /**
     * Giao diện Đăng nhập hợp nhất đa phương thức (Password, OTP, Google SSO, Passkey)
     */
    public function login() {
        AuthService::startSession();
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authMethod = $_POST['auth_method'] ?? 'password';

            if (!CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $error = 'Yêu cầu không hợp lệ hoặc phiên đăng nhập đã hết hạn (CSRF Token mismatch).';
            } elseif ($authMethod === 'password') {
                // Đăng nhập bằng Mật khẩu
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                $result = AuthService::login($username, $password);
                if ($result['success']) {
                    $this->handleLoginSuccess();
                } else {
                    $error = $result['error'];
                }
            } elseif ($authMethod === 'otp') {
                // Đăng nhập bằng OTP
                $recipient = trim($_POST['otp_recipient'] ?? '');
                $code = trim($_POST['otp_code'] ?? '');

                if (empty($recipient) || empty($code)) {
                    $error = 'Vui lòng nhập đầy đủ Số điện thoại/Email và Mã xác thực OTP (6 chữ số).';
                } else {
                    $result = AuthService::loginByOtp($recipient, $code);
                    if ($result['success']) {
                        $this->handleLoginSuccess();
                    } else {
                        $error = $result['error'];
                    }
                }
            } elseif ($authMethod === 'google') {
                // Đăng nhập qua Google SSO
                $googleEmail = trim($_POST['google_email'] ?? '');
                $googleName = trim($_POST['google_name'] ?? '');
                $googleAvatar = trim($_POST['google_avatar'] ?? '');

                if (empty($googleEmail)) {
                    $error = 'Xác thực Google không thành công. Không tìm thấy thông tin email tài khoản.';
                } else {
                    $result = AuthService::loginByGoogle($googleEmail, $googleName, $googleAvatar);
                    if ($result['success']) {
                        $this->handleLoginSuccess();
                    } else {
                        $error = $result['error'];
                    }
                }
            } elseif ($authMethod === 'passkey') {
                // Đăng nhập bằng Passkey / FaceID
                $passkeyUser = trim($_POST['passkey_user'] ?? '');
                if (empty($passkeyUser)) {
                    $error = 'Chưa chọn tài khoản xác thực bằng Passkey.';
                } else {
                    $result = AuthService::loginByPasskey($passkeyUser);
                    if ($result['success']) {
                        $this->handleLoginSuccess();
                    } else {
                        $error = $result['error'];
                    }
                }
            }
        }

        require __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Endpoint API Gửi mã OTP (AJAX hoặc Form submit)
     */
    public function sendOtp() {
        AuthService::startSession();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Phương thức không hợp lệ']);
            exit;
        }

        $recipient = trim($_POST['recipient'] ?? '');
        $channel = trim($_POST['channel'] ?? 'SMS');

        if (empty($recipient)) {
            echo json_encode(['success' => false, 'error' => 'Vui lòng nhập số điện thoại hoặc email nhận mã.']);
            exit;
        }

        // Kiểm tra chống spam gửi OTP
        $lockMsg = RateLimitService::isLocked('send_otp');
        if ($lockMsg) {
            echo json_encode(['success' => false, 'error' => $lockMsg]);
            exit;
        }

        $res = OtpService::generateOtp($recipient, $channel);
        echo json_encode([
            'success'     => true,
            'recipient'   => $recipient,
            'channel'     => $channel,
            'expires_in'  => $res['expires_in'],
            'demo_code'   => $res['code'], // Trả về để UI hiển thị thông báo SMS mô phỏng thực tế
            'message'     => "Mã xác thực OTP 6 chữ số đã được gửi tới {$recipient}."
        ]);
        exit;
    }

    /**
     * Quên mật khẩu - Gửi mã liên kết khôi phục
     */
    public function forgotPassword() {
        AuthService::startSession();
        $error = '';
        $success = '';
        $resetUrl = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $error = 'Yêu cầu không hợp lệ (CSRF Token mismatch).';
            } else {
                $email = trim($_POST['email'] ?? '');
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Địa chỉ email không đúng định dạng!';
                } else {
                    $res = AuthService::createPasswordResetToken($email);
                    $success = 'Đã gửi liên kết khôi phục mật khẩu. Vui lòng kiểm tra hộp thư (hiệu lực trong 15 phút).';
                    if (!empty($res['reset_url'])) {
                        $resetUrl = $res['reset_url'];
                    }
                }
            }
        }

        require __DIR__ . '/../../views/auth/forgot_password.php';
    }

    /**
     * Đặt lại mật khẩu mới
     */
    public function resetPassword() {
        AuthService::startSession();
        $error = '';
        $success = '';

        $email = trim($_GET['email'] ?? $_POST['email'] ?? '');
        $token = trim($_GET['token'] ?? $_POST['token'] ?? '');

        if (empty($email) || empty($token)) {
            $error = 'Liên kết khôi phục mật khẩu không hợp lệ hoặc thiếu tham số.';
        } elseif (!AuthService::verifyPasswordResetToken($email, $token)) {
            $error = 'Mã token khôi phục không hợp lệ hoặc đã hết thời hạn hiệu lực (quá 15 phút).';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
            if (!CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $error = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            } else {
                $newPassword = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if ($newPassword !== $confirmPassword) {
                    $error = 'Xác nhận mật khẩu mới không trùng khớp!';
                } elseif (strlen($newPassword) < 12) {
                    $error = 'Chính sách bảo mật: Mật khẩu mới phải có độ dài tối thiểu từ 12 ký tự trở lên!';
                } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\\\|,.<>\/?~`]/', $newPassword)) {
                    $error = 'Chính sách bảo mật: Mật khẩu mới bắt buộc phải chứa ít nhất 1 chữ hoa, 1 chữ thường, 1 chữ số và 1 ký tự đặc biệt!';
                } else {
                    $res = AuthService::resetPassword($email, $token, $newPassword);
                    if ($res['success']) {
                        header('Location: index.php?page=login&notice=password_reset_success');
                        exit;
                    } else {
                        $error = $res['error'];
                    }
                }
            }
        }

        require __DIR__ . '/../../views/auth/reset_password.php';
    }

    /**
     * Đăng ký tài khoản (KHÔNG tự động đăng nhập - Chuẩn sàn TMĐT)
     */
    public function register() {
        AuthService::startSession();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $error = 'Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn (CSRF Token mismatch).';
            } else {
                $username = trim($_POST['username'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $fullName = trim($_POST['full_name'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');
                $address  = trim($_POST['address'] ?? '');

                if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
                    $error = 'Vui lòng điền đầy đủ các thông tin bắt buộc!';
                } elseif (strlen($password) < 12) {
                    $error = 'Chính sách bảo mật: Mật khẩu phải có độ dài tối thiểu từ 12 ký tự trở lên!';
                } elseif (!preg_match('/[A-Z]/', $password)) {
                    $error = 'Chính sách bảo mật: Mật khẩu phải chứa ít nhất 1 chữ cái in hoa (A-Z)!';
                } elseif (!preg_match('/[a-z]/', $password)) {
                    $error = 'Chính sách bảo mật: Mật khẩu phải chứa ít nhất 1 chữ cái thường (a-z)!';
                } elseif (!preg_match('/[0-9]/', $password)) {
                    $error = 'Chính sách bảo mật: Mật khẩu phải chứa ít nhất 1 chữ số (0-9)!';
                } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\\\|,.<>\/?~`]/', $password)) {
                    $error = 'Chính sách bảo mật: Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt (!@#$%^&*...)!';
                } else {
                    $pdo = Database::getInstance();
                    $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $chk->execute([$username, $email]);

                    if ($chk->fetch()) {
                        $error = 'Tên tài khoản hoặc Email này đã được sử dụng!';
                    } else {
                        $userId = EncryptionService::generateUuid();
                        $pwdHash = EncryptionService::hashPassword($password);
                        $encPhone = EncryptionService::encryptField($phone);
                        $encAddress = EncryptionService::encryptField($address);

                        // Mặc định đăng ký là Người Dùng Sàn (BUYER - Có thể mở Shop bất cứ lúc nào)
                        $stmt = $pdo->prepare("INSERT INTO users (id, username, email, password_hash, full_name, role, phone, address) VALUES (?, ?, ?, ?, ?, 'BUYER', ?, ?)");
                        $stmt->execute([$userId, $username, $email, $pwdHash, $fullName, $encPhone, $encAddress]);

                        // Chuyển hướng tới trang Đăng nhập với thông báo đăng ký thành công (Không tự động đăng nhập)
                        header('Location: index.php?page=login&notice=registered_success');
                        exit;
                    }
                }
            }
        }

        require __DIR__ . '/../../views/auth/register.php';
    }

    public function logout() {
        AuthService::logout();
        header('Location: index.php');
        exit;
    }

    private function handleLoginSuccess() {
        $user = AuthService::user();
        $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';

        if ($redirect && preg_match('/^[a-zA-Z0-9_\-]+$/', $redirect)) {
            header('Location: index.php?page=' . $redirect);
        } elseif ($user && $user['role'] === 'ADMIN') {
            header('Location: index.php?page=admin');
        } elseif ($user && $user['role'] === 'SELLER') {
            header('Location: index.php?page=seller');
        } else {
            header('Location: index.php');
        }
        exit;
    }
}
