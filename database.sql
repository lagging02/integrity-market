-- Tạo database và phân quyền user
CREATE DATABASE IF NOT EXISTS shop_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'shop_user'@'localhost' IDENTIFIED BY 'Pass@123456';
GRANT ALL PRIVILEGES ON shop_db.* TO 'shop_user'@'localhost';
FLUSH PRIVILEGES;

USE shop_db;

-- 1. Bảng danh mục sản phẩm
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- 2. Bảng sản phẩm
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(12,0) NOT NULL,
    image VARCHAR(500),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Bảng đơn hàng
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'COD',
    total_amount DECIMAL(12,0) NOT NULL,
    status VARCHAR(50) DEFAULT 'Chờ xử lý',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Bảng chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    product_name VARCHAR(255),
    price DECIMAL(12,0),
    quantity INT
);

-- Xóa dữ liệu cũ nếu có
DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM products;
DELETE FROM categories;

-- Thêm danh mục mẫu
INSERT INTO categories (id, name) VALUES 
(1, 'Thời trang Nam Nữ'),
(2, 'Đồ công nghệ & Phụ kiện'),
(3, 'Gia dụng & Đời sống');

-- Thêm sản phẩm mẫu
INSERT INTO products (category_id, name, price, image, description) VALUES
(1, 'Áo Thun Polo Nam Classic Cotton', 189000, 'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?w=500&auto=format&fit=crop', 'Chất vải cá sấu cotton co giãn 4 chiều, mềm mịn, thoáng mát, lịch sự.'),
(1, 'Giày Sneaker Thể Thao Pro Sport', 420000, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop', 'Đế cao su non êm chân, bám đường tốt, thiết kế thể thao trẻ trung năng động.'),
(1, 'Balo Chống Nước Đa Năng 15.6 Inch', 279000, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop', 'Vải oxford chống thấm nước, nhiều ngăn chống sốc chuyên dụng cho laptop.'),
(2, 'Tai Nghe Bluetooth TWS Không Dây', 350000, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500&auto=format&fit=crop', 'Âm bass mạnh mẽ, chống ồn đàm thoại, pin dùng liên tục 6 tiếng kèm dock sạc 24h.'),
(2, 'Củ Sạc Nhanh 30W Type-C GaN Pro', 160000, 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=500&auto=format&fit=crop', 'Công nghệ GaN kích thước siêu nhỏ, hỗ trợ chuẩn sạc nhanh PD cho iPhone và Samsung.'),
(3, 'Bình Giữ Nhiệt Inox 304 (500ml)', 129000, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&auto=format&fit=crop', 'Lõi inox 304 an toàn thực phẩm, giữ nóng 8 giờ và giữ lạnh 12 giờ liên tục.');
