<?php
$pageTitle = "Quản Trị Sàn TMĐT - IntegrityMarket Control Panel";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-4">
    <!-- ADMIN HEADER -->
    <div class="card border-0 rounded-4 bg-dark text-white shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #0f172a, #1e293b);">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-danger-subtle text-danger border border-danger-subtle mb-2 small fw-bold">
                    <i class="bi bi-shield-lock-fill"></i> QUẢN TRỊ VIÊN HỆ THỐNG (RBAC: ADMIN)
                </div>
                <h3 class="fw-bold mb-1">Bảng Điều Khiển Sàn Giao Dịch Thương Mại Điện Tử</h3>
                <p class="text-secondary small mb-0">Giám sát chỉ số thị trường B2C, quản lý người bán đa kênh và theo dõi đơn hàng toàn sàn.</p>
            </div>

            <div class="d-flex gap-2">
                <a href="index.php?page=admin-security" class="btn btn-outline-light shadow-sm">
                    <i class="bi bi-people-fill me-1"></i>Kiểm Soát Tài Khoản & Giao Dịch
                </a>
                <a href="index.php?page=seller" class="btn btn-primary shadow-sm">
                    <i class="bi bi-shop me-1"></i>Kênh Người Bán (Seller)
                </a>
            </div>
        </div>
    </div>

    <!-- CHỈ SỐ TOÀN SÀN (PLATFORM KPIS) -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Tổng Doanh Số Sàn (GMV)</span>
                <h3 class="fw-extrabold text-primary my-2"><?= number_format($totalRevenue, 0, ',', '.') ?> đ</h3>
                <span class="small text-success"><i class="bi bi-check-circle"></i> Đã quyết toán qua cổng</span>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Tổng Đơn Hàng Phát Sinh</span>
                <h3 class="fw-extrabold text-dark my-2"><?= $totalOrders ?> đơn</h3>
                <span class="small text-muted">Bao gồm Tech & DIY</span>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Gian Hàng Người Bán (Shops)</span>
                <h3 class="fw-extrabold text-success my-2"><?= $totalShops ?> Shop</h3>
                <span class="small text-success"><i class="bi bi-patch-check"></i> Đã phê duyệt kinh doanh</span>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Tài Khoản Thành Viên</span>
                <h3 class="fw-extrabold text-secondary my-2"><?= $totalUsers ?> Users</h3>
                <span class="small text-muted">Bảo mật mật khẩu Bcrypt</span>
            </div>
        </div>
    </div>

    <!-- BẢNG GIAN HÀNG NGƯỜI BÁN & ĐƠN HÀNG TOÀN SÀN -->
    <div class="row g-4 mb-4">
        <!-- CỘT TRÁI: DANH SÁCH GIAN HÀNG -->
        <div class="col-lg-5">
            <div class="card border rounded-3 bg-white shadow-sm p-3 h-100">
                <h5 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-shop me-2 text-primary"></i>Danh Sách Gian Hàng Trên Sàn</h5>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($shops as $s): ?>
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($s['shop_name']) ?></h6>
                                    <div class="text-muted small">Slug: <code><?= htmlspecialchars($s['shop_slug']) ?></code></div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">HOẠT ĐỘNG</span>
                            </div>
                            <p class="small text-secondary mb-2"><?= htmlspecialchars($s['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center small text-muted">
                                <span>Đánh giá: <strong><?= number_format($s['rating'], 1) ?> ⭐</strong></span>
                                <span class="badge bg-primary-subtle text-primary">3PL GHN Active</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: ĐƠN HÀNG TOÀN SÀN -->
        <div class="col-lg-7">
            <div class="card border rounded-3 bg-white shadow-sm p-3 h-100">
                <h5 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-receipt me-2 text-primary"></i>Đơn Hàng Gần Đây Toàn Sàn</h5>
                <div class="table-responsive">
                    <table class="table align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã Đơn</th>
                                <th>Gian Hàng</th>
                                <th>Thanh Toán</th>
                                <th>Logistics 3PL</th>
                                <th class="text-end">Giá Trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ro): ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary"><?= htmlspecialchars($ro['order_code']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($ro['shop_name'] ?? 'Gian hàng') ?></td>
                                    <td>
                                        <?php if ($ro['payment_status'] === 'PAID'): ?>
                                            <span class="badge bg-success">Đã TT</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Chờ TT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($ro['fulfillment_status']) ?></span>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        <?= number_format($ro['total_amount'], 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
