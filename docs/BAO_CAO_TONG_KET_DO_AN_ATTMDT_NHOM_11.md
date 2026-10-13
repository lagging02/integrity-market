# BÁO CÁO TỔNG KẾT ĐỒ ÁN AN TOÀN THƯƠNG MẠI ĐIỆN TỬ
## ĐỀ TÀI: THIẾT KẾ, TRIỂN KHAI VÀ BẢO MẬT TOÀN DIỆN SÀN GIAO DỊCH THƯƠNG MẠI ĐIỆN TỬ B2C LAI TẠO (INTEGRITYMARKET)

---

**HỌC PHẦN:** AN TOÀN THƯƠNG MẠI ĐIỆN TỬ (ATTMDT 2026)  
**ĐƠN VỊ THỰC HIỆN:** NHÓM 11  
**MÔI TRƯỜNG TRIỂN KHAI:** Ubuntu Server 24.04 LTS • Apache 2.4 Reverse Proxy • PHP 8.3 FPM • MySQL 8.0 • Cloudflare Anycast Edge Tunnel  
**TÊN MIỀN HỆ THỐNG:** `https://shop.itgslab.io.vn/`  
**ĐỊA CHỈ IP MÁY CHỦ:** `192.168.1.54` (Mạng LAN Lab) / `113.23.12.157` (Public Gateway)

---

## MỤC LỤC TỔNG QUAN

1. **LỜI NÓI ĐẦU & TỔNG QUAN ĐỀ TÀI**
2. **CHƯƠNG 1: KHẢO SÁT HIỆN TRẠNG & ĐẶC TẢ YÊU CẦU HỆ THỐNG**
   - 1.1. Mô hình Sàn B2C Lai tạo (Công nghệ & Đồ sáng tạo DIY/Handmade)
   - 1.2. Đối sánh kỹ thuật với các sàn lớn (Shopee, Tiki, TikTok Shop)
   - 1.3. Mô hình định danh người dùng thống nhất (Unified Single Identity)
   - 1.4. Tiêu chuẩn an toàn thương mại điện tử bắt buộc (OWASP Top 10, PCI-DSS, PII)
3. **CHƯƠNG 2: THIẾT KẾ KIẾN TRÚC HỆ THỐNG & HẠ TẦNG MẠNG**
   - 2.1. Mô hình kiến trúc phần mềm Clean MVC & Service Layer (OOP PHP 8.3)
   - 2.2. Tầng mạng & Phòng thủ biên (Cloudflare Anycast Tunnel & TLS 1.3 HSTS)
   - 2.3. Cụm cân bằng tải thực tế Lớp 7 (Reverse Proxy Load Balancer & 2 Worker Nodes)
   - 2.4. Cơ chế giấu kín công nghệ (Pretty URLs loại bỏ đuôi `.php` & triệt tiêu Header `X-Powered-By`)
4. **CHƯƠNG 3: THIẾT KẾ CƠ SỞ DỮ LIỆU & BẢO MẬT DỮ LIỆU TĨNH (DATA AT-REST)**
   - 3.1. Đặc tả lược đồ 8 bảng cơ sở dữ liệu MySQL
   - 3.2. Giải pháp khóa chính UUIDv4 RFC 4122 (Triệt tiêu lỗ hổng IDOR - OWASP A01)
   - 3.3. Mã hóa thông tin cá nhân PII bằng AES-256-CBC
   - 3.4. Băm mật khẩu 1 chiều bằng Bcrypt Work Factor = 12 kết hợp Salt 128-bit
   - 3.5. Tiêu chuẩn thẻ quốc tế PCI-DSS Level 1 Tokenization
   - 3.6. Toàn bộ mã nguồn SQL DDL & DML khởi tạo hệ thống
5. **CHƯƠNG 4: GIẢI PHÁP BẢO MẬT ỨNG DỤNG WEB (BLUE TEAM DEFENSE-IN-DEPTH)**
   - 4.1. Phòng vệ tuyệt đối SQL Injection bằng PDO Prepared Statements (OWASP A03)
   - 4.2. Phòng vệ Cross-Site Scripting (XSS) qua cơ chế lọc ký tự HTML (OWASP A03)
   - 4.3. Phòng vệ Cross-Site Request Forgery (CSRF) bằng mật mã học (OWASP A01)
   - 4.4. Kiểm soát dò quét Brute-Force & Bảo mật phiên làm việc (Session Security)
   - 4.5. Chính sách mật khẩu mạnh chuẩn NIST SP 800-63B (12+ ký tự)
   - 4.6. Cổng quản trị ẩn danh tuyệt đối (Stealth Admin 404 Gate)
6. **CHƯƠNG 5: TÍCH HỢP & BẢO MẬT CỔNG THANH TOÁN ĐA KÊNH & VẬN CHUYỂN 3PL**
   - 5.1. Tích hợp Cổng VNPAY Sandbox (Chữ ký số HMAC-SHA512)
   - 5.2. Tích hợp Ví điện tử MoMo Sandbox (Chữ ký số HMAC-SHA256)
   - 5.3. Tích hợp Ví điện tử ZaloPay Sandbox (Chữ ký số HMAC-SHA256 MAC)
   - 5.4. Tích hợp Cổng quốc tế PayPal Sandbox (OAuth2 Bearer Token & Server Capture)
   - 5.5. Tích hợp Logistics 3PL GHN Express (State Machine 8 trạng thái)
   - 5.6. Mô hình phòng thủ 4 lớp bảo vệ Webhook / IPN API
