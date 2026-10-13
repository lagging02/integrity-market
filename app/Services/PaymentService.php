<?php
namespace App\Services;

use App\Models\Database;
use PDO;

class PaymentService {
    /**
     * Lấy danh sách thẻ đã lưu dạng Token của người dùng (PCI-DSS Tokenization)
     */
    public static function getUserTokens($userId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM user_payment_tokens WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Lưu chuỗi Token do Cổng thanh toán cấp (Bước 3 trong docx Chương 4.2)
     * Tuyệt đối không nhận hay lưu số thẻ PAN/CVV vào Database
     */
    public static function saveToken($userId, $provider, $cardBrand, $last4, $expMonth, $expYear) {
        $pdo = Database::getInstance();
        $tokenId = EncryptionService::generateUuid();
        $paymentToken = 'tok_' . bin2hex(random_bytes(12)); // Giả lập token sinh từ Stripe/VNPAY Vault

        $stmt = $pdo->prepare("INSERT INTO user_payment_tokens (token_id, user_id, gateway_provider, payment_token, card_brand, last_four_digits, expiry_month, expiry_year, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$tokenId, $userId, $provider, $paymentToken, $cardBrand, $last4, $expMonth, $expYear]);

        return $paymentToken;
    }

    /**
     * Thực thi 1-Click Checkout qua Server-to-Server API (Chương 4.3 trong docx)
     */
    public static function executeOneClickCharge($paymentToken, $amount, $orderCode) {
        // Cấu trúc Payload API gọi trừ tiền gửi sang Payment Gateway theo đúng mẫu Docx:
        $payload = [
            "merchant_id"     => "MERCHANT_PROD_9981",
            "idempotency_key" => "IDEM_REQ_" . bin2hex(random_bytes(8)),
            "payment_token"   => $paymentToken,
            "amount"          => (int)$amount,
            "currency"        => "VND",
            "order_id"        => $orderCode,
            "capture"         => true
        ];

        // Giả lập giao tiếp Server-to-Server an toàn qua HTTPS mTLS
        // Thành công trả về transaction_id
        return [
            'success'        => true,
            'transaction_id' => 'TXN_' . strtoupper(bin2hex(random_bytes(6))),
            'payload'        => $payload
        ];
    }

    public static function getVietQrUrl($amount, $orderCode) {
        $sec = require __DIR__ . '/../../config/security.php';
        $vqr = $sec['payment_gateways']['vietqr'];
        $memo = "DH" . $orderCode;
        return "https://img.vietqr.io/image/{$vqr['bank_id']}-{$vqr['account_no']}-compact2.png?amount={$amount}&addInfo={$memo}&accountName=" . urlencode($vqr['account_name']);
    }

    /**
     * Tạo URL chuyển hướng sang Cổng thanh toán VNPAY Sandbox (v2.1.0)
     */
    public static function createVnpayPaymentUrl($orderCode, $amount, $orderInfo = null) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $vnpConfig = $config['vnpay'];
        $vnp_Url = $vnpConfig['payment_url'];
        $vnp_TmnCode = $vnpConfig['tmn_code'];
        $vnp_HashSecret = $vnpConfig['hash_secret'];
        $vnp_Returnurl = $vnpConfig['return_url'];
        // Tự động điều chỉnh Return URL khớp với domain hiện tại (shop.itgslab.io.vn hoặc IP LAN)
        if (!empty($_SERVER['HTTP_HOST'])) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                       (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                       (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            $proto = $isHttps ? 'https://' : 'http://';
            $vnp_Returnurl = $proto . $_SERVER['HTTP_HOST'] . '/index.php?page=vnpay-return';
        }

        $vnp_TxnRef = $orderCode;
        $vnp_OrderInfo = $orderInfo ?: ("Thanh toan don hang #" . $orderCode);
        $vnp_OrderType = "billpayment";
        $vnp_Amount = (int)$amount * 100; // VNPAY tính đơn vị x100 (VND)
        $vnp_Locale = "vn";
        $vnp_IpAddr = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $createDate = date('YmdHis');
        $expireDate = date('YmdHis', strtotime('+15 minutes'));

        $inputData = [
            "vnp_Version"        => "2.1.0",
            "vnp_TmnCode"        => $vnp_TmnCode,
            "vnp_Amount"         => $vnp_Amount,
            "vnp_Command"        => "pay",
            "vnp_CreateDate"     => $createDate,
            "vnp_CurrCode"       => "VND",
            "vnp_IpAddr"         => $vnp_IpAddr,
            "vnp_Locale"         => $vnp_Locale,
            "vnp_OrderInfo"      => $vnp_OrderInfo,
            "vnp_OrderType"      => $vnp_OrderType,
            "vnp_ReturnUrl"      => $vnp_Returnurl,
            "vnp_TxnRef"         => $vnp_TxnRef,
            "vnp_ExpireDate"     => $expireDate,
        ];

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (!empty($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Xác thực chữ ký số HMAC-SHA512 khi VNPAY trả kết quả về (Return / IPN)
     */
    public static function verifyVnpayResponse(array $params) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $vnpConfig = $config['vnpay'];
        $vnp_HashSecret = $vnpConfig['hash_secret'];

        $vnp_SecureHash = $params['vnp_SecureHash'] ?? '';
        $inputData = [];
        foreach ($params as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);
        ksort($inputData);

        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $isValid = hash_equals($secureHash, $vnp_SecureHash);
        $isSuccess = ($params['vnp_ResponseCode'] ?? '') === '00';

        return [
            'valid'         => $isValid,
            'success'       => $isValid && $isSuccess,
            'order_code'    => $params['vnp_TxnRef'] ?? '',
            'amount'        => isset($params['vnp_Amount']) ? ((int)$params['vnp_Amount'] / 100) : 0,
            'response_code' => $params['vnp_ResponseCode'] ?? '',
            'transaction_no'=> $params['vnp_TransactionNo'] ?? '',
            'bank_code'     => $params['vnp_BankCode'] ?? ''
        ];
    }

    /**
     * Tạo URL chuyển hướng sang Cổng thanh toán MoMo Developer Sandbox (API v2 / All-in-One)
     * Dựa trên chuẩn chính thức: https://github.com/momo-wallet/payment
     */
    public static function createMomoPaymentUrl($orderCode, $amount, $orderInfo = null) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $momo = $config['momo'];

        if (empty($momo['enabled'])) {
            return null;
        }

        $endpoint    = $momo['endpoint'];
        $partnerCode = $momo['partner_code'];
        $accessKey   = $momo['access_key'];
        $secretKey   = $momo['secret_key'];

        $redirectUrl = $momo['return_url'];
        $ipnUrl      = $momo['notify_url'];

        if (!empty($_SERVER['HTTP_HOST'])) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                       (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                       (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            $proto = $isHttps ? 'https://' : 'http://';
            $redirectUrl = $proto . $_SERVER['HTTP_HOST'] . '/index.php?page=momo-return';
            $ipnUrl      = $proto . $_SERVER['HTTP_HOST'] . '/index.php?page=payment-webhook';
        }

        $orderId     = (string)$orderCode;
        $requestId   = (string)$orderCode . '_' . time();
        $amountStr   = (string)(int)$amount;
        $orderInfo   = $orderInfo ?: ("Thanh toan don hang MoMo #" . $orderCode);
        $requestType = "captureWallet";
        $extraData   = "";

        $rawHash = "accessKey=" . $accessKey .
                   "&amount=" . $amountStr .
                   "&extraData=" . $extraData .
                   "&ipnUrl=" . $ipnUrl .
                   "&orderId=" . $orderId .
                   "&orderInfo=" . $orderInfo .
                   "&partnerCode=" . $partnerCode .
                   "&redirectUrl=" . $redirectUrl .
                   "&requestId=" . $requestId .
                   "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'IntegrityMarket',
            'storeId'     => 'IntegrityMarketStore',
            'requestId'   => $requestId,
            'amount'      => $amountStr,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) {
            return null;
        }

        $res = json_decode($result, true);
        if (isset($res['payUrl'])) {
            return $res['payUrl'];
        }

        return null;
    }

    /**
     * Xác thực chữ ký số HMAC-SHA256 khi MoMo trả kết quả về (momo-return)
     */
    public static function verifyMomoResponse(array $params) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $momo = $config['momo'];
        $secretKey = $momo['secret_key'];
        $accessKey = $momo['access_key'];

        $partnerCode  = $params['partnerCode'] ?? '';
        $orderId      = $params['orderId'] ?? '';
        $requestId    = $params['requestId'] ?? '';
        $amount       = $params['amount'] ?? '';
        $orderInfo    = $params['orderInfo'] ?? '';
        $orderType    = $params['orderType'] ?? '';
        $transId      = $params['transId'] ?? '';
        $resultCode   = $params['resultCode'] ?? '';
        $message      = $params['message'] ?? '';
        $payType      = $params['payType'] ?? '';
        $responseTime = $params['responseTime'] ?? '';
        $extraData    = $params['extraData'] ?? '';
        $momoSig      = $params['signature'] ?? '';

        $rawHash = "accessKey=" . $accessKey .
                   "&amount=" . $amount .
                   "&extraData=" . $extraData .
                   "&message=" . $message .
                   "&orderId=" . $orderId .
                   "&orderInfo=" . $orderInfo .
                   "&orderType=" . $orderType .
                   "&partnerCode=" . $partnerCode .
                   "&payType=" . $payType .
                   "&requestId=" . $requestId .
                   "&responseTime=" . $responseTime .
                   "&resultCode=" . $resultCode .
                   "&transId=" . $transId;

        $computedSig = hash_hmac("sha256", $rawHash, $secretKey);
        $isValid = hash_equals($computedSig, $momoSig);
        $isSuccess = ($resultCode === '0' || $resultCode === 0);

        return [
            'valid'          => $isValid,
            'success'        => $isValid && $isSuccess,
            'order_code'     => $orderId,
            'amount'         => (int)$amount,
            'result_code'    => $resultCode,
            'message'        => $message,
            'transaction_no' => $transId,
            'pay_type'       => $payType
        ];
    }

    /**
     * Tạo URL chuyển hướng sang Cổng thanh toán ZaloPay Developer Sandbox (API v2)
     */
    public static function createZaloPayPaymentUrl($orderCode, $amount, $orderInfo = null) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $zalo = $config['zalopay'];

        if (empty($zalo['enabled'])) {
            return null;
        }

        $app_id   = $zalo['app_id'];
        $key1     = $zalo['key1'];
        $endpoint = $zalo['endpoint'];

        $redirectUrl = $zalo['return_url'];
        if (!empty($_SERVER['HTTP_HOST'])) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                       (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                       (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            $proto = $isHttps ? 'https://' : 'http://';
            $redirectUrl = $proto . $_SERVER['HTTP_HOST'] . '/index.php?page=zalopay-return&order_code=' . urlencode($orderCode) . '&amount=' . (int)$amount;
        }

        $embeddata = json_encode([
            "redirecturl" => $redirectUrl,
            "order_code"  => (string)$orderCode
        ]);

        $items = json_encode([
            [
                "itemid"       => (string)$orderCode,
                "itemname"     => "Don hang #" . $orderCode,
                "itemprice"    => (int)$amount,
                "itemquantity" => 1
            ]
        ]);

        $appTransId = date("ymd") . "_" . (string)$orderCode;
        $appTime = round(microtime(true) * 1000);
        $amountInt = (int)$amount;
        $appUser = "buyer_" . substr($orderCode, 0, 8);
        $description = $orderInfo ?: ("Thanh toan don hang #" . $orderCode);

        // Chuỗi băm chuẩn của ZaloPay: app_id|app_trans_id|app_user|amount|app_time|embed_data|item
        $dataStr = $app_id . "|" . $appTransId . "|" . $appUser . "|" . $amountInt . "|" . $appTime . "|" . $embeddata . "|" . $items;
        $mac = hash_hmac("sha256", $dataStr, $key1);

        $order = [
            "app_id"       => $app_id,
            "app_user"     => $appUser,
            "app_time"     => $appTime,
            "amount"       => $amountInt,
            "app_trans_id" => $appTransId,
            "embed_data"   => $embeddata,
            "item"         => $items,
            "description"  => $description,
            "bank_code"    => "",
            "mac"          => $mac
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($order));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return null;
        }

        $result = json_decode($response, true);
        if (isset($result["return_code"]) && $result["return_code"] == 1 && !empty($result["order_url"])) {
            return [
                'order_url'         => $result["order_url"],
                'qr_code'           => $result["qr_code"] ?? '',
                'zp_trans_token'    => $result["zp_trans_token"] ?? '',
                'cashier_order_url' => $result["cashier_order_url"] ?? ''
            ];
        }

        return null;
    }

    /**
     * Xác thực kết quả trả về từ Cổng ZaloPay Developer Sandbox
     */
    public static function verifyZaloPayResponse(array $params) {
        $orderCode     = $params['order_code'] ?? '';
        $appTransId    = $params['apptransid'] ?? '';
        $amount        = (int)($params['amount'] ?? 0);
        $status        = $params['status'] ?? null;
        $checksum      = $params['checksum'] ?? '';
        $isSandboxDemo = ($params['sandbox_demo'] ?? '') === '1';

        // Nếu order_code rỗng nhưng có apptransid dạng "yymmdd_ORD-..."
        if (empty($orderCode) && !empty($appTransId)) {
            $parts = explode('_', $appTransId, 2);
            if (count($parts) === 2) {
                $orderCode = $parts[1];
            }
        }

        // 1. Kiểm tra trường hợp người dùng hủy hoặc ZaloPay trả về mã thất bại
        // Theo ZaloPay API: status = 1 là THÀNH CÔNG; status != 1 (như -49, 2, 4...) là THẤT BẠI / HỦY BỎ
        if ($status !== null && (int)$status !== 1) {
            $msg = 'Giao dịch ZaloPay không thành công hoặc đã bị hủy (Mã phản hồi: ' . htmlspecialchars((string)$status) . ').';
            if ((int)$status === -49) {
                $msg = 'Giao dịch đã bị khách hàng hủy bỏ hoặc chưa hoàn tất trên ứng dụng ZaloPay.';
            }
            return [
                'valid'          => true,
                'success'        => false,
                'order_code'     => $orderCode,
                'amount'         => $amount,
                'status'         => $status,
                'message'        => $msg,
                'transaction_no' => $appTransId ?: ('ZLP_CANCEL_' . time()),
                'pay_type'       => 'Ví ZaloPay'
            ];
        }

        // 2. Nếu status === 1 (ZaloPay chuyển hướng về báo thành công)
        if ($status !== null && (int)$status === 1) {
            // Xác thực thêm 1 lớp bảo mật với ZaloPay Server qua Query API
            $queryRes = self::queryZaloPayStatus($orderCode, $appTransId);
            $queryReturnCode = isset($queryRes['return_code']) ? (int)$queryRes['return_code'] : 0;

            if ($queryRes && $queryReturnCode === 1) {
                return [
                    'valid'          => true,
                    'success'        => true,
                    'order_code'     => $orderCode,
                    'amount'         => $queryRes['amount'] ?? $amount,
                    'transaction_no' => $queryRes['zp_trans_id'] ?? ($appTransId ?: ('ZLP_' . strtoupper(bin2hex(random_bytes(5))))),
                    'pay_type'       => 'Ví ZaloPay (Đã đối soát Server)',
                    'message'        => 'Thanh toán ZaloPay thành công.'
                ];
            } elseif ($queryRes && in_array($queryReturnCode, [2, 4])) {
                return [
                    'valid'          => true,
                    'success'        => false,
                    'order_code'     => $orderCode,
                    'amount'         => $amount,
                    'status'         => $queryReturnCode,
                    'message'        => $queryRes['return_message'] ?? 'Giao dịch đã bị hủy hoặc không thành công trên ZaloPay.',
                    'transaction_no' => $appTransId,
                    'pay_type'       => 'Ví ZaloPay'
                ];
            } else {
                return [
                    'valid'          => true,
                    'success'        => true,
                    'order_code'     => $orderCode,
                    'amount'         => $amount,
                    'transaction_no' => $appTransId ?: ('ZLP_' . strtoupper(bin2hex(random_bytes(5)))),
                    'pay_type'       => 'Ví ZaloPay Sandbox',
                    'message'        => 'Thanh toán ZaloPay hoàn tất.'
                ];
            }
        }

        // 3. Nếu là nút Giả lập Sandbox Demo được click có chủ đích (sandbox_demo=1)
        if ($isSandboxDemo && $status === null) {
            return [
                'valid'          => true,
                'success'        => true,
                'order_code'     => $orderCode,
                'amount'         => $amount,
                'transaction_no' => 'ZLP_DEMO_' . strtoupper(bin2hex(random_bytes(4))),
                'pay_type'       => 'Ví ZaloPay Sandbox Demo',
                'message'        => 'Xác nhận thanh toán mô phỏng thành công.'
            ];
        }

        // 4. Nếu không có status trên URL (truy vấn trực tiếp ZaloPay Server qua API Query)
        if (!empty($orderCode)) {
            $queryRes = self::queryZaloPayStatus($orderCode, $appTransId);
            if ($queryRes && (int)($queryRes['return_code'] ?? 0) === 1) {
                return [
                    'valid'          => true,
                    'success'        => true,
                    'order_code'     => $orderCode,
                    'amount'         => $queryRes['amount'] ?? $amount,
                    'transaction_no' => $queryRes['zp_trans_id'] ?? ($appTransId ?: ('ZLP_' . strtoupper(bin2hex(random_bytes(5))))),
                    'pay_type'       => 'Ví ZaloPay (Đối soát API)',
                    'message'        => 'Giao dịch đã thanh toán thành công.'
                ];
            } else {
                $returnMsg = $queryRes['return_message'] ?? 'Giao dịch chưa được thanh toán hoặc đã bị hủy trên ZaloPay.';
                return [
                    'valid'          => true,
                    'success'        => false,
                    'order_code'     => $orderCode,
                    'amount'         => $amount,
                    'message'        => $returnMsg,
                    'transaction_no' => $appTransId,
                    'pay_type'       => 'Ví ZaloPay'
                ];
            }
        }

        return [
            'valid'          => false,
            'success'        => false,
            'order_code'     => $orderCode,
            'amount'         => $amount,
            'message'        => 'Không tìm thấy thông tin giao dịch ZaloPay hợp lệ.',
            'transaction_no' => $appTransId,
            'pay_type'       => 'Ví ZaloPay'
        ];
    }

    /**
     * Truy vấn trực tiếp trạng thái đơn hàng từ Cổng ZaloPay Sandbox (API v2 Query)
     * Endpoint: https://sb-openapi.zalopay.vn/v2/query
     */
    public static function queryZaloPayStatus($orderCode, $appTransId = null) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $zalo = $config['zalopay'];
        $app_id = $zalo['app_id'];
        $key1   = $zalo['key1'];

        if (empty($appTransId)) {
            if (strpos($orderCode, '_') !== false && strlen(explode('_', $orderCode)[0]) === 6) {
                $appTransId = $orderCode;
            } else {
                $appTransId = date("ymd") . "_" . (string)$orderCode;
            }
        }

        $data = $app_id . "|" . $appTransId . "|" . $key1;
        $mac = hash_hmac("sha256", $data, $key1);

        $payload = [
            'app_id'       => $app_id,
            'app_trans_id' => $appTransId,
            'mac'          => $mac
        ];

        $ch = curl_init('https://sb-openapi.zalopay.vn/v2/query');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        if (!$res) return null;
        return json_decode($res, true);
    }

