# BÁO CÁO KỸ THUẬT: ĐẶC TẢ CƠ SỞ DỮ LIỆU SQL & KIẾN TRÚC PHÒNG THỦ BLUE TEAM
**Hệ Thống Sàn Thương Mại Điện Tử Lai Tạo (IntegrityMarket)**  
*Đề tài: Đồ Án An Toàn Thương Mại Điện Tử - Nhóm 11*  
*Môi trường triển khai: Ubuntu Server 24.04 LTS | Nginx Reverse Proxy | PHP 8.3 FPM | MariaDB / MySQL 8.0 | Cloudflare Tunnel*

---

## LỜI MỞ ĐẦU: ĐỊNH VỊ VAI TRÒ BLUE TEAM (PHÒNG THỦ HỆ THỐNG)

Trong mô hình an toàn thông tin chuyên nghiệp:
- **Blue Team (Đội Phòng Thủ):** Là đội ngũ kỹ sư chịu trách nhiệm thiết kế kiến trúc bảo mật ngay từ đầu (**Security by Design**), triển khai phòng ngự theo chiều sâu (**Defense in Depth**), mã hóa dữ liệu tĩnh và động, kiểm soát phân quyền chặt chẽ và cô lập các mối đe dọa bên ngoài (Hacker, Botnet, Crawler, Scanning).
- **Hệ thống này được xây dựng chuẩn hóa 100% theo góc nhìn Blue Team:** Chúng ta **không** xây dựng các nút bấm giả lập tấn công ("trò mèo") trên giao diện web người dùng hay trang quản trị. Mọi cơ chế phòng vệ (kiểm tra chữ ký số HMAC, chống sửa đổi số tiền, chống SQLi, chống XSS, chống tấn công phát lại) đều nằm ở tầng **nhân hệ điều hành, cấu hình Nginx, dịch vụ mạng Cloudflare WAF và tầng xử lý nghiệp vụ Backend (Middleware/Controller)**.

---

## PHẦN I: TOÀN BỘ CÂU LỆNH SQL HỆ THỐNG (DDL & DML)

Toàn bộ cấu trúc cơ sở dữ liệu được thiết kế nhằm đáp ứng các tiêu chuẩn bảo mật khắt khe nhất (**OWASP Top 10**, **PCI-DSS Tokenization** và **Bảo vệ dữ liệu cá nhân PII**).

### 1.1. Khởi Tạo Cơ Sở Dữ Liệu & Phân Quyền Tối Thiểu (Principle of Least Privilege)

```sql
-- 1. Tạo Database với bảng mã Unicode đầy đủ (hỗ trợ tiếng Việt và Emoji)
CREATE DATABASE IF NOT EXISTS shop_db 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- 2. Tạo tài khoản chuyên dụng cho ứng dụng Web, không dùng tài khoản root
CREATE USER IF NOT EXISTS 'shop_user'@'localhost' IDENTIFIED BY 'Pass@123456';

-- 3. Chỉ cấp quyền thao tác trên đúng cơ sở dữ liệu shop_db
GRANT ALL PRIVILEGES ON shop_db.* TO 'shop_user'@'localhost';
FLUSH PRIVILEGES;

USE shop_db;
```

---

### 1.2. Đặc Tả DDL 8 Bảng Dữ Liệu Cốt Lõi (Kèm Giải Thích Kỹ Thuật Bảo Mật)

#### Bảng 1: `users` - Quản Lý Người Dùng & Phân Quyền (RBAC)