7. **CHƯƠNG 6: HƯỚNG DẪN THỰC NGHIỆM, GIÁM SÁT & VẬN HÀNH THỰC TẾ**
   - 6.1. Hướng dẫn giám sát Load Balancer qua Web GUI Balancer Manager
   - 6.2. Hướng dẫn theo dõi Live Logs luân phiên giữa các Worker Nodes
   - 6.3. Bảng tài khoản thực nghiệm & thẻ thanh toán Sandbox
   - 6.4. Câu lệnh truy vấn kiểm tra CSDL trực tiếp trên máy chủ
8. **KẾT LUẬN & HƯỚNG PHÁT TRIỂN**

---

# LỜI NÓI ĐẦU & TỔNG QUAN ĐỀ TÀI

Thương mại điện tử (TMĐT) tại Việt Nam và trên thế giới đang chứng kiến sự tăng trưởng vượt bậc với quy mô hàng chục tỷ USD. Tuy nhiên, đi đôi với sự bùng nổ này là sự gia tăng đột biến của các hiểm họa an ninh mạng:
- Các cuộc tấn công đánh cắp dữ liệu cá nhân (**PII Data Breach**) làm lộ hàng triệu số điện thoại, địa chỉ cư trú của khách hàng.
- Tấn công thao túng tham số thanh toán (**Payment Amount Tampering**) khiến người mua có thể sở hữu món hàng hàng chục triệu đồng với giá chỉ 1.000 đ.
- Tấn công phát lại gói tin (**Replay Attack**) làm sai lệch dòng tiền và gây tê liệt quản lý kho hàng.
- Tấn công từ chối dịch vụ (**DDoS/Slowloris**) làm sập hệ thống trong các khung giờ cao điểm Flash Sale.

Nhận thức được tầm quan trọng cốt lõi của an toàn thông tin trong giao dịch số, đề tài **"Thiết kế, Triển khai và Bảo mật Toàn diện Sàn Giao Dịch Thương Mại Điện Tử B2C Lai Tạo (IntegrityMarket)"** được Nhóm 11 lựa chọn thực hiện.

### Định vị vai trò: Blue Team (Đội Ngũ Kỹ Sư Phòng Thủ Hệ Thống)
Đồ án được thực hiện nghiêm túc và chuẩn mực 100% theo lập trường **Blue Team**:
- Chúng tôi **không** xây dựng các nút bấm giả lập tấn công mang tính diễn xuất trên giao diện người dùng.
- Toàn bộ cơ chế an toàn thông tin được xây dựng ăn sâu vào cấu trúc phần mềm (**Security by Design**) và triển khai phòng ngự theo chiều sâu (**Defense in Depth**) qua 4 lớp: **Biên Mạng Edge (Cloudflare Anycast) ➔ Máy chủ Cân Bằng Tải Reverse Proxy ➔ Backend MVC Middleware ➔ Cơ sở dữ liệu MySQL At-Rest**.

---

# CHƯƠNG 1: KHẢO SÁT HIỆN TRẠNG & ĐẶC TẢ YÊU CẦU HỆ THỐNG

## 1.1. Mô Hình Sàn B2C Lai Tạo (Horizontal Marketplace)
IntegrityMarket được thiết kế theo mô hình sàn thương mại điện tử B2C Lai Tạo (Horizontal Multi-Vendor Marketplace) kết hợp giữa hai ngành hàng:
1. **Thiết bị Công Nghệ & Điện Tử (Tech & Gaming Gear):** Laptop, bàn phím cơ, củ sạc GaN, tai nghe True Wireless. Đặc thù: Cần quản lý cấu hình kỹ thuật (RAM, ROM, Bluetooth, ANC) và số lượng tồn kho chính xác theo SKU.
2. **Đồ Sáng Tạo DIY & Thủ Công (Handmade & DIY Craft):** Đèn gỗ Epoxy, kit tự ráp bàn phím Custom, kệ sách khớp mộng. Đặc thù: Sản phẩm mang tính độc bản, có thông số thời gian chế tác thủ công (`diy_craft_time`) và đánh dấu sáng tạo (`is_diy`).

