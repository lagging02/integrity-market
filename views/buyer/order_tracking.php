<?php
$pageTitle = !empty($order) ? ("Trạng Thái Đơn Hàng #" . htmlspecialchars($order['order_code']) . " - IntegrityMarket") : "Tra Cứu Trạng Thái Đơn Hàng - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';

// Xác định các chặng trong quy trình 4 bước thân thiện với người mua
// Step 1: Đã Đặt Hàng (Placed)
// Step 2: Đang Chuẩn Bị Hàng (Preparing / Packing)
// Step 3: Đang Giao Hàng (In Transit / Delivering)
// Step 4: Giao Thành Công (Delivered)

$step = 1;
$statusTitle = "Đã Đặt Hàng Thành Công";
$statusDesc = "Đơn hàng của bạn đã được ghi nhận trên hệ thống.";
$statusColor = "primary";
$statusIcon = "bi-bag-check-fill";

if (!empty($order)) {
    $fStatus = $order['fulfillment_status'];
    if ($fStatus === 'AWAITING_FULFILLMENT') {
        $step = 2;
        $statusTitle = "Đang Chuẩn Bị Hàng";
        $statusDesc = "Người bán đang kiểm tra, đóng gói sản phẩm và dán tem vận chuyển GHN Express.";
        $statusColor = "warning";
        $statusIcon = "bi-box-seam-fill";
    } elseif ($fStatus === 'READY_TO_PICK') {
        $step = 2;
        $statusTitle = "Đã Đóng Gói - Chờ Bưu Tá Lấy";
        $statusDesc = "Kiện hàng đã đóng gói hoàn tất tại kho tổng, chờ bưu tá GHN Express đến tiếp nhận.";
        $statusColor = "info";
        $statusIcon = "bi-upc-scan";
    } elseif ($fStatus === 'PICKING' || $fStatus === 'IN_TRANSIT') {
        $step = 3;
        $statusTitle = "Đang Luân Chuyển Hàng Hóa";
        $statusDesc = "Bưu tá GHN đã tiếp nhận kiện hàng và đang luân chuyển qua các trung tâm khai thác.";
        $statusColor = "primary";
        $statusIcon = "bi-truck";
    } elseif ($fStatus === 'OUT_FOR_DELIVERY') {
        $step = 3;
        $statusTitle = "Đang Giao Hàng Đến Bạn";
        $statusDesc = "Bưu tá GHN Express đang trên đường giao kiện hàng đến địa chỉ của bạn. Vui lòng chú ý điện thoại!";
        $statusColor = "primary";
        $statusIcon = "bi-bicycle";
    } elseif ($fStatus === 'DELIVERED') {
        $step = 4;
        $statusTitle = "Giao Hàng Thành Công";
        $statusDesc = "Kiện hàng đã được phát thành công đến tay người nhận. Cảm ơn bạn đã tin tưởng mua sắm!";
        $statusColor = "success";
        $statusIcon = "bi-check-circle-fill";
    }
}
?>

