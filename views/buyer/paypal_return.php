<?php
$pageTitle = "Kết Quả Thanh Toán PayPal - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 650px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white text-center p-4 p-md-5">
        <?php if ($result['success']): ?>
            <!-- GIAO DỊCH THÀNH CÔNG -->
            <div class="mb-3">
                <div class="text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 72px; height: 72px; background-color: #003087;">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #003087;">Thanh Toán PayPal Thành Công!</h3>
            <p class="text-muted small">Giao dịch đã được Cổng thanh toán Quốc tế PayPal Sandbox xác nhận thành công.</p>

            <div class="bg-light rounded-3 p-3 text-start small border my-3">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mã Đơn Hàng Sàn:</span>
                    <strong class="text-dark">#<?= htmlspecialchars($result['order_code']) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mã Giao Dịch PayPal (Capture ID):</span>
                    <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($result['transaction_id']) ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Kênh Thanh Toán:</span>
                    <span class="badge" style="background-color: #003087;">PayPal Checkout (USD / Visa)</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Số Tiền Thanh Toán:</span>
                    <strong class="text-danger fs-6"><?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?> đ (~$<?= number_format(($order['total_amount'] ?? 0) / 25000, 2) ?> USD)</strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Trạng Thái Xác Thực:</span>
                    <span class="text-success fw-bold"><i class="bi bi-shield-fill-check me-1"></i>OAuth2 / Capture Completed</span>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-center mt-3">
                <a href="index.php?page=my-orders" class="btn px-4 fw-bold text-white shadow-sm" style="background-color: #003087;">
                    <i class="bi bi-box-seam me-1"></i> Xem Đơn Mua
                </a>
                <a href="index.php" class="btn btn-outline-secondary px-3">
                    Tiếp Tục Mua Sắm
                </a>
            </div>

        <?php else: ?>
            <!-- GIAO DỊCH THẤT BẠI -->
            <div class="mb-3">
                <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 72px; height: 72px;">
                    <i class="bi bi-x-lg fs-1"></i>
                </div>
            </div>
            <h3 class="fw-bold text-danger mb-1">Giao Dịch PayPal Không Thành Công</h3>
            <p class="text-muted small mb-3">
                Giao dịch bị từ chối hoặc người dùng đã hủy thanh toán trên cổng PayPal.
            </p>

            <div class="alert alert-light border small text-muted">
                Mã đơn hàng: <strong>#<?= htmlspecialchars($result['order_code']) ?></strong>. Đơn hàng vẫn được lưu ở trạng thái chờ thanh toán.
            </div>

            <div class="d-flex flex-column gap-2 mt-3" style="max-width: 420px; margin: 0 auto;">
                <?php if (!empty($result['order_code'])): ?>
                    <a href="index.php?page=paypal-return&order_code=<?= urlencode($result['order_code']) ?>&amount=<?= (int)($order['total_amount'] ?? 0) ?>&sandbox_demo=1" class="btn btn-outline-success fw-bold py-2 shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> Xác Nhận Đã Thanh Toán (Giả Lập Demo Sandbox)
                    </a>
                    <a href="index.php?page=order-tracking&order_code=<?= urlencode($result['order_code']) ?>" class="btn fw-bold text-white py-2 shadow-sm" style="background-color: #003087;">
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
