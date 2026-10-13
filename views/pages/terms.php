<?php
$pageTitle = "Quy Chế Hoạt Động & Điều Khoản Dịch Vụ - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 900px;">
    <div class="card border rounded-4 bg-white shadow-sm p-4 p-md-5">
        <div class="border-bottom pb-4 mb-4">
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-2">
                <i class="bi bi-journal-text me-1"></i> ĐIỀU KHOẢN & QUY CHẾ
            </span>
            <h2 class="fw-bold text-dark mb-1">Quy Chế Hoạt Động Sàn Thương Mại Điện Tử</h2>
            <div class="text-muted small">Cập nhật lần cuối: Tháng 10/2026 | Nhóm 11 - Đồ án An toàn thương mại điện tử</div>
        </div>

        <div class="content text-secondary lh-lg">
            <h5 class="fw-bold text-dark mb-3">1. Nguyên Tắc Chung</h5>
            <p>
                Sàn giao dịch Thương mại Điện tử <strong>IntegrityMarket</strong> là nền tảng trực tuyến kết nối giữa Người mua (Buyer) và Người bán (Seller) trong hai lĩnh vực chủ đạo: Thiết bị công nghệ chính hãng và Sản phẩm thủ công chế tác sáng tạo DIY.
            </p>

            <h5 class="fw-bold text-dark mt-4 mb-3">2. Quy Định Dành Cho Người Mua (Buyer)</h5>
            <ul>
                <li>Cung cấp thông tin nhận hàng chính xác (Họ tên, Số điện thoại, Địa chỉ) để phục vụ công tác giao vận.</li>
                <li>Thực hiện thanh toán theo đúng phương thức đã lựa chọn (COD hoặc Thanh toán chuyển khoản tự động qua VietQR / Thẻ Tokenization).</li>
                <li>Được quyền kiểm tra hàng hóa khi nhận (đồng kiểm) và yêu cầu đổi trả trong vòng 7 ngày nếu sản phẩm có lỗi từ nhà sản xuất hoặc hư hại do vận chuyển.</li>
            </ul>

            <h5 class="fw-bold text-dark mt-4 mb-3">3. Quy Định Dành Cho Người Bán (Seller)</h5>
            <ul>
                <li>Cam kết $100\%$ sản phẩm cung cấp là hàng chính hãng hoặc tác phẩm thủ công DIY đúng như mô tả và hình ảnh hiển thị trên gian hàng.</li>
                <li>Xử lý và đóng gói đơn hàng kịp thời trong vòng 24 giờ kể từ khi hệ thống thông báo đơn hàng mới.</li>
                <li>Tuân thủ chính sách bảo hành, hỗ trợ khách hàng và giải quyết khiếu nại văn minh, minh bạch.</li>
            </ul>

            <h5 class="fw-bold text-dark mt-4 mb-3">4. Cơ Chế Tính Tiền & An Toàn Giao Dịch</h5>
            <p>
                Nhằm bảo vệ tối đa tính toàn vẹn của giao dịch, mọi phép tính tổng giá trị đơn hàng, phí vận chuyển và áp dụng mã khuyến mãi đều được thực thi độc lập tại máy chủ trung tâm (Backend Calculation), ngăn chặn hoàn toàn các hành vi can thiệp sửa đổi giá ở phía trình duyệt (Price Tampering).
            </p>

            <h5 class="fw-bold text-dark mt-4 mb-3">5. Giải Quyết Tranh Chấp & Khiếu Nại</h5>
            <p>
                Khi phát sinh tranh chấp giữa Người mua và Người bán, Ban Quản trị sàn IntegrityMarket đóng vai trò trung gian hòa giải dựa trên bằng chứng vận đơn 3PL và lịch sử giao dịch ghi nhận trong Cơ sở dữ liệu.
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
