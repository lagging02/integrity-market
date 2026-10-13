<?php
namespace App\Services;

class OtpService {
    const OTP_EXPIRY_SECONDS = 180; // 3 phút hiệu lực
    const MAX_ATTEMPTS = 3;         // Tối đa 3 lần nhập sai

    public static function generateOtp(string $recipient, string $channel = 'SMS'): array {
        AuthService::startSession();

        // Tạo mã OTP ngẫu nhiên 6 chữ số an toàn
        $otpCode = (string) random_int(100000, 999999);
        $expiresAt = time() + self::OTP_EXPIRY_SECONDS;

        $_SESSION['otp_auth'] = [
            'recipient'  => $recipient,
            'channel'    => $channel, // 'SMS' hoặc 'EMAIL'
            'code_hash'  => password_hash($otpCode, PASSWORD_BCRYPT, ['cost' => 10]),
            'plain_code' => $otpCode, // Lưu tạm để mô phỏng gateway notification toast
            'expires_at' => $expiresAt,
            'attempts'   => 0,
            'created_at' => time()
        ];

        return [
            'success'    => true,
            'recipient'  => $recipient,
            'channel'    => $channel,
            'code'       => $otpCode,
            'expires_in' => self::OTP_EXPIRY_SECONDS
        ];
    }

    public static function verifyOtp(string $recipient, string $code): array {
        AuthService::startSession();

        if (empty($_SESSION['otp_auth'])) {
            return ['success' => false, 'error' => 'Chưa có mã OTP nào được yêu cầu hoặc phiên đã hết hạn.'];
        }

        $otpData = &$_SESSION['otp_auth'];

        // Kiểm tra đúng người nhận
        if (strtolower(trim($otpData['recipient'])) !== strtolower(trim($recipient))) {
            return ['success' => false, 'error' => 'Mã OTP không khớp với số điện thoại hoặc email đã yêu cầu.'];
        }

        // Kiểm tra thời gian hiệu lực
        if (time() > $otpData['expires_at']) {
            unset($_SESSION['otp_auth']);
            return ['success' => false, 'error' => 'Mã OTP đã hết thời hạn hiệu lực (quá 3 phút). Vui lòng yêu cầu mã mới.'];
        }

        // Kiểm tra số lần thử sai
        if ($otpData['attempts'] >= self::MAX_ATTEMPTS) {
            unset($_SESSION['otp_auth']);
            return ['success' => false, 'error' => 'Bạn đã nhập sai OTP quá 3 lần. Vì lý do an toàn, yêu cầu này đã bị hủy.'];
        }

        // Xác thực mã OTP bằng băm Bcrypt
        if (!password_verify(trim($code), $otpData['code_hash'])) {
            $otpData['attempts']++;
            $remaining = self::MAX_ATTEMPTS - $otpData['attempts'];
            return [
                'success' => false,
                'error'   => "Mã OTP không chính xác. Bạn còn {$remaining} lần thử lại."
            ];
        }

        // Xác thực thành công -> Hủy session OTP chống Replay Attack
        unset($_SESSION['otp_auth']);
        return ['success' => true];
    }

    public static function getActiveSimulation(): ?array {
        AuthService::startSession();
        if (!empty($_SESSION['otp_auth']) && time() <= $_SESSION['otp_auth']['expires_at']) {
            return $_SESSION['otp_auth'];
        }
        return null;
    }
}