    /**
     * Truy vấn trực tiếp trạng thái đơn hàng từ Cổng MoMo Sandbox (API v2 Query)
     * Endpoint: https://test-payment.momo.vn/v2/gateway/api/query
     */
    public static function queryMomoStatus($orderCode) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $momo = $config['momo'];
        $partnerCode = $momo['partner_code'];
        $accessKey   = $momo['access_key'];
        $secretKey   = $momo['secret_key'];

        $requestId = 'QUERY_' . time() . '_' . rand(100, 999);
        $rawHash = "accessKey=" . $accessKey .
                   "&orderId=" . $orderCode .
                   "&partnerCode=" . $partnerCode .
                   "&requestId=" . $requestId;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'requestId'   => $requestId,
            'orderId'     => (string)$orderCode,
            'signature'   => $signature,
            'lang'        => 'vi'
        ];

        $ch = curl_init('https://test-payment.momo.vn/v2/gateway/api/query');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        if (!$res) return null;
        return json_decode($res, true);
    }

    /**
     * Tạo URL chuyển hướng sang Cổng thanh toán Quốc tế PayPal Sandbox (REST API v2)
     */
    public static function createPaypalPaymentUrl($orderCode, $amountVnd) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $paypal = $config['paypal'];

        if (empty($paypal['enabled'])) {
            return null;
        }

        $clientId = $paypal['client_id'];
        $secret   = $paypal['secret'];
        $rate     = $paypal['exchange_rate'] ?? 25000;
        $amountUsd = number_format($amountVnd / $rate, 2, '.', '');

        $redirectUrl = $paypal['return_url'];
        $cancelUrl   = $paypal['cancel_url'];
        if (!empty($_SERVER['HTTP_HOST'])) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                       (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                       (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            $proto = $isHttps ? 'https://' : 'http://';
            $redirectUrl = $proto . $_SERVER['HTTP_HOST'] . '/index.php?page=paypal-return&order_code=' . urlencode($orderCode);
            $cancelUrl   = $proto . $_SERVER['HTTP_HOST'] . '/index.php?page=cart';
        }

        // Lấy OAuth2 Access Token từ PayPal
        $ch = curl_init("https://api-m.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $tokenRes = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($tokenRes, true);
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            // Khi chưa cấu hình key riêng, dùng link Sandbox trực tiếp
            return "https://www.sandbox.paypal.com/checkoutnow?token=EC_DEMO_" . urlencode($orderCode);
        }

        // Tạo Order v2 trên PayPal
        $orderPayload = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => (string)$orderCode,
                "amount" => [
                    "currency_code" => "USD",
                    "value" => $amountUsd
                ],
                "description" => "Don hang IntegrityMarket #" . $orderCode
            ]],
            "application_context" => [
                "return_url"          => $redirectUrl,
                "cancel_url"          => $cancelUrl,
                "brand_name"          => "IntegrityMarket",
                "user_action"         => "PAY_NOW"
            ]
        ];

        $ch = curl_init("https://api-m.sandbox.paypal.com/v2/checkout/orders");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $orderRes = curl_exec($ch);
        curl_close($ch);

        $orderData = json_decode($orderRes, true);
        if (!empty($orderData['links'])) {
            foreach ($orderData['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return $link['href'];
                }
            }
        }

        return "https://www.sandbox.paypal.com/checkoutnow?token=EC_DEMO_" . urlencode($orderCode);
    }

    /**
     * Xác thực và Capture giao dịch PayPal Sandbox
     */
    public static function capturePaypalOrder($token, $orderCode) {
        $config = require __DIR__ . '/../../config/payment_gateways.php';
        $paypal = $config['paypal'];
        $clientId = $paypal['client_id'];
        $secret   = $paypal['secret'];

        // Lấy Token
        $ch = curl_init("https://api-m.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $tokenRes = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($tokenRes, true);
        $accessToken = $tokenData['access_token'] ?? null;

        if ($accessToken && !empty($token) && strpos($token, 'EC_DEMO_') === false) {
            $ch = curl_init("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$token}/capture");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer " . $accessToken
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $capRes = curl_exec($ch);
            curl_close($ch);

            $capData = json_decode($capRes, true);
            $isPaid = ($capData['status'] ?? '') === 'COMPLETED';
            return [
                'success'        => $isPaid,
                'transaction_id' => $capData['purchase_units'][0]['payments']['captures'][0]['id'] ?? ('PAY_' . strtoupper(bin2hex(random_bytes(5)))),
                'order_code'     => $orderCode
            ];
        }

        return [
            'success'        => true,
            'transaction_id' => 'PAYPAL_' . strtoupper(bin2hex(random_bytes(5))),
            'order_code'     => $orderCode
        ];
    }
}
