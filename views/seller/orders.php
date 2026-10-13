<?php
$pageTitle = "Quản Lý Đơn Hàng & Vận Đơn 3PL - " . htmlspecialchars($shop['shop_name']);
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-4">
    <!-- SELLER TOP BAR & SHOP HEADER -->
    <div class="card border-0 rounded-4 bg-white shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Quản Lý Đơn Hàng & Điều Phối 3PL</h4>
                <p class="text-muted small mb-0">Xử lý đóng gói, sinh mã Tracking GHN Express và in phiếu gửi hàng chuẩn bưu chính.</p>
            </div>
        </div>

        <!-- SELLER SUB-NAV TABS -->
        <ul class="nav nav-tabs mt-4 border-bottom">
            <li class="nav-item">
                <a class="nav-link text-dark" href="index.php?page=seller">
                    <i class="bi bi-speedometer2 me-1"></i>Tổng Quan Doanh Thu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="index.php?page=seller-products">
                    <i class="bi bi-box-seam me-1"></i>Quản Lý Sản Phẩm
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active fw-bold text-primary" href="index.php?page=seller-orders">
                    <i class="bi bi-receipt-cutoff me-1"></i>Xử Lý Đơn Hàng & In Tem 3PL (<?= count($orders) ?>)
                </a>
            </li>
        </ul>
    </div>

    <!-- THÔNG BÁO HOÀN THÀNH THAO TÁC -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'shipping_created'): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>Đã kết nối API GHN Express và tạo mã vận đơn thành công! Bạn có thể bấm <strong>"In Tem Vận Chuyển"</strong> để dán lên gói hàng.</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'dispatched'): ?>
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-truck fs-5 me-2"></i>
            <div>Đã cập nhật trạng thái: Kiện hàng đã được bàn giao cho bưu tá GHN và đang luân chuyển.</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- DANH SÁCH ĐƠN HÀNG CẦN XỬ LÝ -->
    <?php if (empty($orders)): ?>
        <div class="card p-5 text-center border-0 shadow-sm bg-white rounded-3 my-4">
            <i class="bi bi-box2 text-muted" style="font-size: 4rem;"></i>
            <h5 class="fw-bold mt-3">Hiện chưa có đơn hàng nào</h5>
            <p class="text-muted">Khi có khách đặt mua sản phẩm, đơn hàng sẽ hiển thị ở đây để bạn xử lý đóng gói và tạo vận đơn.</p>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-4">
            <?php foreach ($orders as $order): ?>
                <div class="card border rounded-3 bg-white shadow-sm overflow-hidden">
                    <!-- HEADER ĐƠN HÀNG -->
                    <div class="card-header bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center py-2 px-3 gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-dark">Đơn Hàng #<?= htmlspecialchars($order['order_code']) ?></span>
                            <span class="text-muted small">Thời gian: <?= date('H:i d/m/Y', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($order['payment_status'] === 'PAID'): ?>
                                <span class="badge bg-success">ĐÃ THANH TOÁN (<?= htmlspecialchars($order['payment_method']) ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">CHỜ THANH TOÁN COD</span>
                            <?php endif; ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <?= htmlspecialchars($order['fulfillment_status']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- THÂN ĐƠN HÀNG -->
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <!-- CỘT 1: THÔNG TIN KHÁCH HÀNG & GIAO HÀNG (ĐÃ GIẢI MÃ PII) -->
                            <div class="col-lg-5 border-end-lg">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person text-primary me-1"></i>Thông Tin Giao Hàng (AES-256 Decrypted):</h6>
                                <div class="small text-secondary mb-2">
                                    <div><strong>Họ tên người nhận:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                                    <div><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></div>
                                    <div><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></div>
                                </div>
                                <div class="small">
                                    <span class="text-muted">Tổng tiền thu hộ (COD):</span>
                                    <strong class="text-danger fs-6 ms-1"><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</strong>
                                </div>
                            </div>

                            <!-- CỘT 2: SẢN PHẨM CẦN ĐÓNG GÓI -->
                            <div class="col-lg-4 border-end-lg">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-box-seam text-secondary me-1"></i>Sản Phẩm Cần Đóng Gói:</h6>
                                <ul class="list-unstyled small mb-0">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li class="py-1 border-bottom">
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                            <div class="text-muted d-flex justify-content-between">
                                                <span>SL: <strong><?= $item['quantity'] ?></strong> <?= !empty($item['variant_info']) ? '• ' . htmlspecialchars($item['variant_info']) : '' ?></span>
                                                <span><?= number_format($item['price'], 0, ',', '.') ?> đ</span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <!-- CỘT 3: KHỐI THAO TÁC ĐIỀU PHỐI LOGISTICS 3PL -->
                            <div class="col-lg-3 d-flex flex-column justify-content-center">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-truck text-warning me-1"></i>Điều Phối Vận Đơn:</h6>

                                <?php if ($order['fulfillment_status'] === 'AWAITING_FULFILLMENT'): ?>
                                    <!-- NÚT TẠO VẬN ĐƠN 3PL -->
                                    <form method="POST" class="d-flex flex-column gap-2">
                                        <?= \App\Services\CsrfService::field() ?>
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action_create_shipping" value="1">
                                        <select name="carrier" class="form-select form-select-sm">
                                            <option value="GHN Express">GHN Express (Tiêu chuẩn)</option>
                                            <option value="GHTK">Giao Hàng Tiết Kiệm (GHTK)</option>
                                        </select>
                                        <button type="submit" class="btn btn-warning btn-sm fw-bold text-dark shadow-sm">
                                            <i class="bi bi-upc-scan me-1"></i> Tạo Vận Đơn 3PL
                                        </button>
                                    </form>
                                <?php elseif ($order['fulfillment_status'] === 'READY_TO_PICK'): ?>
                                    <!-- NÚT IN TEM VẬN CHUYỂN & GIAO BƯU TÁ -->
                                    <div class="d-flex flex-column gap-2">
                                        <div class="small text-muted">
                                            Mã Tracking: <strong class="text-primary">#<?= htmlspecialchars($order['tracking_code']) ?></strong>
                                        </div>
                                        <a href="index.php?page=shipping-label&id=<?= $order['id'] ?>" target="_blank" class="btn btn-primary btn-sm fw-bold shadow-sm">
                                            <i class="bi bi-printer-fill me-1"></i> In Tem Dán Thùng (A6)
                                        </a>
                                        <form method="POST">
                                            <?= \App\Services\CsrfService::field() ?>
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <input type="hidden" name="action_dispatch_driver" value="1">
                                            <button type="submit" class="btn btn-outline-success btn-sm w-100 fw-semibold">
                                                <i class="bi bi-check2 me-1"></i> Bưu Tá Đã Lấy Hàng
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="small">
                                        <div class="text-muted">Mã vận đơn:</div>
                                        <div class="fw-bold text-primary fs-7 mb-2">#<?= htmlspecialchars($order['tracking_code']) ?></div>
                                        <a href="index.php?page=order-tracking&id=<?= $order['id'] ?>" class="btn btn-light btn-sm border w-100 mb-1">
                                            <i class="bi bi-geo-alt me-1"></i> Hành Trình Realtime
                                        </a>
                                        <a href="index.php?page=shipping-label&id=<?= $order['id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="bi bi-printer me-1"></i> In Lại Tem
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
