<?php
namespace App\Services;

class EncryptionService {
    private static $key;
    private static $cipher;

    public static function init() {
        if (!self::$key) {
            $config = require __DIR__ . '/../../config/security.php';
            self::$key = $config['db_encryption_key'];
            self::$cipher = $config['cipher_algorithm'];
        }
    }

    /**
     * Mã hóa thông tin định danh cá nhân (PII) bằng AES-256-CBC
     * Sinh Vector ngẫu nhiên (IV) 16 bytes cho từng bản ghi
     */
    public static function encryptField($plaintext) {
        if (empty($plaintext)) return '';
        self::init();

        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $raw_cipher = openssl_encrypt($plaintext, self::$cipher, self::$key, OPENSSL_RAW_DATA, $iv);

        return 'ENC:' . base64_encode($iv . $raw_cipher);
    }

    /**
     * Giải mã dữ liệu khi hiển thị cho người có thẩm quyền
     */
    public static function decryptField($ciphertext_base64) {
        if (empty($ciphertext_base64)) return '';
        if (strpos($ciphertext_base64, 'ENC:') !== 0) {
            return $ciphertext_base64; // Dữ liệu cũ hoặc chưa mã hóa
        }
        self::init();

        $raw_data = base64_decode(substr($ciphertext_base64, 4));
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($raw_data, 0, $ivlen);
        $raw_cipher = substr($raw_data, $ivlen);

        return openssl_decrypt($raw_cipher, self::$cipher, self::$key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Băm mật khẩu bằng Bcrypt chuẩn Cost = 12 (Chương 5.2 trong docx)
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Sinh mã UUIDv4 ngẫu nhiên chống tấn công IDOR (Chương 5.1 trong docx)
     */
    public static function generateUuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant RFC 4122
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
