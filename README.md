# Integrity Market - Sàn Thương Mại Điện Tử An Toàn (ATTMDT - Nhóm 11)

> **Môn học:** An Toàn Thương Mại Điện Tử (ATTMDT)  
> **Nhóm thực hiện:** Nhóm 11  
> **Kiến trúc:** Clean MVC (Model - View - Controller), Service Layer, OOP  
> **Nền tảng:** Apache 2.4 + PHP 8.3 + MariaDB (MySQL)  

---

## 🛡️ Điểm Nhấn Kiến Trúc & Tính Năng An Toàn

Hệ thống được thiết kế và triển khai tuân thủ nghiêm ngặt các tiêu chuẩn an toàn thông tin thương mại điện tử hiện đại:

1. **Bảo Mật Tầng Giao Vận (Transport Layer Security - TLS/HTTPS):**
   - **Vô hiệu hóa hoàn toàn TLS 1.0 và TLS 1.1:** Chỉ chấp nhận **TLS 1.2** và **TLS 1.3** với các bộ mã hóa mạnh (ECDHE + AES-GCM, DHE).
   - **Cơ chế HSTS (HTTP Strict Transport Security):** Thiết lập `max-age=31536000; includeSubDomains; preload` ngăn chặn tuyệt đối các cuộc tấn công **SSL Stripping** và hạ cấp giao thức (Downgrade Attack).
   - **Cưỡng chế HTTPS (301 Permanent Redirect):** Mọi truy vấn HTTP thông thường tự động được điều hướng sang cổng 443 an toàn.

2. **Bảo Vệ Phiên Làm Việc & Chống Đánh Cắp Cookie (Anti-Session Hijacking):**
   - Phiên đăng nhập (Session) được cấp phát tự động các cờ bảo vệ tối đa:
     - `Secure`: Cookie chỉ truyền tải qua kênh mã hóa HTTPS, không bị rò rỉ qua mạng mở.
     - `HttpOnly`: Ngăn chặn JavaScript độc hại truy cập qua lỗ hổng XSS (`document.cookie`).
     - `SameSite=Lax`: Chống tấn công giả mạo yêu cầu chéo trang (CSRF).
   - Chống **Session Fixation**: Tự động gọi `session_regenerate_id(true)` ngay khi người dùng đăng nhập thành công.

3. **Chống Lộ Thông Tin Backend (Information Disclosure Hardening):**
   - Loại bỏ hoàn toàn các header nhạy cảm: `X-Powered-By`, `Server`, `X-Backend-Server`, `X-Load-Balancer`.
   - Vô hiệu hóa Directory Browsing (`Options -Indexes`).
   - Xóa bỏ các thông báo lỗi chi tiết (Stack Trace, Database Error Messages), ghi nhật ký lỗi an toàn vào tệp riêng của hệ điều hành.

4. **Mã Hóa Dữ Liệu Lưu Trữ (Data-at-Rest Encryption - PII):**
   - Thông tin cá nhân nhạy cảm của khách hàng (Số điện thoại, Địa chỉ giao hàng) được mã hóa bằng thuật toán **AES-256-CBC** với IV 16-byte ngẫu nhiên trước khi lưu vào MySQL.
   - Khóa mã hóa được quản lý độc lập tại tệp cấu hình máy chủ, ngăn chặn lộ dữ liệu ngay cả khi cơ sở dữ liệu bị kết xuất trái phép (SQL Dump).

5. **Xác Thực Đa Yếu Tố & Sinh Trắc Học:**
   - Mã hóa mật khẩu chuẩn công nghiệp **Bcrypt** với Cost Factor = 12.
   - Hỗ trợ xác thực sinh trắc học **Passkey / FIDO2 / WebAuthn** (Vân tay, Face ID, Windows Hello).
   - Hỗ trợ xác thực mã OTP thời gian thực qua SMS / Email.

6. **Tích Hợp Cổng Thanh Toán & Vận Chuyển 3PL:**
   - Hỗ trợ COD, MoMo, VNPay, ZaloPay, PayPal Sandbox.
   - Tích hợp API Giao Hàng Nhanh (GHN) theo mô hình máy trạng thái (State Machine 8 bước).

---

## 📂 Cấu Trúc Thư Mục Dự Án

```text
├── app/
│   ├── Controllers/     # Bộ điều khiển: Admin, Auth, Cart, Checkout, Order, Payment, Product, Seller...
│   ├── Models/          # Mô hình dữ liệu: Database, User, Shop, Product, Order, Category
│   └── Services/        # Tầng nghiệp vụ: AuthService, CsrfService, EncryptionService, PaymentService...
├── assets/              # Tài nguyên giao diện: Logo SVG, hình ảnh cổng thanh toán, thumbnail
├── config/              # Tệp cấu hình: database.php, payment_gateways.php, ghn.php, security.php
├── docs/                # Toàn bộ tài liệu báo cáo kỹ thuật, hướng dẫn pentest & kịch bản kiểm thử
├── views/               # Giao diện người dùng: admin, auth, buyer, seller, errors, layouts, pages
├── .htaccess            # Cấu hình bảo mật Apache, Security Headers & URL Rewriting
├── .gitignore           # Danh sách loại trừ tệp rác, nhật ký và thông tin nhạy cảm
├── config.php           # Tệp khởi tạo cấu hình chung & hệ thống mã hóa PII
├── database_enterprise.sql # Lược đồ cơ sở dữ liệu hoàn chỉnh (UUID, RBAC, PII, 3PL)
├── seeders_enterprise.sql  # Dữ liệu mẫu khởi tạo cho hệ thống
├── index.php            # Điểm vào chính của ứng dụng (Front Controller Router)
└── README.md            # Tài liệu tổng quan dự án
```

---

## 🚀 Hướng Dẫn Cài Đặt & Triển Khai

### 1. Yêu Cầu Môi Trường
- Máy chủ Linux (Ubuntu 22.04 LTS / 24.04 LTS khuyến nghị).
- Web Server: Apache 2.4 (kích hoạt module `ssl`, `headers`, `rewrite`).
- PHP: 8.2 trở lên (với các extension: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd`, `zip`).
- Database: MySQL 8.0+ hoặc MariaDB 10.11+.

### 2. Thiết Lập Cơ Sở Dữ Liệu
```bash
mysql -u root -p < database_enterprise.sql
mysql -u root -p shop_db < seeders_enterprise.sql
```

### 3. Cấu Hình Ứng Dụng
Kiểm tra và cập nhật thông tin kết nối MySQL trong `config/database.php` và `config.php`:
```php
'host' => 'localhost',
'database' => 'shop_db',
'username' => 'shop_user',
'password' => 'Pass@123456',
```

### 4. Tài Khoản Thử Nghiệm Mẫu (Mật khẩu mặc định: `Password@123`)
- **Quản trị viên (Admin):** `admin`
- **Người bán hàng (Seller):** `tech_seller`
- **Khách hàng (Buyer):** `khachhang1`
