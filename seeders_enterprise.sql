USE shop_db;

-- Xóa dữ liệu cũ theo đúng thứ tự ràng buộc khóa ngoại
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

-- 1. NẠP USERS (Mật khẩu 'Password@123' băm Bcrypt Work Factor 12)
-- Bcrypt hash cho 'Password@123': $2y$12$e7iG/sI8b7u2wRkE2.aDk.wI8K9y/1xL4o4k3x1hP8u7iG/sI8b7u (hoặc chuỗi tương đương chuẩn PHP)
-- Chúng ta dùng chuỗi Bcrypt chuẩn sinh từ PHP:
-- $2y$12$K1A8rD0T9.jHqE1rP8i9Oe2hB5gV0wX7mY6zL4sK3jH2gF1eD0cB.
INSERT INTO users (id, username, email, password_hash, full_name, role, phone, address) VALUES
('usr-admin-001', 'admin', 'admin@attmdt.vn', '$2y$12$kPPkY9fOeclTWTvJcONxyuB2YeD78VQAnPowGzDBiMnWK.UDxms..', 'Hệ Thống Quản Trị Sàn', 'ADMIN', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ=='),
('usr-seller-001', 'tech_seller', 'seller.tech@attmdt.vn', '$2y$12$kPPkY9fOeclTWTvJcONxyuB2YeD78VQAnPowGzDBiMnWK.UDxms..', 'Trần Văn Công Nghệ', 'SELLER', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ=='),
('usr-seller-002', 'diy_maker', 'seller.diy@attmdt.vn', '$2y$12$kPPkY9fOeclTWTvJcONxyuB2YeD78VQAnPowGzDBiMnWK.UDxms..', 'Lê Thị Sáng Tạo DIY', 'SELLER', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ=='),
('usr-buyer-001', 'khachhang1', 'buyer@attmdt.vn', '$2y$12$kPPkY9fOeclTWTvJcONxyuB2YeD78VQAnPowGzDBiMnWK.UDxms..', 'Nguyễn Thị Thu Hà', 'BUYER', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ==');

-- 2. NẠP SHOPS (Kênh Người Bán)
INSERT INTO shops (id, user_id, shop_name, shop_slug, description, rating, status, avatar) VALUES
('shp-tech-001', 'usr-seller-001', 'TechZone Pro Official', 'techzone-pro', 'Gian hàng ủy quyền phân phối linh kiện điện tử, bàn phím cơ, đồ công nghệ chính hãng bảo hành 24 tháng.', 4.9, 'ACTIVE', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=200'),
('shp-diy-002', 'usr-seller-002', 'Mộc & Sáng Tạo DIY Studio', 'moc-sang-tao-diy', 'Xưởng thủ công chế tác sản phẩm gỗ Epoxy, đồ decor tự làm (DIY), bàn phím Custom và quà tặng độc bản.', 5.0, 'ACTIVE', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=200');

-- 3. NẠP DANH MỤC
INSERT INTO categories (id, name, slug, icon, type) VALUES
(1, 'Thiết Bị Công Nghệ', 'thiet-bi-cong-nghe', 'laptop', 'TECH'),
(2, 'Phụ Kiện & Gaming Gear', 'phu-kien-gaming', 'controller', 'TECH'),
(3, 'Đồ Thủ Công & Decor DIY', 'do-thu-cong-diy', 'palette', 'DIY_HANDMADE'),
(4, 'Bàn Phím Custom & DIY Kit', 'ban-phim-diy-kit', 'keyboard', 'DIY_HANDMADE'),
(5, 'Đời Sống & Gia Dụng', 'doi-song-gia-dung', 'house-heart', 'LIFESTYLE');

-- 4. NẠP SẢN PHẨM PHONG PHÚ (CÔNG NGHỆ + ĐỒ SÁNG TẠO DIY)
INSERT INTO products (id, shop_id, category_id, name, slug, price, original_price, stock, sku, is_diy, diy_craft_time, attributes_json, image, description, sales_count, rating) VALUES
-- Hàng Công Nghệ (Shop TechZone)
('prd-tech-001', 'shp-tech-001', 1, 'Laptop MacBook Air M2 13.6 Inch (8GB/256GB)', 'macbook-air-m2', 24900000, 27900000, 15, 'MBA-M2-256', FALSE, NULL, '{"chip":"Apple M2","ram":"8GB","ssd":"256GB","color":"Midnight"}', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600', 'Thiết kế nhôm nguyên khối siêu mỏng nhẹ 1.24kg, màn hình Liquid Retina tràn viền rực rỡ, pin trâu 18 tiếng liên tục.', 42, 4.9),
('prd-tech-002', 'shp-tech-001', 2, 'Tai Nghe Bluetooth True Wireless Chống Ồn ANC', 'tai-nghe-tws-anc', 650000, 950000, 50, 'TWS-ANC-PRO', FALSE, NULL, '{"bluetooth":"5.3","battery":"28h","anc":"Active Noise Canceling"}', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600', 'Công nghệ chống ồn chủ động ANC -35dB, âm bass lực, micro đàm thoại khử ồn AI, dock sạc hiển thị pin LED.', 128, 4.8),
('prd-tech-003', 'shp-tech-001', 2, 'Củ Sạc Nhanh 65W GaN Pro 3 Cổng (2 Type-C + 1 USB)', 'cu-sac-65w-gan', 350000, 480000, 80, 'GAN-65W-BLK', FALSE, NULL, '{"power":"65W","port":"3 Ports","tech":"GaN 5th Gen"}', 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600', 'Kích thước siêu nhỏ gọn nhờ công nghệ bán dẫn GaN thế hệ mới, hỗ trợ chuẩn PD 3.0 sạc siêu nhanh cho Laptop và Smartphone.', 215, 4.9),

-- Hàng Sáng Tạo DIY / Handmade (Shop Mộc & Sáng Tạo DIY Studio)
('prd-diy-001', 'shp-diy-002', 3, 'Đèn Ngủ Gỗ Me Tây & Nhựa Epoxy Thủ Công Tự Làm (DIY Art)', 'den-ngu-go-epoxy-diy', 450000, 590000, 12, 'DIY-LAMP-EPX', TRUE, '1-2 ngày', '{"material":"Gỗ Me Tây tự nhiên + Epoxy Resin","led":"Vàng ấm 3W","power":"USB 5V"}', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=600', 'Sản phẩm chế tác thủ công 100% từ phôi gỗ tự nhiên kết hợp keo Epoxy xuyên sáng. Mỗi chiếc đèn mang vân gỗ độc nhất vô nhị không đụng hàng.', 36, 5.0),
('prd-diy-002', 'shp-diy-002', 4, 'Bộ Kit Tự Lắp Ráp Bàn Phím Cơ Custom DIY (Gasket Mount)', 'kit-ban-phim-custom-diy', 990000, 1350000, 20, 'DIY-KB-KIT75', TRUE, 'Có sẵn Kit (Tự ráp)', '{"layout":"75%","mount":"Gasket Mount","hotswap":"3-pin / 5-pin","plate":"PC"}', 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600', 'Dành cho dân mê bàn phím cơ tự ráp: Tặng kèm đầy đủ Foam tiêu âm Poron, Switch Pad, Plate PC và dụng cụ mở switch, key puller.', 89, 4.9),
('prd-diy-003', 'shp-diy-002', 3, 'Kệ Sách Gỗ Thông Mini Lắp Ghép Mộng Gỗ Thủ Công DIY', 'ke-sach-go-thong-diy', 280000, 350000, 25, 'DIY-SHELF-WOD', TRUE, '1 ngày', '{"material":"Gỗ thông New Zealand","assemble":"Khớp mộng không dùng đinh"}', 'https://images.unsplash.com/photo-1534349762230-e0cadf78f5da?w=600', 'Thiết kế thông minh dùng khớp mộng truyền thống tự ghép nối dễ dàng trong 5 phút. Gỗ thông sấy khô chống mối mọt, sơn lau tự nhiên thơm nhẹ.', 64, 4.8);

-- 5. NẠP PAYMENT TOKEN (Chuẩn PCI-DSS Tokenization cho khách hàng mẫu)
INSERT INTO user_payment_tokens (token_id, user_id, gateway_provider, payment_token, card_brand, last_four_digits, expiry_month, expiry_year, is_default) VALUES
('tok-uuid-001', 'usr-buyer-001', 'STRIPE', 'tok_1N4XykLkdIwHu7ixXYZ12345', 'VISA', '4242', 12, 2028, TRUE),
('tok-uuid-002', 'usr-buyer-001', 'VNPAY', 'vnp_tok_991823719284712', 'MASTERCARD', '8899', 8, 2027, FALSE);

-- 6. NẠP ĐƠN HÀNG MẪU (Bao gồm theo dõi vận đơn 3PL GHN)
INSERT INTO orders (id, order_code, buyer_id, shop_id, customer_name, phone, address, payment_method, payment_status, fulfillment_status, total_amount, shipping_fee, shipping_carrier, tracking_code) VALUES
('ord-uuid-001', 'ORD-2026-9901', 'usr-buyer-001', 'shp-diy-002', 'Nguyễn Thị Thu Hà', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ==', 'TOKENIZATION_CARD', 'PAID', 'IN_TRANSIT', 480000, 30000, 'GHN Express', 'GHN882910VN'),
('ord-uuid-002', 'ORD-2026-9902', 'usr-buyer-001', 'shp-tech-001', 'Nguyễn Thị Thu Hà', 'ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=', 'ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk8vbzN6K1CTwWqITI6pcBNMufjFO46ZaPdMtP4BzkBQ==', 'VIETQR', 'PAID', 'AWAITING_FULFILLMENT', 680000, 30000, 'GHTK', 'GHTK192837VN');

-- 7. CHI TIẾT ĐƠN HÀNG
INSERT INTO order_items (order_id, product_id, product_name, price, quantity, variant_info) VALUES
('ord-uuid-001', 'prd-diy-001', 'Đèn Ngủ Gỗ Me Tây & Nhựa Epoxy Thủ Công Tự Làm (DIY Art)', 450000, 1, 'Mẫu Gỗ Tự Nhiên - Led Vàng'),
('ord-uuid-002', 'prd-tech-002', 'Tai Nghe Bluetooth True Wireless Chống Ồn ANC', 650000, 1, 'Màu Đen Nhám');

-- 8. NẠP HÀNH TRÌNH VẬN ĐƠN 3PL GHN (Chương 6.2)
INSERT INTO delivery_trackings (order_id, status, status_title, description, location) VALUES
('ord-uuid-001', 'AWAITING_FULFILLMENT', 'Đã xác nhận thanh toán', 'Người bán đã tiếp nhận đơn và đang chuẩn bị kiện hàng.', 'Xưởng Mộc & Sáng Tạo DIY Studio'),
('ord-uuid-001', 'READY_TO_PICK', 'Đã đóng gói & In tem', 'Đã tạo vận đơn GHN Express #GHN882910VN. Chờ bưu tá lấy hàng.', 'Kho Người Bán - Q. Bình Thạnh, TP.HCM'),
('ord-uuid-001', 'PICKING', 'Bưu tá đã lấy hàng', 'Nhân viên GHN Nguyễn Văn Bưu (0909112233) đã tiếp nhận kiện hàng.', 'Bưu cục GHN Bình Thạnh'),
('ord-uuid-001', 'IN_TRANSIT', 'Đang luân chuyển bưu kiện', 'Kiện hàng đã nhập kho trung chuyển phân loại tự động TP.HCM.', 'Kho Tổng GHN Tân Bình');
