<?php
$pageTitle = "Chính Sách Bảo Mật Thông Tin - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 900px;">
    <div class="card border rounded-4 bg-white shadow-sm p-4 p-md-5">
        <div class="border-bottom pb-4 mb-4">
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-2">
                <i class="bi bi-shield-check me-1"></i> BẢO MẬT & QUYỀN RIÊNG TƯ
            </span>
            <h2 class="fw-bold text-dark mb-1">Chính Sách Bảo Mật Thông Tin Cá Nhân</h2>
            <div class="text-muted small">Cập nhật lần cuối: Tháng 10/2026 | Nhóm 11 - Đồ án An toàn thương mại điện tử</div>
        </div>

        <div class="content text-secondary lh-lg">
            <h5 class="fw-bold text-dark mb-3">1. Mục Đích Thu Thập Thông Tin Cá Nhân</h5>
            <p>
                IntegrityMarket cam kết bảo vệ thông tin định danh cá nhân (Personally Identifiable Information - PII) của quý khách hàng khi tham gia giao dịch trên nền tảng. Các thông tin được thu thập bao gồm:
            </p>
            <ul>
                <li><strong>Họ và tên, Email, Tên đăng nhập:</strong> Phục vụ xác thực danh tính và thông báo trạng thái đơn hàng.</li>
                <li><strong>Số điện thoại & Địa chỉ nhận hàng:</strong> Phục vụ trực tiếp cho quá trình tạo vận đơn và giao nhận hàng hóa qua đối tác 3PL (GHN Express).</li>
                <li><strong>Địa chỉ IP và Dấu thời gian (Timestamp):</strong> Ghi nhận nhật ký truy cập (Security Audit Logs) nhằm phát hiện và phòng chống các hành vi gian lận hoặc tấn công dò mật khẩu (Brute-Force).</li>
            </ul>

            <h5 class="fw-bold text-dark mt-4 mb-3">2. Tiêu Chuẩn & Cơ Chế Mã Hóa Dữ Liệu (Data at Rest)</h5>
            <p>
                Toàn bộ dữ liệu nhạy cảm của khách hàng được bảo vệ bằng các thuật toán mật mã học hiện đại nhất:
            </p>
            <div class="p-3 bg-light rounded-3 border mb-3 font-monospace small">
                <div>• <strong>Mã hóa Số điện thoại & Địa chỉ:</strong> Thuật toán đối xứng <code>AES-256-CBC</code> với Vector khởi tạo (IV) ngẫu nhiên 16 bytes cho từng bản ghi.</div>
                <div>• <strong>Băm Mật Khẩu:</strong> Thuật toán một chiều <code>Bcrypt Cost 12</code> kết hợp Salt ngẫu nhiên 22 ký tự, ngăn chặn hoàn toàn tấn công bảng Rainbow Table.</div>
                <div>• <strong>Quản Lý Khóa Bí Mật:</strong> Khóa mã hóa (Master Encryption Key) được lưu trữ độc lập ngoài Cơ sở dữ liệu.</div>
            </div>

            <h5 class="fw-bold text-dark mt-4 mb-3">3. Bảo Mật Thanh Toán & Chuẩn Tokenization (PCI-DSS)</h5>
            <p>
                Hệ thống <strong>tuyệt đối không lưu trữ</strong> số thẻ thanh toán (PAN), ngày hết hạn hay mã bảo mật CVV/CVC của khách hàng trên máy chủ CSDL:
            </p>
            <ul>
                <li>Mọi giao dịch thanh toán thẻ trực tuyến được xử lý qua công nghệ <strong>Tokenization</strong> của Cổng thanh toán quốc tế chuẩn PCI-DSS Level 1.</li>
                <li>Giao tiếp giữa máy chủ và Cổng thanh toán được ký số bằng mã băm bí mật <strong>HMAC-SHA256</strong> với cơ chế chống tấn công lặp (Idempotency Key).</li>
            </ul>

            <h5 class="fw-bold text-dark mt-4 mb-3">4. Chính Sách Cookie & An Toàn Kênh Truyền (Data in Transit)</h5>
            <ul>
                <li><strong>Cờ Cookie Bảo Vệ:</strong> Cookie phiên làm việc (Session Cookie) được thiết lập cờ <code>HttpOnly</code> và <code>SameSite=Lax</code>, ngăn chặn mã độc JavaScript (XSS) đánh cắp danh tính.</li>
                <li><strong>Mã Hóa Đường Truyền:</strong> Toàn bộ lưu lượng truy cập được mã hóa qua giao thức an toàn <code>TLS 1.3 / HTTPS</code> với chính sách bảo vệ nghiêm ngặt <code>HSTS (Strict-Transport-Security)</code>.</li>
            </ul>

            <h5 class="fw-bold text-dark mt-4 mb-3">5. Quyền Lợi Của Khách Hàng</h5>
            <p>
                Khách hàng có toàn quyền kiểm tra, cập nhật hoặc yêu cầu xóa dữ liệu cá nhân của mình khỏi hệ thống thông qua trang Quản lý tài khoản hoặc liên hệ trực tiếp với Ban Quản trị qua Kênh Hỗ Trợ 24/7.
            </p>
        </div>

        <div class="mt-4 pt-4 border-top text-center">
            <a href="index.php" class="btn btn-primary px-4 fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Quay lại trang chủ
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
