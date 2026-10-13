<?php
namespace App\Controllers;

use App\Models\Category;
use App\Models\Database;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Services\AuthService;
use App\Services\CsrfService;
use App\Services\EncryptionService;
use App\Services\LogisticsService;

class SellerController {
    public function __construct() {
        AuthService::requireRole(['BUYER', 'SELLER', 'ADMIN'], '?page=login&notice=login_required');
    }

    private function getShop() {
        $user = AuthService::user();
        $shop = Shop::getByUserId($user['id']);
        if (!$shop && $user['role'] === 'ADMIN') {
            $shop = Shop::getById('shp-tech-001');
        }
        if (!$shop) {
            header('Location: index.php?page=seller-register');
            exit;
        }
        return $shop;
    }

    /**
     * Luồng Shopee: Bất kỳ người dùng nào cũng có thể đăng ký mở Shop
     */
    public function registerShop() {
        $currentUser = AuthService::user();
        $existingShop = Shop::getByUserId($currentUser['id']);
        if ($existingShop) {
            header('Location: index.php?page=seller');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $error = 'Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn (CSRF Token invalid)!';
            } else {
                $shopName = trim($_POST['shop_name'] ?? '');
                $address  = trim($_POST['pickup_address'] ?? '');
                $desc     = trim($_POST['description'] ?? '');
                $avatar   = trim($_POST['avatar'] ?? '');

                if (empty($shopName) || empty($address)) {
                    $error = 'Vui lòng điền tên gian hàng và địa chỉ kho lấy hàng!';
                } else {
                    $pdo = Database::getInstance();
                    $shopId = EncryptionService::generateUuid();
                    $shopSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $shopName))) . '-' . rand(100, 999);

                    $stmt = $pdo->prepare("INSERT INTO shops (id, user_id, shop_name, shop_slug, description, avatar) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $shopId,
                        $currentUser['id'],
                        $shopName,
                        $shopSlug,
                        $desc ?: 'Gian hàng mới mở trên sàn IntegrityMarket.',
                        $avatar ?: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=200'
                    ]);

                    // Nâng cấp vai trò nếu đang là BUYER (ADMIN giữ nguyên quyền cao nhất)
                    if ($currentUser['role'] !== 'ADMIN') {
                        $pdo->prepare("UPDATE users SET role = 'SELLER' WHERE id = ?")->execute([$currentUser['id']]);
                        $_SESSION['role'] = 'SELLER';
                    }

                    // Cập nhật session ngay lập tức
                    $_SESSION['shop'] = Shop::getById($shopId);

                    header('Location: index.php?page=seller&welcome=1');
                    exit;
                }
            }
        }

        require __DIR__ . '/../../views/seller/register_shop.php';
    }

    public function dashboard() {
        $currentUser = AuthService::user();
        $shop = $this->getShop();
        $orders = Order::getByShop($shop['id']);
        $products = Product::getByShop($shop['id']);

        $totalRevenue = 0;
        $awaitingCount = 0;
        foreach ($orders as $o) {
            if ($o['payment_status'] === 'PAID') {
                $totalRevenue += $o['total_amount'];
            }
            if ($o['fulfillment_status'] === 'AWAITING_FULFILLMENT') {
                $awaitingCount++;
            }
        }

        require __DIR__ . '/../../views/seller/dashboard.php';
    }

    public function products() {
        $currentUser = AuthService::user();
        $shop = $this->getShop();
        $products = Product::getByShop($shop['id']);

        require __DIR__ . '/../../views/seller/products.php';
    }

    public function addProduct() {
        $currentUser = AuthService::user();
        $shop = $this->getShop();
        $categories = Category::getAll();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $error = 'Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn (CSRF Token invalid)!';
            } else {
                $name     = trim($_POST['name'] ?? '');
                $catId    = (int)($_POST['category_id'] ?? 1);
                $price    = (float)($_POST['price'] ?? 0);
                $origPrice= (float)($_POST['original_price'] ?? 0);
                $stock    = (int)($_POST['stock'] ?? 10);
                $sku      = trim($_POST['sku'] ?? '');
                $isDiy    = isset($_POST['is_diy']) ? 1 : 0;
                $craftTime= trim($_POST['diy_craft_time'] ?? '');
                $image    = trim($_POST['image'] ?? '');
                $desc     = trim($_POST['description'] ?? '');

                if (Product::checkDuplicate($name, $image, $shop['id'])) {
                    $error = 'Sản phẩm có tên hoặc ảnh này đã tồn tại trong gian hàng của bạn!';
                } elseif (empty($name) || $price <= 0) {
                    $error = 'Vui lòng điền tên sản phẩm và mức giá hợp lệ!';
                } else {
                    $productId = EncryptionService::generateUuid();
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

                    $data = [
                        'id'             => $productId,
                        'shop_id'        => $shop['id'],
                        'category_id'    => $catId,
                        'name'           => $name,
                        'slug'           => $slug,
                        'price'          => $price,
                        'original_price' => $origPrice,
                        'stock'          => $stock,
                        'sku'            => $sku ?: 'SKU-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
                        'is_diy'         => $isDiy,
                        'diy_craft_time' => $isDiy ? $craftTime : null,
                        'attributes_json'=> !empty($_POST['attributes']) ? json_encode($_POST['attributes'], JSON_UNESCAPED_UNICODE) : null,
                        'image'          => $image ?: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600',
                        'description'    => $desc
                    ];

                    if (Product::create($data)) {
                        header('Location: index.php?page=seller-products&msg=created');
                        exit;
                    } else {
                        $error = 'Không thể lưu sản phẩm. Vui lòng kiểm tra dữ liệu!';
                    }
                }
            }
        }

        require __DIR__ . '/../../views/seller/add_product.php';
    }

    public function orders() {
        $currentUser = AuthService::user();
        $shop = $this->getShop();
        $orders = Order::getByShop($shop['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_create_shipping'])) {
            if (CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $orderId = $_POST['order_id'] ?? '';
                $carrier = $_POST['carrier'] ?? 'GHN Express';
                LogisticsService::createShippingOrder($orderId, $carrier);
                header('Location: index.php?page=seller-orders&msg=shipping_created');
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_dispatch_driver'])) {
            if (CsrfService::validateToken($_POST['csrf_token'] ?? '')) {
                $orderId = $_POST['order_id'] ?? '';
                LogisticsService::updateStatus($orderId, 'IN_TRANSIT', 'Kho trung chuyển bưu cục phát');
                header('Location: index.php?page=seller-orders&msg=dispatched');
                exit;
            }
        }

        require __DIR__ . '/../../views/seller/orders.php';
    }

    public function shippingLabel() {
        $id = $_GET['id'] ?? '';
        $order = Order::getById($id);
        if (!$order) {
            die('Không tìm thấy đơn hàng');
        }
        require __DIR__ . '/../../views/seller/shipping_label.php';
    }
}
