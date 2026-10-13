<?php
$pageTitle = "Kết Quả Thanh Toán VNPAY - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 650px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white text-center p-4 p-md-5">
        <?php if ($result['valid'] && $result['success']): ?>
            <!-- GIAO DỊCH THÀNH CÔNG -->
            <div class="mb-3">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 72px; height: 72px;">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
            </div>
            <h3 class="fw-bold text-success mb-1">Thanh Toán VNPAY Thành Công!</h3>
            <p class="text-muted small">Giao dịch đã được Cổng thanh toán VNPAY xác nhận thành công.</p>

            <div class="bg-light rounded-3 p-3 text-start small border my-3">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mã Đơn Hàng Sàn:</span>
                    <strong class="text-dark">#<?= htmlspecialchars($result['order_code']) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mã Giao Dịch VNPAY:</span>
                    <span class="font-monospace fw-bold text-primary"><?= htmlspecialchars($result['transaction_no']) ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Ngân Hàng Thanh Toán:</span>
                    <span class="badge bg-primary"><?= htmlspecialchars($result['bank_code']) ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Số Tiền Thanh Toán:</span>
                    <strong class="text-danger fs-6"><?= number_format($result['amount'], 0, ',', '.') ?> đ</strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Trạng Thái Xác Thực:</span>
                    <span class="text-success fw-bold"><i class="bi bi-shield-fill-check me-1"></i>Đã Xác Thực Hợp Lệ</span>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-center mt-3">
                <a href="index.php?page=my-orders" class="btn btn-primary px-4 fw-bold">
                    <i class="bi bi-box-seam me-1"></i> Xem Đơn Mua
                </a>
                <a href="index.php" class="btn btn-outline-secondary px-3">
                    Tiếp Tục Mua Sắm
                </a>
            </div>

        <?php else: ?>
            <!-- GIAO DỊCH THẤT BẠI HOẶC BỊ GIẢ MẠO CHỮ KÝ -->
            <div class="mb-3">
                <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 72px; height: 72px;">
                    <i class="bi bi-x-lg fs-1"></i>
                </div>
            </div>
            <h3 class="fw-bold text-danger mb-1">Giao Dịch VNPAY Không Thành Công</h3>
            <p class="text-muted small mb-3">
                <?php if (!$result['valid']): ?>
                    <strong>Thông báo lỗi:</strong> Dữ liệu phản hồi không hợp lệ hoặc chữ ký xác thực không khớp.
                <?php else: ?>
                    Giao dịch đã bị hủy hoặc ngân hàng từ chối thanh toán (Mã lỗi VNPAY: <?= htmlspecialchars($result['response_code']) ?>).
                <?php endif; ?>
            </p>

            <div class="alert alert-light border small text-muted">
                Mã đơn hàng: <strong>#<?= htmlspecialchars($result['order_code']) ?></strong>. Đơn hàng vẫn được lưu ở trạng thái chờ thanh toán.
            </div>

            <div class="d-flex flex-column gap-2 mt-3" style="max-width: 420px; margin: 0 auto;">
                <?php if (!empty($result['order_code'])): ?>
                    <a href="index.php?page=vnpay-return&order_code=<?= urlencode($result['order_code']) ?>&amount=<?= (int)$result['amount'] ?>&sandbox_demo=1" class="btn btn-outline-success fw-bold py-2 shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> Xác Nhận Đã Thanh Toán (Giả Lập Demo Sandbox)
                    </a>
                    <a href="index.php?page=order-tracking&order_code=<?= urlencode($result['order_code']) ?>" class="btn btn-primary fw-bold py-2 shadow-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Tiếp Tục Thanh Toán Đơn Này
                    </a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-secondary">
                    Về Trang Chủ
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
