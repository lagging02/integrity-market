<?php
$pageTitle = "Kiểm Soát Tài Khoản & Giao Dịch - IntegrityMarket Admin";
require __DIR__ . '/../layouts/header.php';

$adminCount = 0;
$sellerCount = 0;
$buyerCount = 0;
foreach ($users as $u) {
    if ($u['role'] === 'ADMIN') $adminCount++;
    elseif ($u['role'] === 'SELLER') $sellerCount++;
    else $buyerCount++;
}
?>

<div class="container my-4">
    <!-- ADMIN HEADER -->
    <div class="card border-0 rounded-4 text-white shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary-subtle text-primary border border-primary-subtle mb-2 small fw-bold">
                    <i class="bi bi-shield-check"></i> QUẢN TRỊ VIÊN HỆ THỐNG
                </div>
                <h3 class="fw-bold mb-1">Kiểm Soát Tài Khoản & Giám Sát Giao Dịch</h3>
                <p class="text-secondary small mb-0">Quản lý danh sách người dùng phân quyền (RBAC), đối soát đơn hàng và trạng thái cổng thanh toán.</p>
            </div>
            <div>
                <a href="index.php?page=admin" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Quay lại Admin Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- TỔNG QUAN CHỈ SỐ TÀI KHOẢN -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Tổng Người Dùng</span>
                <h4 class="fw-bold text-primary my-1"><?= count($users) ?> tài khoản</h4>
                <span class="small text-muted">Toàn bộ hệ thống</span>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Khách Hàng (Buyer)</span>
                <h4 class="fw-bold text-dark my-1"><?= $buyerCount ?> tài khoản</h4>
                <span class="small text-muted">Người mua trực tuyến</span>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Người Bán (Seller)</span>
                <h4 class="fw-bold text-success my-1"><?= $sellerCount ?> tài khoản</h4>
                <span class="small text-muted">Kênh bán hàng đối tác</span>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <span class="text-muted small fw-bold text-uppercase">Quản Trị (Admin)</span>
                <h4 class="fw-bold text-danger my-1"><?= $adminCount ?> tài khoản</h4>
                <span class="small text-muted">Quyền kiểm soát sàn</span>
            </div>
        </div>
    </div>

    <!-- BẢNG 1: KIỂM SOÁT THÔNG TIN TÀI KHOẢN & PHÂN QUYỀN (RBAC) -->
    <div class="card border rounded-3 bg-white shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>1. Danh Sách Tài Khoản & Phân Quyền Hệ Thống</h5>
                <p class="text-muted small mb-0">Kiểm soát thông tin định danh, vai trò phân quyền (Role-Based Access Control) và trạng thái tài khoản.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%;">Tên Đăng Nhập</th>
                        <th style="width: 18%;">Họ Và Tên</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 12%;" class="text-center">Vai Trò (Role)</th>
                        <th style="width: 15%;">Số Điện Thoại</th>
                        <th style="width: 20%;">Địa Chỉ</th>
                        <th style="width: 10%;" class="text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <strong class="text-primary font-monospace"><?= htmlspecialchars($u['username']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td class="text-center">
                                <?php if ($u['role'] === 'ADMIN'): ?>
                                    <span class="badge bg-danger">ADMIN</span>
                                <?php elseif ($u['role'] === 'SELLER'): ?>
                                    <span class="badge bg-success">SELLER</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">BUYER</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span><?= htmlspecialchars($u['phone_display']) ?></span>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 220px;" title="<?= htmlspecialchars($u['address_display']) ?>">
                                    <?= htmlspecialchars($u['address_display']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Hoạt động
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- BẢNG 2: NHẬT KÝ ĐƠN HÀNG & ĐỐI SOÁT CỔNG THANH TOÁN -->
    <div class="card border rounded-3 bg-white shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-credit-card-2-front text-primary me-2"></i>2. Giám Sát Đơn Hàng & Đối Soát Giao Dịch Cổng Thanh Toán</h5>
                <p class="text-muted small mb-0">Theo dõi thông tin thanh toán đa cổng (VNPAY, MoMo, ZaloPay, PayPal, GHN COD) phục vụ đối soát tài chính.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="https://sandbox.vnpayment.vn/merchantv2/" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>VNPAY Merchant Admin
                </a>
                <a href="https://developer.paypal.com/dashboard/" target="_blank" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>PayPal Developer
                </a>
                <a href="https://khachhang.ghn.vn/" target="_blank" class="btn btn-outline-warning text-dark btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>GHN Portal
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Đơn Hàng</th>
                        <th>Khách Hàng</th>
                        <th>Phương Thức</th>
                        <th>Tổng Tiền</th>
                        <th class="text-center">Trạng Thái</th>
                        <th>Cơ Chế Xác Thực & Toàn Vẹn</th>
                        <th>Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">Chưa có giao dịch nào</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td><strong class="text-primary font-monospace"><?= htmlspecialchars($ord['order_code']) ?></strong></td>
                                <td><?= htmlspecialchars($ord['customer_name']) ?></td>
                                <td>
                                    <?php if ($ord['payment_method'] === 'VNPAY'): ?>
                                        <span class="badge bg-danger">VNPAY Sandbox</span>
                                    <?php elseif ($ord['payment_method'] === 'MOMO'): ?>
                                        <span class="badge" style="background-color: #a50064;">MoMo Sandbox</span>
                                    <?php elseif ($ord['payment_method'] === 'ZALOPAY'): ?>
                                        <span class="badge" style="background-color: #008fe5;">ZaloPay Sandbox</span>
                                    <?php elseif ($ord['payment_method'] === 'PAYPAL'): ?>
                                        <span class="badge" style="background-color: #003087;">PayPal USD</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">COD Tiền Mặt</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong class="text-danger"><?= number_format($ord['total_amount'], 0, ',', '.') ?> đ</strong></td>
                                <td class="text-center">
                                    <?php if ($ord['payment_status'] === 'PAID'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i>ĐÃ THANH TOÁN
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold px-2 py-1 text-dark">
                                            <i class="bi bi-clock-history me-1"></i>CHỜ THANH TOÁN
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ord['payment_method'] === 'VNPAY'): ?>
                                        <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>Chữ ký HMAC-SHA512 (Terminal <code>I25YOYNN</code>)</small>
                                    <?php elseif ($ord['payment_method'] === 'MOMO'): ?>
                                        <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>Chữ ký HMAC-SHA256 (Khóa bí mật Partner)</small>
                                    <?php elseif ($ord['payment_method'] === 'ZALOPAY'): ?>
                                        <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>Chuỗi băm HMAC-SHA256 MAC (App ID <code>2553</code>)</small>
                                    <?php elseif ($ord['payment_method'] === 'PAYPAL'): ?>
                                        <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>Xác thực OAuth2 Bearer & Capture</small>
                                    <?php else: ?>
                                        <small class="text-muted"><i class="bi bi-truck text-primary me-1"></i>Vận đơn 3PL GHN Express TP.HCM</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($ord['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