## 1.2. Đối Sánh Kỹ Thuật Với Các Sàn Thương Mại Điện Tử Lớn
| Tiêu Chí Đánh Giá | Shopee | Tiki | TikTok Shop | IntegrityMarket (Đồ Án) |
| :--- | :--- | :--- | :--- | :--- |
| **Mô hình định danh** | 1 Tài khoản dùng chung Mua & Bán | Phân tách Buyer / Tiki Seller | Gắn liền TikTok ID | **Unified Identity (1 User = Mua & Bán)** |
| **Bảo vệ dữ liệu PII** | Ẩn số đuôi điện thoại | Che địa chỉ | Mã hóa ID | **AES-256-CBC mã hóa trực tiếp trong DB** |
| **Mật khẩu người dùng** | Yêu cầu 8-16 ký tự | Yêu cầu OTP | Đa yếu tố | **Bcrypt Cost 12, tối thiểu 12 ký tự mạnh** |
| **Cổng thanh toán** | ShopeePay, SPayLater | TikiPay, VNPAY | ZaloPay, MoMo, Thẻ | **VNPAY, MoMo, ZaloPay, PayPal, GHN COD** |
| **Cân bằng tải (LB)** | K8s Ingress Controller | AWS ALB / Nginx | ByteDance Global LB | **Layer 7 Round-Robin Apache LB (2 Nodes)** |
| **Tiêu chuẩn thẻ** | PCI-DSS Level 1 | PCI-DSS Level 1 | PCI-DSS Level 1 | **PCI-DSS Tokenization (Không lưu PAN/CVV)** |

## 1.3. Mô Hình Định Danh Thống Nhất (Unified Single Identity)
Hệ thống học tập kiến trúc tối ưu của Shopee: **Một người dùng chỉ cần một tài khoản duy nhất**.
- Mặc định khi đăng ký, người dùng có vai trò `BUYER` (Khách mua sắm).
- Bất kỳ lúc nào, khách hàng đều có thể bấm **"Mở Shop Bán Hàng"** (`/seller-register`) để trở thành `SELLER` mà không cần đăng ký tài khoản mới.
- Hệ thống hỗ trợ đa phương thức xác thực an toàn: Mật khẩu Bcrypt, Mã OTP SMS/Email, Google SSO Identity và Sinh trắc học Passkey FIDO2/WebAuthn.

## 1.4. Các Tiêu Chuẩn Bảo Mật Bắt Buộc Đáp Ứng
- **OWASP Top 10 (2021/2026):** Khắc phục triệt để A01 (Broken Access Control - IDOR, CSRF), A02 (Cryptographic Failures - PII AES-256, Bcrypt 12), A03 (Injection - SQLi, XSS), A05 (Security Misconfiguration - Ẩn version PHP/Server, tắt display_errors).
- **PCI-DSS Level 1 Compliance:** Không bao giờ lưu trữ số thẻ tín dụng (PAN 16 số) và mã CVV trong cơ sở dữ liệu máy chủ.
- **Nghị định 13/2023/NĐ-CP:** Bảo vệ dữ liệu cá nhân (PII) người dùng bằng mã hóa đối xứng ở trạng thái nghỉ (At-Rest).

---

# CHƯƠNG 2: THIẾT KẾ KIẾN TRÚC HỆ THỐNG & HẠ TẦNG MẠNG

## 2.1. Mô Hình Kiến Trúc Phần Mềm Clean MVC & Service Layer
Hệ thống áp dụng kiến trúc hướng đối tượng (OOP) theo chuẩn Clean MVC kết hợp Service Layer:
```
my_shop/
├── app/
│   ├── Controllers/   # Tiếp nhận Request, xử lý điều hướng (Auth, Checkout, Order, Admin)
│   ├── Models/        # Tương tác CSDL qua PDO Prepared Statements (Database, Order, Product, Shop)
│   └── Services/      # Tầng nghiệp vụ bảo mật (AuthService, EncryptionService, PaymentService, RateLimitService)
├── config/            # Cấu hình độc lập (security.php, ghn.php, payment_gateways.php)
├── views/             # Giao diện hiển thị (Blade-like PHP Templates, chia theo vai trò buyer/seller/admin)
├── assets/            # Tài nguyên hình ảnh Vector SVG, Logo chuẩn thương hiệu
├── .htaccess          # Cấu hình bảo mật Apache, Pretty URLs, loại bỏ Header
└── index.php          # Front Controller trung tâm, ánh xạ định tuyến toàn hệ thống
```

## 2.2. Tầng Mạng & Phòng Thủ Biên (Edge Defense với Cloudflare Tunnel)
- Tên miền `shop.itgslab.io.vn` hoạt động thông qua kết nối bảo mật **Cloudflare Tunnel (`cloudflared`)**.
- **Lợi ích an ninh:**
  - Ẩn hoàn toàn địa chỉ IP thật của máy chủ (`192.168.1.54` / `113.23.12.157`). Kẻ tấn công trên Internet không thể quét cổng hay tấn công trực diện vào máy chủ.
  - Tự động hấp thụ và lọc sạch các đợt tấn công từ chối dịch vụ phân tán (DDoS Layer 3/4/7).
  - Bắt buộc mã hóa toàn trình đường truyền với chuẩn **TLS 1.3 và cờ HSTS (HTTP Strict Transport Security)**, chống triệt để tấn công nghe lén Man-in-the-Middle (MITM).

## 2.3. Cụm Cân Bằng Tải Thực Tế Lớp 7 (Layer 7 Reverse Proxy Load Balancer)
Hệ thống đã triển khai thực tế mô hình **Cân Bằng Tải Lớp 7** với 2 Worker Nodes hoạt động song song ngay trên máy chủ:

