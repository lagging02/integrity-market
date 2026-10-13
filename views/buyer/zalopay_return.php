<?php
$pageTitle = "Kết Quả Thanh Toán ZaloPay - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 650px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white text-center p-4 p-md-5">
        <?php if ($result['valid'] && $result['success']): ?>
            <!-- GIAO DỊCH THÀNH CÔNG -->
            <div class="mb-3">
                <div class="text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 72px; height: 72px; background-color: #008fe5;">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #008fe5;">Thanh Toán ZaloPay Thành Công!</h3>
            <p class="text-muted small">Giao dịch đã được Cổng thanh toán ZaloPay Sandbox xác nhận thành công.</p>

            <div class="bg-light rounded-3 p-3 text-start small border my-3">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mã Đơn Hàng Sàn:</span>
                    <strong class="text-dark">#<?= htmlspecialchars($result['order_code']) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Mã Giao Dịch ZaloPay:</span>
                    <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($result['transaction_no'] ?: 'ZLP_SANDBOX') ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Phương Thức Thanh Toán:</span>
                    <span class="badge" style="background-color: #008fe5;">Ví ZaloPay / ATM Sandbox</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Số Tiền Thanh Toán:</span>
                    <strong class="text-danger fs-6"><?= number_format($order['total_amount'] ?? $result['amount'], 0, ',', '.') ?> đ</strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Trạng Thái Xác Thực:</span>
                    <span class="text-success fw-bold"><i class="bi bi-shield-fill-check me-1"></i>Xác Thực ZaloPay Hợp Lệ</span>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-center mt-3">
                <a href="index.php?page=my-orders" class="btn px-4 fw-bold text-white shadow-sm" style="background-color: #008fe5;">
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
            <h3 class="fw-bold text-danger mb-1">Giao Dịch ZaloPay Không Thành Công</h3>
            <p class="text-muted small mb-3">
                <?= htmlspecialchars($result['message'] ?? 'Giao dịch đã bị hủy hoặc không thể hoàn tất trên Cổng ZaloPay Sandbox.') ?>
            </p>

            <div class="alert alert-light border small text-muted text-start p-3 mb-3">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span>Mã Đơn Hàng:</span>
                    <strong class="text-dark">#<?= htmlspecialchars($result['order_code']) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span>Trạng Thái Đơn:</span>
                    <span class="badge bg-warning text-dark">Chờ Thanh Toán (PENDING)</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span>Lý Do:</span>
                    <span class="text-danger fw-bold">Đã hủy thao tác hoặc chưa hoàn tất</span>
                </div>
            </div>

            <div class="d-flex flex-column gap-2 mt-3" style="max-width: 420px; margin: 0 auto;">
                <?php if (!empty($result['order_code'])): ?>
                    <a href="index.php?page=zalopay-return&order_code=<?= urlencode($result['order_code']) ?>&amount=<?= (int)($result['amount'] ?? 0) ?>&sandbox_demo=1" class="btn btn-outline-success fw-bold py-2 shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> Xác Nhận Đã Thanh Toán (Giả Lập Demo Sandbox)
                    </a>
                <?php endif; ?>
                <?php if (!empty($order['id'])): ?>
                    <a href="index.php?page=zalopay-gateway&id=<?= urlencode($order['id']) ?>" class="btn btn-primary fw-bold py-2 shadow-sm" style="background-color: #008fe5;">
                        <i class="bi bi-arrow-repeat me-1"></i> Quét Lại Mã QR ZaloPay
                    </a>
                    <a href="index.php?page=order-tracking&id=<?= urlencode($order['id']) ?>" class="btn btn-outline-dark fw-bold py-2">
                        <i class="bi bi-truck me-1"></i> Theo Dõi Trạng Thái Đơn Hàng
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
