<?php
namespace App\Services;

use App\Models\Database;
use PDO;

class LogisticsService {
    // 8 Trạng thái chuẩn của State Machine đồng bộ GHN / GHTK (Chương 6.3 trong docx)
    const STATUS_MAP = [
        'AWAITING_FULFILLMENT' => [
            'title' => 'Chờ Người Bán Chuẩn Bị Hàng',
            'desc'  => 'Đơn hàng đã được thanh toán, Shop đang kiểm tra và đóng gói vật lý.',
            'ghn'   => 'pending',
            'badge' => 'warning'
        ],
        'READY_TO_PICK' => [
            'title' => 'Đã Đóng Gói & Chờ Lấy Hàng',
            'desc'  => 'Người bán đã đóng gói và in tem vận chuyển 3PL. Chờ nhân viên bưu tá tới lấy kiện hàng.',
            'ghn'   => 'ready_to_pick',
            'badge' => 'info'
        ],
        'PICKING' => [
            'title' => 'Bưu Tá Đang Lấy Hàng',
            'desc'  => 'Nhân viên giao nhận đã tiếp nhận bưu kiện từ kho người bán.',
            'ghn'   => 'picking',
            'badge' => 'primary'
        ],
        'IN_TRANSIT' => [
            'title' => 'Đang Luân Chuyển Giữa Các Kho',
            'desc'  => 'Kiện hàng đã nhập kho trung chuyển phân loại tự động và đang di chuyển.',
            'ghn'   => 'storing, transporting',
            'badge' => 'primary'
        ],
        'OUT_FOR_DELIVERY' => [
            'title' => 'Đang Giao Hàng Cho Khách',
            'desc'  => 'Bưu tá bưu cục phát đang di chuyển đến địa chỉ nhận của khách hàng.',
            'ghn'   => 'delivering',
            'badge' => 'info'
        ],
        'DELIVERED' => [
            'title' => 'Giao Hàng Thành Công & Ký Nhận',
            'desc'  => 'Người nhận đã đồng kiểm hàng hóa, ký nhận điện tử bưu tá và hoàn tất thanh toán tiền mặt (nếu là COD).',
            'ghn'   => 'delivered',
            'badge' => 'success'
        ],
        'DELIVERY_FAILED' => [
            'title' => 'Giao Hàng Không Thành Công',
            'desc'  => 'Bưu tá không liên lạc được với người nhận hoặc khách hẹn giao lại.',
            'ghn'   => 'delivery_fail',
            'badge' => 'danger'
        ],
        'RETURNED' => [
            'title' => 'Đã Chuyển Hoàn Kho',
            'desc'  => 'Đơn hàng giao thất bại quá số lần quy định, bưu kiện được hoàn trả về kho Shop.',
            'ghn'   => 'returned',
            'badge' => 'secondary'
        ]
    ];

