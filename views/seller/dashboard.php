<?php
$pageTitle = "Kênh Người Bán - " . htmlspecialchars($shop['shop_name']);
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-4">
    <!-- SELLER TOP BAR & SHOP HEADER -->
    <div class="card border-0 rounded-4 bg-white shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="<?= htmlspecialchars($shop['avatar'] ?? 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=200') ?>" alt="<?= htmlspecialchars($shop['shop_name']) ?>" class="rounded-circle border" style="width: 70px; height: 70px; object-fit: cover;">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0"><?= htmlspecialchars($shop['shop_name']) ?></h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Gian Hàng Hoạt Động</span>
                    </div>
                    <p class="text-muted small mb-1"><?= htmlspecialchars($shop['description']) ?></p>
                    <div class="d-flex gap-3 small text-muted">
                        <span><i class="bi bi-star-fill text-warning me-1"></i>Đánh giá: <strong><?= number_format($shop['rating'], 1) ?>/5.0</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-shield-check text-primary me-1"></i>Đã kích hoạt 3PL GHN Express</span>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="index.php?page=seller-add-product" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i>Đăng Sản Phẩm Mới
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Xem Sàn Mua Sắm
                </a>
            </div>
        </div>

        <!-- SELLER SUB-NAV TABS -->
        <ul class="nav nav-tabs mt-4 border-bottom">
            <li class="nav-item">
                <a class="nav-link active fw-bold text-primary" href="index.php?page=seller">
                    <i class="bi bi-speedometer2 me-1"></i>Tổng Quan Doanh Thu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="index.php?page=seller-products">
                    <i class="bi bi-box-seam me-1"></i>Quản Lý Sản Phẩm (<?= count($products) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="index.php?page=seller-orders">
                    <i class="bi bi-receipt-cutoff me-1"></i>Xử Lý Đơn Hàng & In Tem 3PL (<?= count($orders) ?>)
                </a>
            </li>
        </ul>
    </div>

    <!-- METRIC CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Doanh Thu Đã Thu</span>
                <h3 class="fw-extrabold text-primary my-2"><?= number_format($totalRevenue, 0, ',', '.') ?> đ</h3>
                <span class="small text-success"><i class="bi bi-arrow-up-right"></i> Đã qua cổng an toàn</span>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Chờ Chuẩn Bị & Đóng Gói</span>
                <h3 class="fw-extrabold <?= $awaitingCount > 0 ? 'text-danger' : 'text-dark' ?> my-2"><?= $awaitingCount ?> đơn</h3>
                <span class="small <?= $awaitingCount > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                    <?= $awaitingCount > 0 ? 'Cần tạo vận đơn GHN ngay' : 'Đã xử lý toàn bộ' ?>
                </span>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Tổng Sản Phẩm Đang Bán</span>
                <h3 class="fw-extrabold text-dark my-2"><?= count($products) ?> SKU</h3>
                <span class="small text-muted">Có hỗ trợ đồ DIY & Tech</span>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Hiệu Suất Giao 3PL</span>
                <h3 class="fw-extrabold text-success my-2">99.4%</h3>
                <span class="small text-success"><i class="bi bi-patch-check"></i> Đạt chuẩn SLA GHN</span>
            </div>
        </div>
    </div>

    <!-- CẢNH BÁO ĐƠN CHỜ ĐÓNG GÓI -->
    <?php if ($awaitingCount > 0): ?>
        <div class="alert alert-warning border-warning-subtle d-flex align-items-center justify-content-between p-3 rounded-3 mb-4 shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-bell-fill fs-4 text-warning-emphasis"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Có <?= $awaitingCount ?> đơn hàng mới đang chờ bạn xác nhận và đóng gói!</h6>
                    <small class="text-secondary">Hãy bấm Tạo Vận Đơn để lấy mã Tracking GHN và in tem dán gói hàng.</small>
                </div>
            </div>
            <a href="index.php?page=seller-orders" class="btn btn-warning btn-sm fw-bold text-dark px-3">
                Xử Lý Ngay <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    <?php endif; ?>

    <!-- DANH SÁCH ĐƠN HÀNG GẦN ĐÂY -->
    <div class="card border rounded-3 bg-white shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Đơn Hàng Gần Đây Của Gian Hàng</h5>
            <a href="index.php?page=seller-orders" class="small fw-bold text-decoration-none">Xem toàn bộ</a>
        </div>

        <?php if (empty($orders)): ?>
            <p class="text-muted small my-3 text-center py-4">Chưa có đơn hàng nào phát sinh cho gian hàng này.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Đơn</th>
                            <th>Khách Hàng (Decrypted)</th>
                            <th>Thanh Toán</th>
                            <th>Trạng Thái 3PL</th>
                            <th>Tổng Tiền</th>
                            <th class="text-end">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($orders, 0, 5) as $o): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary"><?= htmlspecialchars($o['order_code']) ?></strong>
                                    <div class="text-muted" style="font-size: 10px;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($o['customer_name']) ?></div>
                                    <div class="text-muted"><?= htmlspecialchars($o['phone']) ?></div>
                                </td>
                                <td>
                                    <?php if ($o['payment_status'] === 'PAID'): ?>
                                        <span class="badge bg-success">Đã thanh toán (<?= htmlspecialchars($o['payment_method']) ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Chưa thanh toán (<?= htmlspecialchars($o['payment_method']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        <?= htmlspecialchars($o['fulfillment_status']) ?>
                                    </span>
                                    <?php if (!empty($o['tracking_code'])): ?>
                                        <div class="text-muted fw-bold" style="font-size: 10px;">#<?= htmlspecialchars($o['tracking_code']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-danger">
                                    <?= number_format($o['total_amount'], 0, ',', '.') ?> đ
                                </td>
                                <td class="text-end">
                                    <a href="index.php?page=shipping-label&id=<?= $o['id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm p-1 px-2" title="In tem dán thùng">
                                        <i class="bi bi-printer"></i> In Tem
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