<div class="container my-5" style="max-width: 950px;">
    <!-- NẾU CÓ THÔNG BÁO TỪ CỔNG THANH TOÁN -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'paid'): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-3 border-success-subtle" role="alert">
            <i class="bi bi-patch-check-fill fs-2 me-3 text-success"></i>
            <div>
                <strong class="fs-6">Thanh toán trực tuyến thành công!</strong>
                <div class="small text-success-emphasis">Giao dịch đã được Cổng thanh toán xác thực an toàn. Đơn hàng đang được tự động chuyển sang bộ phận chuẩn bị hàng.</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-3 border-success-subtle" role="alert">
            <i class="bi bi-check-circle-fill fs-2 me-3 text-success"></i>
            <div>
                <strong class="fs-6">Đặt hàng thành công!</strong>
                <div class="small text-success-emphasis">Cảm ơn bạn đã mua hàng tại IntegrityMarket. Đơn hàng đã được gửi tới người bán để chuẩn bị đóng gói.</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4 shadow-sm rounded-3">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <!-- TRƯỜNG HỢP 1: CÓ THÔNG TIN ĐƠN HÀNG (HIỂN THỊ GIAO DIỆN TRẠNG THÁI LIVE) -->
    <?php if (!empty($order)): ?>
        <!-- CARD 1: HERO TRẠNG THÁI HIỆN TẠI & TIẾN ĐỘ -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <!-- BANNER TRẠNG THÁI LỚN -->
            <div class="p-4 p-md-5 text-white" style="background: <?= ($statusColor === 'warning') ? 'linear-gradient(135deg, #d97706 0%, #b45309 100%)' : (($statusColor === 'success') ? 'linear-gradient(135deg, #059669 0%, #047857 100%)' : 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)') ?>;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                            <i class="bi <?= $statusIcon ?> text-white" style="font-size: 2.2rem;"></i>
                        </div>
                        <div>
                            <span class="badge bg-white text-dark text-uppercase px-2 py-1 mb-1 fw-bold" style="font-size: 11px;">Trạng Thái Đơn Hàng</span>
                            <h2 class="fw-extrabold mb-1 text-white"><?= $statusTitle ?></h2>
                            <p class="mb-0 text-white text-opacity-90 small"><?= $statusDesc ?></p>
                        </div>
                    </div>
                    <div class="text-md-end bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-25">
                        <div class="small text-white text-opacity-75">Mã Đơn Hàng</div>
                        <strong class="font-monospace fs-5 text-white"><?= htmlspecialchars($order['order_code']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- THANH TIẾN ĐỘ 4 BƯỚC ĐỒ HỌA TRỰC QUAN -->
            <div class="p-4 bg-light border-bottom">
                <div class="position-relative m-3">
                    <!-- Đường kẻ tiến trình -->
                    <div class="progress" style="height: 6px;">
                        <?php
                        $progressPercent = ($step === 1) ? 15 : (($step === 2) ? 45 : (($step === 3) ? 75 : 100));
                        ?>
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progressPercent ?>%;"></div>
                    </div>

                    <!-- 4 Mốc Tròn -->
                    <div class="d-flex justify-content-between position-absolute top-0 start-0 w-100 translate-middle-y">
                        <!-- Mốc 1: Đã Đặt Hàng -->
                        <div class="text-center" style="width: 100px; margin-left: -30px;">
                            <div class="btn btn-sm rounded-circle <?= $step >= 1 ? 'btn-success text-white' : 'btn-light border' ?> shadow-sm fw-bold" style="width: 38px; height: 38px; line-height: 24px;">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="small fw-bold mt-2 <?= $step >= 1 ? 'text-dark' : 'text-muted' ?>" style="font-size: 12px;">Đã Đặt Hàng</div>
                            <div class="text-muted" style="font-size: 10px;"><?= date('H:i d/m', strtotime($order['created_at'])) ?></div>
                        </div>

                        <!-- Mốc 2: Đang Chuẩn Bị -->
                        <div class="text-center" style="width: 110px; margin-left: -35px;">
                            <div class="btn btn-sm rounded-circle <?= $step >= 2 ? 'btn-success text-white' : ($step === 1 ? 'btn-warning text-dark border shadow' : 'btn-light border') ?> fw-bold" style="width: 38px; height: 38px; line-height: 24px;">
                                <i class="bi <?= $step >= 2 ? 'bi-check-lg' : 'bi-hourglass-split' ?>"></i>
                            </div>
                            <div class="small fw-bold mt-2 <?= $step >= 2 ? 'text-dark' : 'text-muted' ?>" style="font-size: 12px;">Chuẩn Bị Hàng</div>
                            <div class="text-muted" style="font-size: 10px;"><?= $step >= 2 ? 'Đang đóng gói' : 'Chờ xử lý' ?></div>
                        </div>

                        <!-- Mốc 3: Đang Giao Hàng -->
                        <div class="text-center" style="width: 110px; margin-left: -35px;">
                            <div class="btn btn-sm rounded-circle <?= $step >= 3 ? 'btn-success text-white' : ($step === 2 ? 'btn-secondary text-white' : 'btn-light border') ?> fw-bold" style="width: 38px; height: 38px; line-height: 24px;">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div class="small fw-bold mt-2 <?= $step >= 3 ? 'text-dark' : 'text-muted' ?>" style="font-size: 12px;">Đang Giao Hàng</div>
                            <div class="text-muted" style="font-size: 10px;">GHN Express 3PL</div>
                        </div>

                        <!-- Mốc 4: Giao Thành Công -->
                        <div class="text-center" style="width: 100px; margin-right: -30px;">
                            <div class="btn btn-sm rounded-circle <?= $step >= 4 ? 'btn-success text-white' : 'btn-light border text-muted' ?> fw-bold" style="width: 38px; height: 38px; line-height: 24px;">
                                <i class="bi bi-house-door-fill"></i>
                            </div>
                            <div class="small fw-bold mt-2 <?= $step >= 4 ? 'text-success' : 'text-muted' ?>" style="font-size: 12px;">Đã Nhận Hàng</div>
                            <div class="text-muted" style="font-size: 10px;">Hoàn tất đơn</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- THÔNG TIN VẬN ĐƠN 3PL GHN -->
            <div class="p-4 bg-white">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="small text-muted mb-1"><i class="bi bi-truck text-primary me-1"></i>Đơn Vị Vận Chuyển:</div>
                        <strong class="text-dark fs-6"><?= htmlspecialchars($order['shipping_carrier']) ?></strong>
                        <div class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 10px;">Chính sách Đồng kiểm khi nhận</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1"><i class="bi bi-upc-scan text-primary me-1"></i>Mã Vận Đơn 3PL:</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="font-monospace fw-bold text-primary fs-6"><?= htmlspecialchars($order['tracking_code'] ?? ('GHN-' . strtoupper(substr(md5($order['id']), 0, 9)))) ?></span>
                            <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($order['tracking_code'] ?? ('GHN-' . strtoupper(substr(md5($order['id']), 0, 9)))) ?>'); alert('Đã sao chép mã vận đơn!');">Copy</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1"><i class="bi bi-credit-card text-primary me-1"></i>Thanh Toán:</div>
                        <div>
                            <?php if ($order['payment_status'] === 'PAID'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>ĐÃ THANH TOÁN (<?= htmlspecialchars($order['payment_method']) ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-cash me-1"></i>CHỜ THU TIỀN KHI GIAO (COD)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- CỘT TRÁI: NHẬT KÝ LUÂN CHUYỂN BƯU KIỆN 3PL -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i> Nhật Ký Luân Chuyển Bưu Kiện 3PL
                    </h5>

                    <?php if (empty($trackingLogs)): ?>
                        <div class="alert alert-light border small text-muted p-3 rounded-3">
                            <i class="bi bi-info-circle me-1"></i> Đơn hàng vừa được khởi tạo thành công. Nhật ký di chuyển chi tiết từ các trạm bưu cục GHN sẽ tự động cập nhật ngay khi kiện hàng bắt đầu luân chuyển.
                        </div>
                    <?php else: ?>
                        <div class="timeline ps-3 border-start border-2 border-primary-subtle my-2">
                            <?php foreach ($trackingLogs as $log): ?>
                                <div class="position-relative mb-4 ps-3">
                                    <div class="position-absolute top-0 start-0 translate-middle bg-primary rounded-circle border border-white" style="width: 14px; height: 14px; margin-left: -1px;"></div>
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($log['status_title']) ?></h6>
                                        <span class="text-muted small" style="font-size: 11px;"><?= date('H:i d/m/Y', strtotime($log['timestamp'])) ?></span>
                                    </div>
                                    <p class="small text-secondary mb-1"><?= htmlspecialchars($log['description']) ?></p>
                                    <div class="small text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars($log['location']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                        <span class="small text-muted"><i class="bi bi-building me-1"></i>Bưu cục gửi: Kho Tổng TP. Hồ Chí Minh</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="window.location.reload();">
                            <i class="bi bi-arrow-clockwise me-1"></i>Làm Mới Trạng Thái
                        </button>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: CHI TIẾT ĐƠN HÀNG & ĐỊA CHỈ NHẬN HÀNG -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-danger"></i> Thông Tin Nhận Hàng
                    </h5>

                    <div class="bg-light rounded-3 p-3 mb-3 small">
                        <div class="mb-2">
                            <span class="text-muted">Người nhận:</span>
                            <strong class="d-block text-dark"><?= htmlspecialchars($order['customer_name']) ?></strong>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Số điện thoại:</span>
                            <strong class="d-block text-dark font-monospace"><?= htmlspecialchars($order['phone_decrypted'] ?? $order['phone']) ?></strong>
                        </div>
                        <div>
                            <span class="text-muted">Địa chỉ nhận hàng:</span>
                            <div class="text-dark fw-semibold"><?= htmlspecialchars($order['address_decrypted'] ?? $order['address']) ?></div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-2">Sản Phẩm Trong Đơn:</h6>
                    <div class="d-flex flex-column gap-2 mb-3" style="max-height: 220px; overflow-y: auto;">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 small">
                                <div>
                                    <div class="fw-semibold text-truncate" style="max-width: 200px;"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <span class="text-muted">x<?= $item['quantity'] ?></span>
                                </div>
                                <span class="fw-bold text-dark"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> đ</span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-top pt-2 small">
                        <div class="d-flex justify-content-between mb-1 text-muted">
                            <span>Phí vận chuyển GHN:</span>
                            <span><?= number_format($order['shipping_fee'] ?? 28000, 0, ',', '.') ?> đ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                            <span class="fw-bold text-dark">Tổng cộng:</span>
                            <span class="fs-5 fw-extrabold text-danger"><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2 mt-auto">
                        <button onclick="window.print();" class="btn btn-outline-secondary w-100 fw-semibold btn-sm">
                            <i class="bi bi-printer me-1"></i> In Hóa Đơn & Vận Đơn
                        </button>
                        <a href="index.php?page=order-tracking" class="btn btn-light border w-100 fw-semibold btn-sm">
                            <i class="bi bi-search me-1"></i> Tra Cứu Đơn Hàng Khác
                        </a>
                        <a href="index.php" class="btn btn-primary w-100 fw-bold py-2">
                            <i class="bi bi-bag-fill me-1"></i> Tiếp Tục Mua Sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <!-- TRƯỜNG HỢP 2: CHƯA CÓ ĐƠN HÀNG (HIỂN THỊ KHỐI TRA CỨU ĐƠN HÀNG DÀNH CHO KHÁCH) -->
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 text-center my-4 mx-auto" style="max-width: 640px;">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3 mx-auto" style="width: 80px; height: 80px;">
                <i class="bi bi-truck fs-1"></i>
            </div>
            <h3 class="fw-extrabold text-dark mb-2">Tra Cứu Trạng Thái Đơn Hàng</h3>
            <p class="text-muted small mb-4">Nhập mã đơn hàng (được cấp khi bạn đặt hàng hoặc gửi qua email) để kiểm tra tiến trình đóng gói và vận chuyển theo thời gian thực.</p>

            <form action="index.php?page=order-tracking" method="POST" class="mb-3">
                <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden mb-3">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="order_code" class="form-control border-start-0 font-monospace text-uppercase" placeholder="Ví dụ: ORD-20261013-XXXXX" required autofocus>
                    <button class="btn btn-primary px-4 fw-bold" type="submit">Tra Cứu</button>
                </div>
            </form>

            <div class="d-flex align-items-center justify-content-center gap-2 text-muted small">
                <i class="bi bi-shield-lock-fill text-success"></i>
                Hệ thống hỗ trợ tra cứu nhanh cho cả khách vãng lai (không cần đăng nhập).
            </div>

            <div class="mt-4 pt-3 border-top">
                <a href="index.php" class="btn btn-link text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> Quay về trang chủ cửa hàng
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
