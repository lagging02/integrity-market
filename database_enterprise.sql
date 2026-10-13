-- ======================================================================
-- NỀN TẢNG THƯƠNG MẠI ĐIỆN TỬ B2C LAI TẠO (MULTI-VENDOR MARKETPLACE)
-- Đáp ứng đặc tả kỹ thuật: Khảo Sát Hệ Thống TMĐT_Nhom11.docx
-- Chuẩn kiến trúc: B2C Lai Tạo (Công Nghệ + Đồ Sáng Tạo DIY/Handmade)
-- Tiêu chuẩn bảo mật: OWASP Top 10 (A01 RBAC, A02 AES-256 PII, A03 Prepared Statements)
-- Tiêu chuẩn thanh toán: PCI-DSS Level 1 Tokenization (1-Click Checkout)
-- Tiêu chuẩn vận chuyển: 3PL State Machine (GHN / GHTK 8 trạng thái)
-- ======================================================================

CREATE DATABASE IF NOT EXISTS shop_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'shop_user'@'localhost' IDENTIFIED BY 'Pass@123456';
GRANT ALL PRIVILEGES ON shop_db.* TO 'shop_user'@'localhost';
FLUSH PRIVILEGES;

USE shop_db;

-- 1. BẢNG NGƯỜI DÙNG (USERS) - RBAC PHÂN QUYỀN 3 VAI TRÒ
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY, -- UUIDv4 (Chống IDOR - Chương 5.1)
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Bcrypt Cost = 12 (Chương 5.2)
    full_name VARCHAR(150) NOT NULL,
    role ENUM('BUYER', 'SELLER', 'ADMIN') NOT NULL DEFAULT 'BUYER',
    phone VARCHAR(255) DEFAULT NULL, -- Mã hóa AES-256-CBC
    address TEXT DEFAULT NULL,       -- Mã hóa AES-256-CBC
    avatar VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. BẢNG GIAN HÀNG NGƯỜI BÁN (SHOPS / SELLER CENTRE)
CREATE TABLE IF NOT EXISTS shops (
    id VARCHAR(36) PRIMARY KEY, -- UUIDv4
    user_id VARCHAR(36) NOT NULL,
    shop_name VARCHAR(150) NOT NULL,
    shop_slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    rating DECIMAL(2,1) DEFAULT 5.0,
    status ENUM('ACTIVE', 'PENDING', 'SUSPENDED') DEFAULT 'ACTIVE',
    avatar VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. BẢNG DANH MỤC SẢN PHẨM (CATEGORIES - Đa ngành hàng: Công Nghệ, DIY/Handmade)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'bi-tag',
    type ENUM('TECH', 'DIY_HANDMADE', 'LIFESTYLE', 'FASHION') DEFAULT 'TECH'
);

-- 4. BẢNG SẢN PHẨM (PRODUCTS - Hỗ trợ đồ Công nghệ & Đồ sáng tạo DIY)
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(36) PRIMARY KEY, -- UUIDv4
    shop_id VARCHAR(36) NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    price DECIMAL(12,0) NOT NULL,
    original_price DECIMAL(12,0) DEFAULT 0,
    stock INT NOT NULL DEFAULT 10,
    sku VARCHAR(100) DEFAULT NULL,
    is_diy BOOLEAN DEFAULT FALSE, -- Đánh dấu đồ sáng tạo DIY / Thủ công
    diy_craft_time VARCHAR(50) DEFAULT NULL, -- Thời gian chế tác đồ thủ công (ví dụ: '1-2 ngày')
    attributes_json JSON DEFAULT NULL, -- Biến thể (RAM/ROM hoặc Màu sắc/Chất liệu thủ công)
    image VARCHAR(500) DEFAULT NULL,
    description TEXT,
    status ENUM('ACTIVE', 'OUT_OF_STOCK', 'HIDDEN') DEFAULT 'ACTIVE',
    sales_count INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 5.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 5. BẢNG TOKEN THANH TOÁN (USER_PAYMENT_TOKENS - Chuẩn PCI-DSS Tokenization, Chương 4.3)
CREATE TABLE IF NOT EXISTS user_payment_tokens (
    token_id VARCHAR(36) PRIMARY KEY, -- UUIDv4
    user_id VARCHAR(36) NOT NULL,
    gateway_provider VARCHAR(20) NOT NULL, -- STRIPE, VNPAY, NAPAS
    payment_token VARCHAR(255) NOT NULL UNIQUE, -- Token do cổng cấp (Server KHÔNG lưu số thẻ PAN/CVV)
    card_brand VARCHAR(20) NOT NULL, -- VISA, MASTERCARD, JCB
    last_four_digits CHAR(4) NOT NULL,
    expiry_month SMALLINT NOT NULL,
    expiry_year SMALLINT NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. BẢNG ĐƠN HÀNG (ORDERS) - Phân tách theo từng Shop người bán
CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(36) PRIMARY KEY, -- UUIDv4 (Chống IDOR - Chương 5.1)
    order_code VARCHAR(30) NOT NULL UNIQUE,
    buyer_id VARCHAR(36) NOT NULL,
    shop_id VARCHAR(36) NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(255) NOT NULL, -- Mã hóa AES-256-CBC
    address TEXT NOT NULL,       -- Mã hóa AES-256-CBC
    payment_method VARCHAR(50) NOT NULL DEFAULT 'COD',
    payment_status ENUM('PENDING', 'PAID', 'REFUNDED') DEFAULT 'PENDING',
    -- 8 trạng thái vận chuyển đồng bộ 3PL GHN/GHTK (Chương 6.3)
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
    total_amount DECIMAL(12,0) NOT NULL,
    shipping_fee DECIMAL(12,0) DEFAULT 30000,
    shipping_carrier VARCHAR(50) DEFAULT 'GHN Express',
    tracking_code VARCHAR(50) DEFAULT NULL, -- Mã vận đơn 3PL (Tracking ID)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (shop_id) REFERENCES shops(id)
);

-- 7. BẢNG CHI TIẾT ĐƠN HÀNG (ORDER_ITEMS)
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
);

-- 8. BẢNG LỊCH TRÌNH VẬN ĐƠN (DELIVERY_TRACKINGS - Webhook 3PL Logging, Chương 6.2)
CREATE TABLE IF NOT EXISTS delivery_trackings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(36) NOT NULL,
    status VARCHAR(50) NOT NULL,
    status_title VARCHAR(150) NOT NULL,
    description VARCHAR(255) NOT NULL,
    location VARCHAR(150) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- 9. BẢNG GIÁM SÁT ĐĂNG NHẬP & PHÒNG CHỐNG TẤN CÔNG BRUTE-FORCE (OWASP Top 10 - A07)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_success TINYINT(1) DEFAULT 0,
    INDEX idx_ip_time (ip_address, attempted_at),
    INDEX idx_user_time (username, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