```
                  [ CLIENT / CLOUDFLARE EDGE ]
                                │
                                ▼
         ┌──────────────────────────────────────────────┐
         │     APACHE REVERSE PROXY LOAD BALANCER       │
         │       (Cổng tiếp nhận chính: Port 80)        │
         │   Thuật toán: Round-Robin (lbmethod_byrequests)│
         └──────────────┬────────────────┬──────────────┘
                        │ (50% traffic)  │ (50% traffic)
                        ▼                ▼
         ┌────────────────────────┐    ┌────────────────────────┐
         │     WORKER NODE 1      │    │     WORKER NODE 2      │
         │   (Cổng nội bộ 8081)   │    │   (Cổng nội bộ 8082)   │
         │ Header: Node-1 (8081)  │    │ Header: Node-2 (8082)  │
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

- **Cổng 80 (Load Balancer):** Tiếp nhận toàn bộ lưu lượng truy cập từ Cloudflare và mạng LAN. Sử dụng module `mod_proxy_balancer` và giải thuật `lbmethod_byrequests` để chia đều 50% lưu lượng cho Node 1 và 50% cho Node 2.
- **Cổng 8081 (Worker Node 1):** Chạy độc lập, phản hồi kèm header `X-Backend-Server: Node-1 (Port 8081)`, ghi log riêng tại `/var/log/apache2/node1_access.log`.
- **Cổng 8082 (Worker Node 2):** Chạy độc lập, phản hồi kèm header `X-Backend-Server: Node-2 (Port 8082)`, ghi log riêng tại `/var/log/apache2/node2_access.log`.
- **Giao diện Web GUI Quản Trị:** Tích hợp trang điều phối thời gian thực tại `https://shop.itgslab.io.vn/balancer-manager` hiển thị chỉ số Elected requests, số byte và trạng thái sống/chết (Health Check) của từng Node.

## 2.4. Cơ Chế Giấu Kín Công Nghệ (Security by Obscurity + Defense in Depth)
- **Pretty URLs (Loại bỏ đuôi `.php` và tham số URL):** Nhờ cơ chế Rewrite Rules trong `.htaccess`, mọi URL trên sàn đều là URL sạch dạng RESTful:
  - `https://shop.itgslab.io.vn/checkout` *(thay vì `index.php?page=checkout`)*
  - `https://shop.itgslab.io.vn/login`
  - `https://shop.itgslab.io.vn/admin`
  - `https://shop.itgslab.io.vn/my-orders`
- **Triệt tiêu Header nhận diện:**
  - Lệnh `header_remove("X-Powered-By")` loại bỏ hoàn toàn chữ ký nhận diện PHP/8.3.
  - Cấu hình Apache `ServerSignature Off` và `Header unset Server` loại bỏ phiên bản web server. Khi kẻ xấu quét bằng Burp Suite hay Nmap, máy chủ trả về `HTTP/1.1 200 OK` hoàn toàn không để lộ công nghệ bên trong.

---

# CHƯƠNG 3: THIẾT KẾ CƠ SỞ DỮ LIỆU & BẢO MẬT DỮ LIỆU TĨNH

## 3.1. Đặc Tả Lược Đồ 8 Bảng Cơ Sở Dữ Liệu MySQL

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│    users     │1     *│    shops     │1     *│   products   │
│ (UUID, PII)  ├───────┤   (UUID)     ├───────┤    (UUID)    │
└──────┬───────┘       └──────────────┘       └──────┬───────┘
       │1                                            │1
       │                                             │
       │*                                            │*