```sql
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY,                  -- UUIDv4 (128-bit RFC 4122) chống tấn công duyệt ID tự tăng (IDOR)
    username VARCHAR(50) NOT NULL UNIQUE,        -- Định danh đăng nhập duy nhất
    email VARCHAR(100) NOT NULL UNIQUE,          -- Email xác thực tài khoản
    password_hash VARCHAR(255) NOT NULL,         -- Băm 1 chiều bằng Bcrypt Work Factor = 12 kèm Salt ngẫu nhiên
    full_name VARCHAR(150) NOT NULL,             -- Họ tên người dùng
    role ENUM('BUYER', 'SELLER', 'ADMIN') NOT NULL DEFAULT 'BUYER', -- Phân quyền theo vai trò (RBAC)
    phone VARCHAR(255) DEFAULT NULL,             -- Dữ liệu cá nhân (PII): Mã hóa đối xứng AES-256-CBC
    address TEXT DEFAULT NULL,                   -- Dữ liệu cá nhân (PII): Mã hóa đối xứng AES-256-CBC
    avatar VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> **Giải thích kỹ thuật bảo mật:**
> - `id VARCHAR(36)`: Không dùng `INT AUTO_INCREMENT` (1, 2, 3...) để ngăn chặn hoàn toàn kẻ xấu quét dữ liệu khách hàng bằng cách tăng dần số ID (lỗ hổng IDOR - Insecure Direct Object References).
> - `password_hash VARCHAR(255)`: Dùng hàm `password_hash($raw, PASSWORD_BCRYPT, ['cost' => 12])`. Độ dài lưu trữ chuẩn 60 ký tự. Cơ chế Slow KDF khiến hacker dùng dàn máy GPU đào vét cũng mất hàng trăm năm.
> - `phone` & `address`: Định dạng lưu trong MySQL có tiền tố `ENC:` kết hợp Base64 của IV (16 bytes) và Ciphertext. Dù hacker dump được toàn bộ file `.sql` của MySQL cũng không đọc được số điện thoại hay địa chỉ khách hàng.

---

#### Bảng 2: `shops` - Gian Hàng Người Bán (Seller Centre)

```sql
CREATE TABLE IF NOT EXISTS shops (
    id VARCHAR(36) PRIMARY KEY,                  -- UUIDv4 định danh gian hàng
    user_id VARCHAR(36) NOT NULL,               -- Liên kết tài khoản chủ shop
    shop_name VARCHAR(150) NOT NULL,
    shop_slug VARCHAR(150) NOT NULL UNIQUE,      -- Đường dẫn thân thiện SEO & bảo mật URL
    description TEXT,
    rating DECIMAL(2,1) DEFAULT 5.0,
    status ENUM('ACTIVE', 'PENDING', 'SUSPENDED') DEFAULT 'ACTIVE',
    avatar VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

#### Bảng 3: `categories` - Danh Mục Sản Phẩm Đa Ngành

```sql
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'bi-tag',
    type ENUM('TECH', 'DIY_HANDMADE', 'LIFESTYLE', 'FASHION') DEFAULT 'TECH'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

#### Bảng 4: `products` - Sản Phẩm (Hỗ Trợ Công Nghệ & Thủ Công DIY)

```sql
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(36) PRIMARY KEY,                  -- UUIDv4 định danh sản phẩm
    shop_id VARCHAR(36) NOT NULL,                -- Ràng buộc với gian hàng sở hữu
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    price DECIMAL(12,0) NOT NULL,               -- Lưu số nguyên VNĐ, tránh sai số số thực float
    original_price DECIMAL(12,0) DEFAULT 0,
    stock INT NOT NULL DEFAULT 10,
    sku VARCHAR(100) DEFAULT NULL,
    is_diy BOOLEAN DEFAULT FALSE,                -- Phân biệt đồ sáng tạo DIY và linh kiện công nghệ
    diy_craft_time VARCHAR(50) DEFAULT NULL,    -- Thời gian chế tác thủ công
    attributes_json JSON DEFAULT NULL,           -- Cấu hình linh hoạt thuộc tính sản phẩm
    image VARCHAR(500) DEFAULT NULL,
    description TEXT,
    status ENUM('ACTIVE', 'OUT_OF_STOCK', 'HIDDEN') DEFAULT 'ACTIVE',
    sales_count INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 5.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

#### Bảng 5: `user_payment_tokens` - Token Hóa Thẻ Theo Chuẩn PCI-DSS Level 1

```sql
CREATE TABLE IF NOT EXISTS user_payment_tokens (
    token_id VARCHAR(36) PRIMARY KEY,            -- UUIDv4
    user_id VARCHAR(36) NOT NULL,
    gateway_provider VARCHAR(20) NOT NULL,       -- VNPAY, STRIPE, PAYPAL
    payment_token VARCHAR(255) NOT NULL UNIQUE,  -- Token đại diện do cổng thanh toán cấp
    card_brand VARCHAR(20) NOT NULL,             -- VISA, MASTERCARD, JCB
    last_four_digits CHAR(4) NOT NULL,           -- 4 số cuối mặt thẻ phục vụ nhận diện
    expiry_month SMALLINT NOT NULL,
    expiry_year SMALLINT NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> **Giải thích kỹ thuật PCI-DSS:**
> Hệ thống **tuyệt đối không bao giờ chạm hoặc lưu trữ số thẻ tín dụng (PAN 16 chữ số) và mã bảo mật bí mật CVV/CVC** trong cơ sở dữ liệu. Toàn bộ thông tin nhạy cảm của thẻ được người dùng nhập trực tiếp trên Iframe/Gateway có chứng chỉ PCI-DSS của đối tác. Máy chủ chỉ lưu `payment_token` vô giá trị bên ngoài cổng thanh toán.

---

#### Bảng 6: `orders` - Đơn Hàng & Kiểm Soát Thanh Toán

```sql
CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(36) PRIMARY KEY,                  -- UUIDv4 chống đoán đơn hàng
    order_code VARCHAR(30) NOT NULL UNIQUE,      -- Mã đơn hiển thị (VD: ORD-2026-9901)
    buyer_id VARCHAR(36) NOT NULL,
    shop_id VARCHAR(36) NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(255) NOT NULL,                 -- Mã hóa AES-256-CBC
    address TEXT NOT NULL,                       -- Mã hóa AES-256-CBC
    payment_method VARCHAR(50) NOT NULL DEFAULT 'COD', -- VNPAY, MOMO, ZALOPAY, PAYPAL, COD
    payment_status ENUM('PENDING', 'PAID', 'REFUNDED') DEFAULT 'PENDING',
    -- State Machine 8 trạng thái đồng bộ vận chuyển 3PL GHN
    fulfillment_status ENUM(
        'AWAITING_FULFILLMENT',
        'READY_TO_PICK',
        'PICKING',
        'IN_TRANSIT',
        'OUT_FOR_DELIVERY',
        'DELIVERED',
        'DELIVERY_FAILED',
        'RETURNED'
    ) DEFAULT 'AWAITING_FULFILLMENT',
    total_amount DECIMAL(12,0) NOT NULL,        -- Số tiền chuẩn dùng đối soát với Webhook/IPN
    shipping_fee DECIMAL(12,0) DEFAULT 30000,
    shipping_carrier VARCHAR(50) DEFAULT 'GHN Express',
    tracking_code VARCHAR(50) DEFAULT NULL,     -- Mã vận đơn 3PL (GHN Tracking Code)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (shop_id) REFERENCES shops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

#### Bảng 7: `order_items` - Chi Tiết Đơn Hàng

```sql
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(36) NOT NULL,
    product_id VARCHAR(36) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(12,0) NOT NULL,
    quantity INT NOT NULL,
    variant_info VARCHAR(150) DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

#### Bảng 8: `delivery_trackings` - Lịch Trình Vận Đơn 3PL (GHN)

```sql
CREATE TABLE IF NOT EXISTS delivery_trackings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(36) NOT NULL,
    status VARCHAR(50) NOT NULL,
    status_title VARCHAR(150) NOT NULL,
    description VARCHAR(255) NOT NULL,
    location VARCHAR(150) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 1.3. Lệnh DML Seeders Dữ Liệu Khởi Tạo (Chuẩn Hóa Mã Hóa Dữ Liệu)

```sql
USE shop_db;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE delivery_trackings;
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE user_payment_tokens;
TRUNCATE TABLE products;
TRUNCATE TABLE categories;
TRUNCATE TABLE shops;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Nạp Tài Khoản Mẫu (Mật khẩu 'Password@123' băm Bcrypt Cost 12; SĐT và Địa chỉ mã hóa AES-256)
INSERT INTO users (id, username, email, password_hash, full_name, role, phone, address) VALUES
('usr-admin-001', 'admin', 'admin@attmdt.vn', '$2y$12$sVVqQgD1PO3dhZXlWhmA3.te.WmgFQrAAQx4a4r7mJwYG4EjIgGqm', 'Hệ Thống Quản Trị Sàn', 'ADMIN', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ=='),
('usr-seller-001', 'tech_seller', 'seller.tech@attmdt.vn', '$2y$12$sVVqQgD1PO3dhZXlWhmA3.te.WmgFQrAAQx4a4r7mJwYG4EjIgGqm', 'Trần Văn Công Nghệ', 'SELLER', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ=='),
('usr-seller-002', 'diy_maker', 'seller.diy@attmdt.vn', '$2y$12$sVVqQgD1PO3dhZXlWhmA3.te.WmgFQrAAQx4a4r7mJwYG4EjIgGqm', 'Lê Thị Sáng Tạo DIY', 'SELLER', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ=='),
('usr-buyer-001', 'khachhang1', 'buyer@attmdt.vn', '$2y$12$sVVqQgD1PO3dhZXlWhmA3.te.WmgFQrAAQx4a4r7mJwYG4EjIgGqm', 'Nguyễn Thị Thu Hà', 'BUYER', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ==');

-- 2. Nạp Gian Hàng (Shops)
INSERT INTO shops (id, user_id, shop_name, shop_slug, description, rating, status, avatar) VALUES
('shp-tech-001', 'usr-seller-001', 'TechZone Pro Official', 'techzone-pro', 'Gian hàng linh kiện công nghệ, đồ điện tử bảo hành 24 tháng.', 4.9, 'ACTIVE', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=200'),
('shp-diy-002', 'usr-seller-002', 'Mộc & Sáng Tạo DIY Studio', 'moc-sang-tao-diy', 'Xưởng chế tác đồ thủ công, quà tặng gỗ Epoxy và bàn phím Custom.', 5.0, 'ACTIVE', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=200');

-- 3. Nạp Danh Mục Sản Phẩm
INSERT INTO categories (id, name, slug, icon, type) VALUES
(1, 'Thiết Bị Công Nghệ', 'thiet-bi-cong-nghe', 'laptop', 'TECH'),
(2, 'Phụ Kiện & Gaming Gear', 'phu-kien-gaming', 'controller', 'TECH'),
(3, 'Đồ Thủ Công & Decor DIY', 'do-thu-cong-diy', 'palette', 'DIY_HANDMADE'),
(4, 'Bàn Phím Custom & DIY Kit', 'ban-phim-diy-kit', 'keyboard', 'DIY_HANDMADE'),
(5, 'Đời Sống & Gia Dụng', 'doi-song-gia-dung', 'house-heart', 'LIFESTYLE');

-- 4. Nạp Sản Phẩm
INSERT INTO products (id, shop_id, category_id, name, slug, price, original_price, stock, sku, is_diy, diy_craft_time, attributes_json, image, description, sales_count, rating) VALUES
('prd-tech-001', 'shp-tech-001', 1, 'Laptop MacBook Air M2 13.6 Inch', 'macbook-air-m2', 24900000, 27900000, 15, 'MBA-M2-256', FALSE, NULL, '{"chip":"Apple M2","ram":"8GB","ssd":"256GB"}', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600', 'Thiết kế nhôm nguyên khối siêu mỏng nhẹ, pin 18 tiếng.', 42, 4.9),
('prd-tech-002', 'shp-tech-001', 2, 'Tai Nghe Bluetooth True Wireless Chống Ồn ANC', 'tai-nghe-tws-anc', 650000, 950000, 50, 'TWS-ANC-PRO', FALSE, NULL, '{"bluetooth":"5.3","anc":"Active Noise Canceling"}', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600', 'Khử ồn chủ động ANC -35dB, thời lượng pin 28h.', 128, 4.8),
('prd-diy-001', 'shp-diy-002', 3, 'Đèn Ngủ Gỗ Me Tây & Nhựa Epoxy Thủ Công (DIY Art)', 'den-ngu-go-epoxy-diy', 450000, 590000, 12, 'DIY-LAMP-EPX', TRUE, '1-2 ngày', '{"material":"Gỗ Me Tây + Epoxy Resin"}', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=600', 'Sản phẩm chế tác thủ công độc bản từ gỗ tự nhiên.', 36, 5.0);
```

---

### 1.4. Kỹ Thuật Viết Truy Vấn An Toàn Tuyệt Đối (PDO Prepared Statements)

Hệ thống **không bao giờ ghép chuỗi trực tiếp (String Concatenation)** trong bất kỳ câu lệnh SQL nào.

```php
// VÍ DỤ 1: Kiểm tra đăng nhập (Chống SQL Injection dạng ' OR '1'='1)
$stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username OR email = :email LIMIT 1");
$stmt->execute([
    ':username' => $inputIdentifier,
    ':email'    => $inputIdentifier
]);
$user = $stmt->fetch();

// VÍ DỤ 2: Cập nhật trạng thái đơn hàng khi nhận thông báo thanh toán
$stmt = $pdo->prepare("UPDATE orders SET payment_status = :status, updated_at = NOW() WHERE order_code = :order_code AND payment_status != 'PAID'");
$stmt->execute([
    ':status'     => 'PAID',
    ':order_code' => $validOrderCode
]);
```

---

## PHẦN II: CƠ CHẾ BẢO VỆ API CỦA HỆ THỐNG (BLUE TEAM PERSPECTIVE)

Hệ thống bảo vệ các cổng API Endpoint (nhận phản hồi IPN / Webhook / Callback từ VNPAY, MoMo, ZaloPay, PayPal, GHN) qua 5 tầng phòng thủ chủ động:

```
[ Request từ Client / Cổng TT ]
             │
             ▼
┌───────────────────────────────────────────────┐
│ TẦNG 1: Cloudflare WAF + TLS 1.3 Encrypted     │ ➔ Lọc DDoS L3/L4/L7, ẩn IP gốc máy chủ
└──────────────────────┬────────────────────────┘
                       │
                       ▼
┌───────────────────────────────────────────────┐
│ TẦNG 2: Nginx Rate Limiting (limit_req)        │ ➔ Chống vét cạn Brute Force & DoS (10 req/s)
└──────────────────────┬────────────────────────┘
                       │
                       ▼
┌───────────────────────────────────────────────┐
│ TẦNG 3: Kiểm Tra Chữ Ký Số HMAC / Token Bí Mật │ ➔ HMAC-SHA512 (VNPAY), HMAC-SHA256 (MoMo/Zalo)
└──────────────────────┬────────────────────────┘
                       │
                       ▼
┌───────────────────────────────────────────────┐
│ TẦNG 4: Đối Soát Toàn Vẹn Số Tiền Trong CSDL  │ ➔ So sánh amount với orders.total_amount
└──────────────────────┬────────────────────────┘
                       │
                       ▼
┌───────────────────────────────────────────────┐
│ TẦNG 5: Khóa Lũy Kế Idempotency Lock          │ ➔ Chống tấn công phát lại (Replay Attack)
└───────────────────────────────────────────────┘
```

### 1. Xác Thực Tính Toàn Vẹn Bằng Chữ Ký Số HMAC (Chống Giả Mạo & Sửa Đổi Dữ Liệu)
- **VNPAY:** Khi nhận request trả về qua URL, máy chủ lọc bỏ trường `vnp_SecureHash`, sắp xếp toàn bộ tham số `vnp_*` theo thứ tự chữ cái A-Z, mã hóa URL-encode, và tạo chuỗi băm `hash_hmac('sha512', $hashData, $vnp_HashSecret)`. So sánh với chữ ký gửi về bằng thuật toán so sánh thời gian bất biến (`hash_equals()`) để chống **Timing Attack**.
- **MoMo:** Kiểm tra chữ ký `hash_hmac('sha256', "accessKey=...&amount=...&orderId=...", $secretKey)`.
- **ZaloPay:** Kiểm tra mã chứng thực `mac = hash_hmac('sha256', "{$app_id}|{$app_trans_id}|{$pmcid}|{$bank_code}|{$amount}|{$discount_amount}|{$status}", $key2)`.
- **PayPal:** Sử dụng mô hình **Server-to-Server Tokenization**: Hệ thống tự sinh OAuth2 Bearer Token và gửi lệnh trực tiếp sang máy chủ PayPal `https://api-m.sandbox.paypal.com/v2/checkout/orders/{id}/capture` để xác thực, loại bỏ hoàn toàn khả năng can thiệp từ phía Client.

### 2. Chống Sửa Đổi Số Tiền (Data / Parameter Tampering)
- Hacker có thể cố tình sửa gói tin `amount=10000` trên trình duyệt để mua món hàng 24.900.000đ.
- **Cơ chế Blue Team:** Dù chữ ký cổng thanh toán có hợp lệ, khi nhận gói tin, máy chủ luôn chạy câu lệnh SQL truy vấn số tiền gốc:
  ```php
  if ((int)$webhookPayload['amount'] !== (int)$order['total_amount']) {
      // Báo động can thiệp dữ liệu -> Lập tức từ chối và ghi log cảnh báo
      http_response_code(400);
      exit;
  }
  ```

### 3. Chống Tấn Công Phát Lại (Replay Attack) & Khóa Lũy Kế (Idempotency Lock)
- Nếu kẻ tấn công bắt được một gói tin Webhook thành công trong quá khứ và phát lại (Replay) liên tục nhằm làm sai lệch doanh thu hoặc gây lỗi hàng tồn kho:
- **Cơ chế Blue Team:** Máy chủ kiểm tra trạng thái hiện tại của đơn hàng. Nếu `payment_status === 'PAID'`, hệ thống lập tức trả về mã xác nhận thành công mà **không thực thi lại** bất kỳ logic trừ kho hay cộng tiền nào, đảm bảo tính chất **Idempotent** (thực thi nhiều lần kết quả vẫn không đổi).

### 4. Giới Hạn Tần Suất Truy Cập (Rate Limiting Tầng Web Server Nginx)
Cấu hình trực tiếp trong file `/etc/nginx/sites-available/default`:
```nginx
# Tạo vùng nhớ 10MB lưu vết IP truy cập
limit_req_zone $binary_remote_addr zone=api_gateway_limit:10m rate=10r/s;

location /index.php {
    # Áp dụng giới hạn: Tối đa 10 request/giây, bộ đệm tối đa 5 request
    limit_req zone=api_gateway_limit burst=5 nodelay;
    
    fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    include fastcgi_params;
}
```
Khi một địa chỉ IP cố tình gửi request ồ ạt để quét lỗ hổng hoặc DoS, Nginx tự động trả về ngay **HTTP 429 Too Many Requests** trước khi request kịp chạm đến mã nguồn PHP.

---

## PHẦN III: CƠ CHẾ BẢO VỆ ỨNG DỤNG WEB (WEB HARDENING & OWASP)

### 1. Tầng Mạng & Cloudflare WAF (Ẩn Danh Máy Chủ)
- Tên miền `shop.itgslab.io.vn` hoạt động thông qua kết nối an toàn **Cloudflare Tunnel (`cloudflared`)**.
- IP công khai thật của server tại trường (`113.23.12.157`) và IP nội bộ mạng LAN (`192.168.1.54`) hoàn toàn không bị lộ ra Internet.
- Mọi gói tin truyền tải trên đường truyền bắt buộc phải sử dụng mã hóa **TLS 1.3 / HTTPS với tiêu chuẩn HSTS**, triệt tiêu nguy cơ nghe lén Man-in-the-middle (MITM).

### 2. Triệt Tiêu Lỗ Hổng XSS (Cross-Site Scripting - OWASP A03)
Tất cả các biến đầu ra do người dùng nhập (Tên khách hàng, đánh giá, mã đơn, nội dung tìm kiếm) đều bắt buộc đi qua hàm khử khuẩn ký tự đặc biệt:
```php
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
```
Biến các ký tự nguy hiểm `<script>`, `"`, `'`, `&` thành các thực thể an toàn `&lt;script&gt;`, ngăn chặn hoàn toàn việc thực thi mã JavaScript độc hại trên trình duyệt của nạn nhân.

### 3. Phòng Vệ CSRF (Cross-Site Request Forgery - OWASP A01)
Mọi form thay đổi trạng thái (Đặt hàng, Thanh toán, Đăng ký Shop, Đổi mật khẩu) đều được gắn kèm một mã băm ngẫu nhiên sinh bằng hàm an toàn mật mã học:
```php
// Sinh token trong Session
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Xác thực khi người dùng POST dữ liệu
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    die("Yêu cầu không hợp lệ: Lỗi xác thực CSRF Token!");
}
```

### 4. An Toàn Phiên Làm Việc (Session Security & Cookie Hardening)
Trong cấu hình khởi tạo Session:
```php
session_start([
    'cookie_httponly' => true,      // JavaScript trên trình duyệt không thể đọc được Cookie (chống đánh cắp Session qua XSS)
    'cookie_secure'   => true,      // Cookie chỉ được truyền qua kết nối mã hóa HTTPS
    'cookie_samesite' => 'Strict',  // Chống gửi Cookie từ trang web khác sang (chống CSRF)
]);

// Định kỳ đổi ID phiên sau khi đăng nhập để chống Session Fixation:
session_regenerate_id(true);
```

### 5. Che Giấu Vết Lỗi (Stealth Mode & Production Error Handling)
- Khi người dùng không có quyền quản trị cố tình truy cập vào URL `?page=admin`, hệ thống không hiện thông báo "Bạn không có quyền", mà trả về **HTTP 404 Not Found (Stealth Mode)** để kẻ tấn công nghĩ rằng trang web không hề tồn tại chức năng quản trị.
- Trên môi trường chạy thật, tắt cờ hiển thị lỗi `display_errors = Off` trong `php.ini`. Khi hệ thống gặp lỗi cú pháp hay ngoại lệ CSDL, người dùng chỉ thấy trang thông báo thân thiện; mã lỗi chi tiết chỉ được ghi âm thầm vào `/var/log/nginx/error.log` trên máy chủ.

---

## PHẦN IV: THÔNG TIN TÍCH HỢP CÁC CỔNG DỊCH VỤ THỰC TẾ

| Dịch Vụ | Loại Hình | Thông Số Kỹ Thuật / API Key | Cơ Chế Xác Thực & Endpoint |
| :--- | :--- | :--- | :--- |
| **VNPAY Sandbox** | Cổng thanh toán thẻ ATM / QR | `vnp_TmnCode: I25YOYNN`<br>`vnp_HashSecret: CZXUZBJEIEXPSXUTFNMJKSVSOJQJSQZM` | Chữ ký HMAC-SHA512<br>Endpoint: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html` |
| **MoMo Sandbox** | Ví điện tử MoMo | `PartnerCode: MOMOBKUN20180529`<br>`AccessKey: klm05TvNBzhg7h7j`<br>`SecretKey: at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa` | Chữ ký HMAC-SHA256<br>Endpoint: `https://test-payment.momo.vn/v2/gateway/api/create` |
| **ZaloPay Sandbox** | Ví điện tử ZaloPay | `AppID: 2553`<br>`Key1: PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL`<br>`Key2: kLtgPl8YESDmyABkQgeZByOUJsbcpNI2` | Chữ ký HMAC-SHA256 MAC<br>Endpoint: `https://sb-openapi.zalopay.vn/v2/create` |
| **PayPal Sandbox** | Cổng thanh toán quốc tế | `ClientID: BAArLb0vA72Dxn...`<br>`SecretKey: EBQXD_o31UAYjbQ...` | OAuth2 Bearer Token & Capture<br>Endpoint: `https://api-m.sandbox.paypal.com` |
| **GHN Express** | Vận chuyển 3PL | `ClientID: 5573761` \| `ShopID: 6723370`<br>`Token: f172c112-78d7-4a3e-b3c7-ef230f0814d1` | REST API Token Header `token`<br>Endpoint: `https://online-gateway.ghn.vn/shiip/public-api/v2/` |

---

## PHẦN V: KIẾN TRÚC & TRIỂN KHAI CÂN BẰNG TẢI THỰC TẾ (LOAD BALANCER & 2 WORKER NODES)

Hệ thống đã triển khai thành công mô hình **Cân Bằng Tải Lớp 7 (Layer 7 Reverse Proxy Load Balancer)** với 2 Worker Nodes hoạt động song song theo thời gian thực:

```
                  [ CLIENT / CLOUDFLARE EDGE ]
                                │
                                ▼
         ┌──────────────────────────────────────────────┐
         │     APACHE REVERSE PROXY LOAD BALANCER       │
         │      (Cổng tiếp nhận chính: Port 80)         │
         │   Thuật toán: Round-Robin (lbmethod_byrequests)│
         └──────────────┬────────────────┬──────────────┘
                        │ (50% traffic)  │ (50% traffic)
                        ▼                ▼
         ┌────────────────────────┐    ┌────────────────────────┐
         │     WORKER NODE 1      │    │     WORKER NODE 2      │
         │   (Cổng nội bộ 8081)   │    │   (Cổng nội bộ 8082)   │
         │  Header: Node-1 (8081) │    │  Header: Node-2 (8082) │
         └───────────┬────────────┘    └───────────┬────────────┘
                     │                             │
                     └──────────────┬──────────────┘
                                    ▼
                     ┌─────────────────────────────┐
                     │    SHARED STORAGE & DATA    │
                     │  - Session: /var/lib/php/...│
                     │  - Database: MySQL shop_db  │
                     └─────────────────────────────┘
```

### 5.1. Thông Số Triển Khai Thực Tế

| Thành Phần | Cổng Dịch Vụ | Vai Trò & Cơ Chế | File Nhật Ký (Log File) |
| :--- | :--- | :--- | :--- |
| **Load Balancer** | `Port 80` | Tiếp nhận traffic từ Cloudflare, điều phối Round-Robin | `/var/log/apache2/loadbalancer_access.log` |
| **Worker Node 1** | `Port 8081` | Xử lý request, phục vụ mã nguồn PHP & MVC | `/var/log/apache2/node1_access.log` |
| **Worker Node 2** | `Port 8082` | Xử lý request song song, dự phòng chịu lỗi (Failover) | `/var/log/apache2/node2_access.log` |
| **Web GUI Quản Trị** | `/balancer-manager` | Dashboard trực quan quản lý Load Balancer | Hiển thị Elected count, Byte transfer, Status |

### 5.2. Giao Diện Quản Trị Trực Quan (Balancer Manager Web GUI)
- **Đường dẫn truy cập:** `https://shop.itgslab.io.vn/balancer-manager` (hoặc `http://localhost/balancer-manager`).
- **Khả năng điều phối:**
  - Hiển thị bảng trạng thái thời gian thực của `node1` (`127.0.0.1:8081`) và `node2` (`127.0.0.1:8082`).
  - Cột **Elected**: Số lượng request thực tế được chia đều 50% cho từng Node.
  - Cho phép người quản trị click chọn Node và chỉnh sửa trạng thái (**Disable / Drain / Enable**) ngay lập tức để chứng minh tính năng **Chịu Lỗi / Sẵn Sàng Cao (High Availability & Failover)**: Khi Node 1 bị tắt, 100% request tự động chuyển sang Node 2 mà website của khách hàng không bị gián đoạn.

### 5.3. Các Câu Lệnh Kiểm Thử Thực Tế Cho Giảng Viên Xem

**1. Kiểm tra header phản hồi luân phiên giữa 2 Node:**
```bash
curl -I -s https://shop.itgslab.io.vn/ | grep -E "x-backend-server|HTTP"
curl -I -s https://shop.itgslab.io.vn/ | grep -E "x-backend-server|HTTP"
```
*Kết quả:*
- Lần 1: `x-backend-server: Node-1 (Port 8081)`
- Lần 2: `x-backend-server: Node-2 (Port 8082)`

**2. Mở 2 cửa sổ Terminal theo dõi Live Logs (Xem request nhảy luân phiên giữa 2 Node):**
- Terminal 1 (Node 1):
  ```bash
  tail -f /var/log/apache2/node1_access.log
  ```
- Terminal 2 (Node 2):
  ```bash
  tail -f /var/log/apache2/node2_access.log
  ```
Khi người dùng F5 tải lại trang trên điện thoại hoặc trình duyệt, Terminal 1 và Terminal 2 sẽ lần lượt nhảy log xen kẽ nhau!

---

## TỔNG KẾT

Tài liệu này cung cấp bức tranh hoàn chỉnh và chuẩn mực kỹ thuật của hệ thống dưới góc độ **Blue Team**. Mọi khía cạnh từ cơ sở dữ liệu MySQL, cơ chế mã hóa mật mã học (Bcrypt 12, AES-256-CBC, HMAC-SHA256/512), tầng phòng thủ Nginx & Cloudflare, đến quy trình xử lý đơn hàng, đối soát tài chính và **Cụm Cân Bằng Tải 2 Node** đều được cài đặt tự động, chặt chẽ trong mã nguồn và hạ tầng máy chủ, không cần bất kỳ công cụ diễn xuất nhân tạo nào.
