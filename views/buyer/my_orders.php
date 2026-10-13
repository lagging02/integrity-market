<?php
$pageTitle = "Đơn Mua Của Tôi - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-4" style="max-width: 1000px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam text-primary me-2"></i>Đơn Mua Của Tôi</h4>
            <p class="text-muted small mb-0">Quản lý lịch sử mua sắm và theo dõi vận trình đơn hàng 3PL.</p>
        </div>
        <a href="index.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Mua Thêm Sản Phẩm</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card p-5 text-center border-0 shadow-sm bg-white rounded-3 my-4">
            <i class="bi bi-receipt text-muted" style="font-size: 4rem;"></i>
            <h5 class="fw-bold mt-3">Bạn chưa có đơn mua nào</h5>
            <p class="text-muted">Hãy lựa chọn những sản phẩm công nghệ hoặc sản phẩm thủ công DIY độc đáo ngay hôm nay.</p>
            <div>
                <a href="index.php" class="btn btn-primary px-4">Dạo quanh mua sắm</a>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($orders as $order): ?>
                <div class="card border rounded-3 bg-white shadow-sm overflow-hidden">
                    <div class="card-header bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center py-2 px-3 gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shop text-primary"></i>
                            <strong><?= htmlspecialchars($order['shop_name']) ?></strong>
                            <span class="text-muted small">| Đặt lúc <?= date('H:i d/m/Y', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">Mã đơn: <strong><?= htmlspecialchars($order['order_code']) ?></strong></span>
                            <?php if ($order['payment_status'] === 'PAID'): ?>
                                <span class="badge bg-success">ĐÃ THANH TOÁN</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">CHỜ THANH TOÁN</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="row align-items-center g-3">
                            <div class="col-md-7">
                                <div class="small">
                                    <div class="text-muted">Địa chỉ nhận hàng (Mã hóa AES-256):</div>
                                    <div class="text-dark fw-medium"><?= htmlspecialchars($order['customer_name']) ?> - <?= htmlspecialchars($order['phone']) ?></div>
                                    <div class="text-muted text-truncate"><?= htmlspecialchars($order['address']) ?></div>
                                </div>
                            </div>

                            <div class="col-md-5 text-md-end">
                                <div class="mb-2">
                                    <span class="text-muted small">Tổng thanh toán:</span>
                                    <span class="fs-5 fw-extrabold text-danger ms-2"><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</span>
                                </div>
                                <div class="d-flex justify-content-md-end gap-2">
                                    <a href="index.php?page=order-tracking&id=<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm fw-semibold">
                                        <i class="bi bi-truck me-1"></i>Theo Dõi Vận Đơn
                                    </a>
                                    <a href="index.php?page=order-success&id=<?= $order['id'] ?>" class="btn btn-light btn-sm border">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top py-2 px-3 small d-flex justify-content-between align-items-center text-muted">
                        <div>
                            <i class="bi bi-upc-scan me-1"></i>Mã vận đơn GHN: 
                            <strong class="text-primary"><?= htmlspecialchars($order['tracking_code'] ?? 'Đang chuẩn bị hàng') ?></strong>
                        </div>
                        <div>
                            Trạng thái: <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($order['fulfillment_status']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