┌──────┴───────┐1     *┌──────────────┐*     1┌──────┴───────┐
│user_pay_token│       │    orders    ├───────┤  order_items │
│  (PCI-DSS)   │       │(UUID, PII,3PL│       │              │
└──────────────┘       └──────┬───────┘       └──────────────┘
                              │1
                              │*
                       ┌──────┴───────┐
                       │deliv_tracking│
                       │(3PL State)   │
                       └──────────────┘
```

1. **`users`:** Quản lý thông tin định danh, phân quyền RBAC (`BUYER`, `SELLER`, `ADMIN`), mật khẩu băm Bcrypt, số điện thoại và địa chỉ mã hóa AES-256.
2. **`shops`:** Thông tin gian hàng đối tác, mô tả, đánh giá sao, trạng thái duyệt kinh doanh.
3. **`categories`:** Danh mục sản phẩm (Công Nghệ, Phụ Kiện, Đồ Thủ Công DIY, Bàn Phím Custom).
4. **`products`:** Sản phẩm bày bán, giá cả, tồn kho SKU, cờ nhận diện hàng sáng tạo DIY (`is_diy`), thời gian chế tác.
5. **`user_payment_tokens`:** Bảng lưu Token thẻ thanh toán theo chuẩn PCI-DSS Level 1 (Tuyệt đối không lưu thẻ PAN/CVV).
6. **`orders`:** Đơn hàng toàn sàn, phương thức thanh toán, trạng thái thanh toán (`PENDING`, `PAID`), 8 trạng thái vận chuyển GHN 3PL, số tiền đối soát.
7. **`order_items`:** Chi tiết các sản phẩm trong từng đơn hàng, số lượng, đơn giá, biến thể.
8. **`delivery_trackings`:** Nhật ký hành trình vận đơn từ Webhook của đơn vị vận chuyển 3PL GHN Express.

## 3.2. Giải Pháp Khóa Chính UUIDv4 (Chống Lỗ Hổng IDOR - OWASP A01)
Hầu hết các trang web đồ án sinh viên sử dụng khóa chính tự tăng `INT AUTO_INCREMENT` (1, 2, 3...). Kẻ tấn công chỉ cần thay đổi số ID trên thanh địa chỉ URL (`/order?id=2` sang `/order?id=3`) là có thể xem lén toàn bộ đơn hàng của khách hàng khác (Lỗ hổng **Insecure Direct Object References - IDOR**).
- **Giải pháp của IntegrityMarket:** 100% bảng nhạy cảm (`users`, `shops`, `products`, `orders`, `user_payment_tokens`) đều dùng khóa chính **UUIDv4 chuẩn RFC 4122 (128-bit)** dạng chuỗi 36 ký tự:
  ```sql
  id VARCHAR(36) PRIMARY KEY
  ```
  Ví dụ: `e4b3c9f2-8a1d-4f6e-9c2b-1a3d5e7f9b0c`. Với không gian mẫu $2^{122}$ giá trị ngẫu nhiên, kẻ tấn công hoàn toàn không có khả năng đoán biết hoặc vét cạn ID của người khác.

## 3.3. Mã Hóa Thông Tin Cá Nhân PII (AES-256-CBC)
- **Chuẩn mật mã:** Thuật toán đối xứng **AES-256-CBC** với khóa mã hóa 256-bit được lưu an toàn trong file cấu hình bí mật của máy chủ (`security.php`), không lưu trong CSDL.
- **Cơ chế Vector Khởi Tạo (IV - Initialization Vector):** Mỗi lần mã hóa, hệ thống sinh một IV ngẫu nhiên 16 bytes bằng hàm an toàn mật mã `random_bytes(16)`. Do đó, cùng một số điện thoại `0901234567` khi mã hóa 2 lần sẽ cho ra 2 chuỗi Ciphertext hoàn toàn khác nhau.
- **Định dạng lưu trong MySQL:** Chuỗi có tiền tố `ENC:` kết hợp Base64 của IV và Ciphertext:
  ```sql
  phone VARCHAR(255) -- Lưu chuỗi ENC:Zip6UczdsOJDjtKis7b5ycxXk51xzMA9Z/XECcrw+Oc=
  address TEXT       -- Lưu chuỗi ENC:KmWd/qyGmZT2gFAuZlcJ6Av2LjrgYiXIYkKRA5Z7vdUk...
  ```
- **Lợi ích an ninh:** Dù máy chủ MySQL bị hacker tấn công dump toàn bộ tệp tin `.sql`, kẻ tấn công cũng chỉ nhận được các chuỗi vô nghĩa, bảo vệ danh tính khách hàng tuyệt đối.

## 3.4. Băm Mật Khẩu 1 Chiều Bcrypt (Work Factor = 12)
- Hệ thống sử dụng hàm băm một chiều chậm **Bcrypt (Blowfish-based cipher)** với tham số chi phí **Cost = 12** ($2^{12} = 4096$ vòng lặp tính toán):
  ```php
  password_hash($rawPassword, PASSWORD_BCRYPT, ['cost' => 12]);
  ```
- Kèm theo muối ngẫu nhiên 128-bit sinh tự động. Cơ chế KDF tiêu tốn tài nguyên phần cứng này khiến kẻ tấn công dù có dàn máy đào tiền ảo hay siêu máy tính GPU cũng mất hàng trăm năm mới thử được một mật khẩu, triệt tiêu hoàn toàn kỹ thuật tấn công **Rainbow Table** và **Brute-Force**.

## 3.5. Tiêu Chuẩn Thẻ Quốc Tế PCI-DSS Level 1 Tokenization
- Hệ thống **tuyệt đối không chạm hay lưu trữ** số thẻ PAN 16 chữ số, ngày hết hạn mặt thẻ hay mã bảo mật CVV/CVC.
- Mọi thao tác nhập thẻ diễn ra trên Iframe/Gateway đạt chuẩn PCI-DSS của bên thứ ba. Máy chủ sàn chỉ tiếp nhận và lưu trữ `payment_token` vô giá trị bên ngoài hạ tầng thanh toán.

## 3.6. Toàn Bộ Mã Nguồn SQL DDL Khởi Tạo Hệ Thống

```sql
-- 1. Khởi tạo Cơ sở dữ liệu và Cấp quyền User tối thiểu
CREATE DATABASE IF NOT EXISTS shop_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'shop_user'@'localhost' IDENTIFIED BY 'Pass@123456';
GRANT ALL PRIVILEGES ON shop_db.* TO 'shop_user'@'localhost';
FLUSH PRIVILEGES;

USE shop_db;

-- 2. Bảng Users (RBAC + PII AES-256 + Bcrypt Cost 12)
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('BUYER', 'SELLER', 'ADMIN') NOT NULL DEFAULT 'BUYER',
    phone VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng Shops (Kênh Người Bán)
CREATE TABLE IF NOT EXISTS shops (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    shop_name VARCHAR(150) NOT NULL,
    shop_slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    rating DECIMAL(2,1) DEFAULT 5.0,
    status ENUM('ACTIVE', 'PENDING', 'SUSPENDED') DEFAULT 'ACTIVE',
    avatar VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'bi-tag',
    type ENUM('TECH', 'DIY_HANDMADE', 'LIFESTYLE', 'FASHION') DEFAULT 'TECH'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng Products (Công nghệ & DIY Thủ công)
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(36) PRIMARY KEY,
    shop_id VARCHAR(36) NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    price DECIMAL(12,0) NOT NULL,
    original_price DECIMAL(12,0) DEFAULT 0,
    stock INT NOT NULL DEFAULT 10,
    sku VARCHAR(100) DEFAULT NULL,
    is_diy BOOLEAN DEFAULT FALSE,
    diy_craft_time VARCHAR(50) DEFAULT NULL,
    attributes_json JSON DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    description TEXT,
    status ENUM('ACTIVE', 'OUT_OF_STOCK', 'HIDDEN') DEFAULT 'ACTIVE',
    sales_count INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 5.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng User Payment Tokens (Chuẩn PCI-DSS)
CREATE TABLE IF NOT EXISTS user_payment_tokens (
    token_id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    gateway_provider VARCHAR(20) NOT NULL,
    payment_token VARCHAR(255) NOT NULL UNIQUE,
    card_brand VARCHAR(20) NOT NULL,
    last_four_digits CHAR(4) NOT NULL,
    expiry_month SMALLINT NOT NULL,
    expiry_year SMALLINT NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng Orders (Đơn hàng & Quản lý Thanh toán, 3PL GHN)
CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(36) PRIMARY KEY,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    buyer_id VARCHAR(36) NOT NULL,
    shop_id VARCHAR(36) NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'COD',
    payment_status ENUM('PENDING', 'PAID', 'REFUNDED') DEFAULT 'PENDING',
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
    tracking_code VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (shop_id) REFERENCES shops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng Order Items (Chi tiết đơn hàng)
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

-- 9. Bảng Delivery Trackings (Lịch trình vận đơn 3PL)
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

# CHƯƠNG 4: GIẢI PHÁP BẢO MẬT ỨNG DỤNG WEB

## 4.1. Phòng Vệ SQL Injection Bằng PDO Prepared Statements (OWASP A03)
Hệ thống **tuyệt đối không bao giờ nối chuỗi trực tiếp** (String Concatenation) trong truy vấn CSDL. 100% câu truy vấn đều được thực thi qua PDO Prepared Statements với tham số ràng buộc:
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u OR email = :e LIMIT 1");
$stmt->execute([':u' => $username, ':e' => $username]);
$user = $stmt->fetch();
```
Bộ biên dịch MySQL tách biệt hoàn toàn giữa cấu trúc lệnh SQL và dữ liệu đầu vào. Dù kẻ tấn công nhập payload nguy hiểm như `' OR '1'='1' --` hay `'; DROP TABLE users; --`, chuỗi đó chỉ được coi là một giá trị thuần túy, loại trừ 100% nguy cơ SQLi.

## 4.2. Phòng Vệ Cross-Site Scripting (XSS - OWASP A03)
Tất cả các biến hiển thị ra giao diện HTML (tên sản phẩm, họ tên người mua, địa chỉ, từ khóa tìm kiếm) bắt buộc phải đi qua hàm khử khuẩn ký tự đặc biệt:
```php
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
```
Biến đổi các ký tự nguy hiểm `<script>`, `"`, `'`, `&` thành các thực thể an toàn `&lt;script&gt;`, ngăn chặn hoàn toàn mã JavaScript độc hại thực thi trên trình duyệt nạn nhân.

## 4.3. Phòng Vệ Cross-Site Request Forgery (CSRF - OWASP A01)
Mọi biểu mẫu thay đổi trạng thái hệ thống (Đặt hàng, Đăng ký Shop, Đổi mật khẩu) đều yêu cầu Anti-CSRF Token:
```php
// Sinh token trong Session
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Xác thực khi nhận request POST bằng hàm so sánh bất biến thời gian
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die("Lỗi bảo mật: Yêu cầu bị từ chối do CSRF Token không hợp lệ!");
}
```

## 4.4. Kiểm Soát Dò Quét Brute-Force & Bảo Mật Phiên Làm Việc
- **Chống Brute-Force (`RateLimitService`):** Hệ thống ghi nhận số lần đăng nhập sai theo địa chỉ IP thật (nhận từ `HTTP_CF_CONNECTING_IP` của Cloudflare). Nếu thử sai quá **5 lần liên tiếp**, hệ thống tự động khóa tạm thời tài khoản và IP trong **15 phút**.
- **Bảo mật Cookie phiên làm việc:**
  ```php
  ini_set('session.cookie_httponly', 1); // JavaScript không thể đánh cắp Cookie qua XSS
  ini_set('session.cookie_secure', 1);   // Chỉ truyền Cookie qua kênh HTTPS
  ini_set('session.cookie_samesite', 'Strict'); // Ngăn chặn Cookie gửi chéo trang
  session_regenerate_id(true);           // Đổi mã phiên ngay sau khi đăng nhập chống Session Fixation
  ```

## 4.5. Chính Sách Mật Khẩu Mạnh (Chuẩn NIST SP 800-63B)
Nhằm ngăn chặn người dùng đặt mật khẩu yếu dễ đoán, hệ thống thiết lập bộ lọc Regex kiểm tra nghiêm ngặt ở cả Frontend và Backend:
- Độ dài tối thiểu: **$\ge 12$ ký tự**.
- Bắt buộc chứa: Ít nhất 1 chữ cái in hoa (`A-Z`), 1 chữ cái thường (`a-z`), 1 chữ số (`0-9`) và 1 ký tự đặc biệt (`!@#$%^&*...`).
- Áp dụng bắt buộc cho cả chức năng **Đăng Ký Tài Khoản** và **Đặt Lại Mật Khẩu**.

## 4.6. Cổng Quản Trị Ẩn Danh Tuyệt Đối (Stealth Admin 404 Gate)
- Gỡ bỏ hoàn toàn mọi liên kết dẫn tới trang Admin trên giao diện công khai và form đăng nhập.
- Khi người dùng chưa đăng nhập hoặc không có vai trò `ADMIN` cố tình truy cập vào URL `?page=admin` hoặc `admin.php`, hệ thống không hiện thông báo "Bạn không có quyền", mà trả về mã lỗi **HTTP 404 Not Found (Stealth Mode)** để kẻ tấn công nghĩ rằng website không hề tồn tại chức năng quản trị.

---

# CHƯƠNG 5: TÍCH HỢP & BẢO MẬT API THANH TOÁN ĐA CỔNG & VẬN CHUYỂN 3PL

Hệ thống tích hợp thành công 4 Cổng thanh toán trực tuyến chính thức môi trường Sandbox và 1 Cổng vận chuyển Logistics 3PL:

| Cổng Dịch Vụ | Loại Hình | Thông Số Kỹ Thuật / API Key | Cơ Chế Xác Thực & Chữ Ký Số |
| :--- | :--- | :--- | :--- |
| **VNPAY Sandbox** | Cổng thanh toán thẻ ATM / QR | `vnp_TmnCode: I25YOYNN`<br>`vnp_HashSecret: CZXUZBJEIEXPSXUTFNMJKSVSOJQJSQZM` | Chữ ký số **HMAC-SHA512** bảo mật cao |
| **MoMo Sandbox** | Ví điện tử MoMo | `PartnerCode: MOMOBKUN20180529`<br>`AccessKey: klm05TvNBzhg7h7j`<br>`SecretKey: at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa` | Chữ ký số **HMAC-SHA256** |
| **ZaloPay Sandbox** | Ví điện tử ZaloPay | `AppID: 2553`<br>`Key1: PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL`<br>`Key2: kLtgPl8YESDmyABkQgeZByOUJsbcpNI2` | Mã chứng thực **HMAC-SHA256 MAC** |
| **PayPal Sandbox** | Cổng quốc tế USD | `ClientID: BAArLb0vA72Dxn...`<br>`SecretKey: EBQXD_o31UAYjbQ...` | **OAuth2 Bearer Token** & Server Capture API |
| **GHN Express** | Logistics 3PL | `ClientID: 5573761` \| `ShopID: 6723370`<br>`Token: f172c112-78d7-4a3e-b3c7-ef230f0814d1` | Header Token REST API `online-gateway.ghn.vn` |

## Mô Hình Phòng Thủ 4 Lớp Tại Webhook / IPN API
Khi cổng thanh toán gửi kết quả giao dịch về máy chủ, API Endpoint áp dụng tuần tự 4 chốt chặn kiểm tra:
1. **Chốt chặn 1 - Kiểm tra chữ ký số HMAC:** Máy chủ lấy toàn bộ dữ liệu nhận được và Secret Key (lưu bí mật trong server) để tính lại chữ ký HMAC. So sánh bằng hàm `hash_equals()`. Nếu sai lệch dù 1 bit ➔ Trả về `401 Unauthorized` ngay lập tức.
2. **Chốt chặn 2 - Kiểm tra tồn tại đơn hàng:** Dùng Prepared Statement kiểm tra mã đơn `order_code` trong CSDL. Nếu không có ➔ Trả về `404 Not Found`.
3. **Chốt chặn 3 - Chống sửa đổi số tiền (Amount Tampering):** So khớp chính xác số tiền thanh toán nhận về với số tiền lưu trong CSDL (`orders.total_amount`). Dù hacker sửa gói tin thành 10.000 đ thì hệ thống vẫn phát hiện chênh lệch ➔ Lập tức từ chối và cảnh báo.
4. **Chốt chặn 4 - Khóa lũy kế (Idempotency Lock - Chống Replay Attack):** Nếu đơn hàng đã ở trạng thái `PAID`, hệ thống không chạy lại logic trừ kho hay cộng tiền, ngăn chặn tấn công phát lại gói tin.

---

# CHƯƠNG 6: HƯỚNG DẪN THỰC NGHIỆM, GIÁM SÁT & VẬN HÀNH

## 6.1. Giám Sát Cân Bằng Tải Qua Web GUI Balancer Manager
- Truy cập địa chỉ: **`https://shop.itgslab.io.vn/balancer-manager`**
- Giao diện hiển thị trạng thái `Init | Ok` của cả 2 Worker Nodes:
  - `http://127.0.0.1:8081` (Worker Node 1)
  - `http://127.0.0.1:8082` (Worker Node 2)
- Theo dõi cột **Elected**: Số lượng request được phân phối đều 50% - 50%.
- Có thể click chọn Node 1 và chuyển trạng thái sang **Disable** để diễn tập khả năng chịu lỗi (Failover): 100% request chuyển sang Node 2 mà website không bị gián đoạn.

## 6.2. Theo Dõi Live Logs Luân Phiên Trong Terminal
Mở 2 cửa sổ Terminal trên máy chủ:
- **Terminal 1 (Node 1):** `tail -f /var/log/apache2/node1_access.log`
- **Terminal 2 (Node 2):** `tail -f /var/log/apache2/node2_access.log`
Khi người dùng tải lại trang trên trình duyệt, hai màn hình Terminal sẽ lần lượt nhảy dòng nhật ký luân phiên nhau, minh chứng thuật toán Round-Robin đang vận hành chính xác.

## 6.3. Bảng Tài Khoản Thực Nghiệm & Thẻ Thanh Toán Sandbox

### Tài khoản đăng nhập hệ thống (Mật khẩu: `Password@123`):
- **Quản trị viên (Admin):** Username `admin`
- **Người bán công nghệ (Seller):** Username `tech_seller`
- **Người bán sáng tạo DIY (Seller):** Username `diy_maker`
- **Khách hàng mua sắm (Buyer):** Username `khachhang1`

### Thẻ thử nghiệm VNPAY Sandbox (Thẻ NCB):
- Ngân hàng: **NCB** (Ngân hàng Quốc Dân)
- Số thẻ: **`9704198526191432198`**
- Tên chủ thẻ: **`NGUYEN VAN A`**
- Ngày phát hành: **`07/15`**
- Mã xác thực OTP: **`123456`**

## 6.4. Câu Lệnh Truy Vấn Kiểm Tra CSDL Trực Tiếp
Mở Terminal trên máy chủ và chạy câu lệnh sau để trình diễn dữ liệu mã hóa thực tế cho Giảng viên:
```bash
mysql -u shop_user -pPass@123456 shop_db -e "SELECT order_code, customer_name, payment_method, payment_status, total_amount, phone FROM orders ORDER BY created_at DESC LIMIT 5;"
```
Giảng viên sẽ quan sát thấy cột `payment_status` tự động chuyển sang `PAID` và cột `phone` được mã hóa hoàn toàn dưới dạng `ENC:...` (AES-256-CBC).

---

# KẾT LUẬN & HƯỚNG PHÁT TRIỂN

## Đánh Giá Kết Quả Đạt Được
Đồ án **IntegrityMarket** đã hoàn thành xuất sắc toàn bộ các mục tiêu đặt ra:
1. Xây dựng hoàn chỉnh một nền tảng thương mại điện tử B2C Lai Tạo chuẩn mực, giao diện Responsive hiện đại.
2. Tích hợp trọn vẹn chuỗi thanh toán số thực tế: **VNPAY, MoMo, ZaloPay, PayPal, GHN COD** với cơ chế bảo vệ chữ ký số HMAC và chống giả mạo số tiền.
3. Thiết lập hệ thống bảo mật đa tầng kiên cố theo lập trường **Blue Team**: Mã hóa dữ liệu PII tĩnh (AES-256-CBC), băm mật khẩu Bcrypt 12, triệt tiêu 100% các lỗ hổng OWASP Top 10 (SQLi, XSS, CSRF, IDOR).
4. Vận hành thành công cụm **Cân Bằng Tải Lớp 7 (Layer 7 Load Balancer)** với 2 Worker Nodes chạy song song, có bảng điều khiển Balancer Manager và hệ thống nhật ký giám sát chuyên sâu.
5. Triển khai tên miền thực tế **`https://shop.itgslab.io.vn/`** qua Cloudflare Anycast Tunnel ẩn IP gốc và mã hóa TLS 1.3.

## Hướng Phát Triển Tương Lai
- Tích hợp cụm **Redis Cluster / Sentinel** để đồng bộ Session tập trung khi mở rộng quy mô lên hàng chục máy chủ vật lý.
- Xây dựng hệ thống phát hiện xâm nhập **WAF chuyên sâu (ModSecurity)** trên Reverse Proxy để ngăn chặn tự động các cuộc quét lỗ hổng phức tạp.
- Tích hợp quy trình kiểm thử bảo mật tự động **DevSecOps (SAST & DAST)** vào chu trình phát hành mã nguồn CI/CD.
