<?php
$pageTitle = "Kết Quả Thanh Toán Ví MoMo - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 650px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white text-center p-4 p-md-5">
        <?php if ($result['valid'] && $result['success']): ?>
            <!-- GIAO DỊCH THÀNH CÔNG -->
            <div class="mb-3">
                <div class="text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 72px; height: 72px; background-color: #a50064;">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #a50064;">Thanh Toán MoMo Thành Công!</h3>
            <p class="text-muted small">Giao dịch đã được Cổng thanh toán Ví điện tử MoMo xác nhận thành công.</p>

            <div class="bg-light rounded-3 p-3 text-start small border my-3">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mã Đơn Hàng Sàn:</span>
                    <strong class="text-dark">#<?= htmlspecialchars($result['order_code']) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mã Giao Dịch MoMo:</span>
                    <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($result['transaction_no'] ?: 'MOMO_SANDBOX') ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Phương Thức Thanh Toán:</span>
                    <span class="badge" style="background-color: #a50064;">Ví MoMo / ATM Sandbox</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Số Tiền Thanh Toán:</span>
                    <strong class="text-danger fs-6"><?= number_format($result['amount'], 0, ',', '.') ?> đ</strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Trạng Thái Xác Thực:</span>
                    <span class="text-success fw-bold"><i class="bi bi-shield-fill-check me-1"></i>Chữ ký HMAC-SHA256 Hợp Lệ</span>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-center mt-3">
                <a href="index.php?page=my-orders" class="btn px-4 fw-bold text-white shadow-sm" style="background-color: #a50064;">
                    <i class="bi bi-box-seam me-1"></i> Xem Đơn Mua
                </a>
                <a href="index.php" class="btn btn-outline-secondary px-3">
                    Tiếp Tục Mua Sắm
                </a>
            </div>

        <?php else: ?>
            <!-- GIAO DỊCH THẤT BẠI HOẶC BỊ HỦY / GIẢ MẠO CHỮ KÝ -->
            <div class="mb-3">
                <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 72px; height: 72px;">
                    <i class="bi bi-x-lg fs-1"></i>
                </div>
            </div>
            <h3 class="fw-bold text-danger mb-1">Giao Dịch MoMo Không Thành Công</h3>
            <p class="text-muted small mb-3">
                <?php if (!$result['valid']): ?>
                    <strong>Thông báo bảo mật:</strong> Dữ liệu phản hồi không hợp lệ hoặc chữ ký HMAC-SHA256 không khớp! Giao dịch bị từ chối.
                <?php else: ?>
                    Giao dịch đã bị hủy hoặc người dùng từ chối thanh toán trên ứng dụng MoMo (Mã phản hồi MoMo: <?= htmlspecialchars($result['result_code']) ?> - <?= htmlspecialchars($result['message']) ?>).
                <?php endif; ?>
            </p>

            <div class="alert alert-light border small text-muted">
                Mã đơn hàng: <strong>#<?= htmlspecialchars($result['order_code']) ?></strong>. Đơn hàng vẫn được lưu ở trạng thái chờ thanh toán.
            </div>

            <div class="d-flex gap-2 justify-content-center mt-3">
                <a href="index.php?page=checkout" class="btn btn-danger px-4 fw-bold">
                    Thử Lại Thanh Toán
                </a>
                <a href="index.php" class="btn btn-outline-secondary px-3">
                    Về Trang Chủ
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