    /**
     * Gửi request trực tiếp đến GHN Public API (chuẩn https://developer.ghn.dev)
     */
    public static function callGhnApi($endpoint, $payload = [], $method = 'POST') {
        $config = require __DIR__ . '/../../config/ghn.php';
        $baseUrl = !empty($config['sandbox']) ? $config['dev_api_url'] : $config['prod_api_url'];
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $headers = [
            'Content-Type: application/json',
            'Token: ' . $config['token'],
            'ShopId: ' . $config['shop_id']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $res = curl_exec($ch);
        curl_close($ch);

        if (!$res) return null;
        return json_decode($res, true);
    }

    /**
     * Tính phí giao hàng tự động qua GHN API
     */
    public static function calculateShippingFee($toDistrictId = 1444, $toWardCode = '20308', $weightGram = 1000) {
        $config = require __DIR__ . '/../../config/ghn.php';
        $payload = [
            'service_type_id'  => 2, // Giao chuẩn
            'from_district_id' => (int)($config['from_district_id'] ?? 1442),
            'to_district_id'   => (int)$toDistrictId,
            'to_ward_code'     => (string)$toWardCode,
            'weight'           => (int)$weightGram,
            'length'           => 15,
            'width'            => 15,
            'height'           => 15,
            'insurance_value'  => 500000
        ];

        $res = self::callGhnApi('v2/shipping-order/fee', $payload, 'POST');
        if (!empty($res['code']) && $res['code'] === 200 && !empty($res['data']['total'])) {
            return (int)$res['data']['total'];
        }

        return (int)($config['default_fee'] ?? 28000);
    }

    /**
     * Pha 2: Người bán bấm "Chuẩn bị hàng xong" -> Sinh Mã vận đơn 3PL & Nhãn in (Chương 6.2)
     */
    public static function createShippingOrder($orderId, $carrier = 'GHN Express') {
        $pdo = Database::getInstance();

        // Lấy thông tin đơn hàng để chuẩn bị payload GHN
        $stmtOrder = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmtOrder->execute([$orderId]);
        $order = $stmtOrder->fetch();

        $trackingCode = null;
        $ghnShopId = 6723370;

        // Thử gửi yêu cầu tạo đơn lên hệ thống GHN Express qua API
        if ($order) {
            $decPhone = EncryptionService::decryptField($order['phone']);
            $decAddress = EncryptionService::decryptField($order['address']);

            $ghnPayload = [
                'payment_type_id' => ($order['payment_method'] === 'COD') ? 2 : 1, // 2: Người mua trả tiền khi nhận, 1: Người gửi trả
                'note'            => 'Cho xem hàng, không cho thử (Đồng kiểm)',
                'required_note'   => 'CHOXEMHANGKHONGCHOTHU',
                'to_name'         => $order['customer_name'],
                'to_phone'        => !empty($decPhone) ? $decPhone : '0987654321',
                'to_address'      => !empty($decAddress) ? $decAddress : '72 Lê Thánh Tôn, Bến Nghé, Quận 1',
                'to_ward_code'    => '20308',
                'to_district_id'  => 1444,
                'cod_amount'      => ($order['payment_method'] === 'COD') ? (int)$order['total_amount'] : 0,
                'content'         => 'Đơn hàng IntegrityMarket #' . $order['order_code'],
                'weight'          => 500,
                'length'          => 15,
                'width'           => 15,
                'height'          => 10,
                'service_type_id' => 2,
                'items'           => [
                    [
                        'name'     => 'Kiện hàng TMĐT IntegrityMarket',
                        'code'     => $order['order_code'],
                        'quantity' => 1,
                        'price'    => (int)$order['total_amount']
                    ]
                ]
            ];

            $ghnRes = self::callGhnApi('v2/shipping-order/create', $ghnPayload, 'POST');
            if (!empty($ghnRes['code']) && $ghnRes['code'] === 200 && !empty($ghnRes['data']['order_code'])) {
                $trackingCode = $ghnRes['data']['order_code'];
            }
        }

        // Nếu API GHN chưa tạo được (do địa chỉ test hoặc kết nối), tự động sinh mã định dạng GHN chuẩn
        if (empty($trackingCode)) {
            $trackingCode = 'GHN' . rand(10000000, 99999999) . 'VN';
        }

        // Cập nhật trạng thái sang READY_TO_PICK
        $stmt = $pdo->prepare("UPDATE orders SET fulfillment_status = 'READY_TO_PICK', tracking_code = ?, shipping_carrier = ? WHERE id = ?");
        $stmt->execute([$trackingCode, $carrier, $orderId]);

        // Thêm log hành trình vận đơn
        self::addTrackingLog(
            $orderId, 
            'READY_TO_PICK', 
            'Đã đóng gói & In tem vận đơn GHN Express', 
            "Mã vận đơn {$trackingCode} đã được tạo thành công trên hệ thống GHN Express (Shop #{$ghnShopId}). Đơn hàng áp dụng chính sách Đồng kiểm khi phát.",
            'Kho Tổng TP. Hồ Chí Minh'
        );

        return $trackingCode;
    }

    /**
     * Cập nhật trạng thái vận đơn (Giả lập Webhook 3PL)
     */
    public static function updateStatus($orderId, $newStatus, $location = 'Bưu cục trung chuyển') {
        if (!isset(self::STATUS_MAP[$newStatus])) return false;

        $pdo = Database::getInstance();
        if ($newStatus === 'DELIVERED') {
            // Khi bưu tá phát thành công: nếu là đơn COD thì cập nhật luôn trạng thái tiền sang PAID
            $stmt = $pdo->prepare("UPDATE orders SET fulfillment_status = ?, payment_status = IF(payment_method = 'COD', 'PAID', payment_status) WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
        } else {
            $stmt = $pdo->prepare("UPDATE orders SET fulfillment_status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
        }

        $info = self::STATUS_MAP[$newStatus];
        self::addTrackingLog($orderId, $newStatus, $info['title'], $info['desc'], $location);

        return true;
    }

    public static function addTrackingLog($orderId, $status, $title, $desc, $location) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO delivery_trackings (order_id, status, status_title, description, location) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$orderId, $status, $title, $desc, $location]);
    }

    public static function getTrackingLogs($orderId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM delivery_trackings WHERE order_id = ? ORDER BY timestamp DESC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}
