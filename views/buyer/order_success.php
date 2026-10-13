<?php
$pageTitle = "Đặt Hàng Thành Công - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 850px;">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'paid'): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
            <i class="bi bi-patch-check-fill fs-3 me-3 text-success"></i>
            <div>
                <strong>Thanh toán thành công!</strong> Đơn hàng của bạn đã được xác nhận thanh toán an toàn.
                <div class="small mt-1 text-success-emphasis">Đơn vị vận chuyển đang chuẩn bị nhận hàng để luân chuyển đến địa chỉ của bạn.</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- THẺ THÀNH CÔNG -->
    <div class="card border rounded-4 bg-white shadow-sm p-4 p-md-5 text-center mb-4">
        <div class="mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow" style="width: 80px; height: 80px;">
                <i class="bi bi-check-lg" style="font-size: 2.8rem;"></i>
            </div>
        </div>

        <h2 class="fw-extrabold text-dark mb-2">Đặt Hàng Thành Công!</h2>
        <p class="text-muted mb-3">Cảm ơn bạn đã mua sắm tại IntegrityMarket. Đơn hàng của bạn đã được tiếp nhận và xử lý.</p>

        <!-- MÃ ĐƠN HÀNG VÀ TRẠNG THÁI -->
        <div class="d-inline-block bg-light p-3 rounded-3 border mx-auto mb-4 text-start" style="min-width: 320px;">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Mã đơn hàng:</span>
                <strong class="text-primary fs-6" id="displayOrderCode"><?= htmlspecialchars($order['order_code']) ?></strong>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted small">Trạng thái thanh toán:</span>
                <?php if ($order['payment_status'] === 'PAID'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>ĐÃ THANH TOÁN (PAID)</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>CHỜ THANH TOÁN</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- THÔNG TIN PHƯƠNG THỨC THANH TOÁN -->
        <?php if ($order['payment_method'] === 'TOKENIZATION_CARD'): ?>
            <div class="alert alert-success border-success-subtle text-start p-3 rounded-3 mb-4">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-credit-card-2-front-fill fs-5 text-success"></i>
                    <strong class="text-success">Thanh Toán Bằng Thẻ Thành Công</strong>
                </div>
                <div class="small text-muted">
                    Khoản tiền <strong><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</strong> đã được thanh toán an toàn qua thẻ liên kết của bạn.
                </div>
            </div>
        <?php elseif ($order['payment_method'] === 'VNPAY'): ?>
            <div class="alert alert-success border-success-subtle text-start p-3 rounded-3 mb-4">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-wallet2 fs-5 text-success"></i>
                    <strong class="text-success">Thanh Toán Qua Cổng VNPAY Thành Công</strong>
                </div>
                <div class="small text-muted">
                    Khoản tiền <strong><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</strong> đã được xác nhận thanh toán thành công qua Cổng thanh toán VNPAY.
                </div>
            </div>
        <?php elseif ($order['payment_method'] === 'COD'): ?>
            <div class="alert alert-secondary border-secondary-subtle text-start p-3 rounded-3 mb-4">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-cash-coin fs-5 text-secondary"></i>
                    <strong>Thanh Toán Khi Nhận Hàng (COD)</strong>
                </div>
                <div class="small text-muted">
                    Vui lòng chuẩn bị số tiền <strong><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</strong> khi bưu tá giao kiện hàng đến địa chỉ của bạn.
                </div>
            </div>
        <?php elseif ($order['payment_method'] === 'VIETQR' && $order['payment_status'] !== 'PAID'): ?>
            <!-- TRƯỜNG HỢP: THANH TOÁN VIETQR 24/7 DYNAMIC -->
            <div class="card border-primary rounded-3 bg-light p-4 mb-4 text-start">
                <div class="row g-4 align-items-center">
                    <div class="col-md-5 text-center">
                        <div class="p-2 bg-white rounded-3 border d-inline-block shadow-sm">
                            <img src="<?= htmlspecialchars($vietqrUrl) ?>" alt="Mã VietQR Thanh Toán" class="img-fluid" style="max-width: 200px;">
                        </div>
                        <div class="small text-muted mt-2">Quét mã bằng app ngân hàng</div>
                    </div>
                    <div class="col-md-7">
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-qr-code-scan me-2"></i>Quét Mã Chuyển Khoản Nhanh</h5>
                        <div class="d-flex flex-column gap-2 small mb-3">
                            <div class="d-flex justify-content-between border-bottom pb-1">
                                <span class="text-muted">Ngân hàng thụ hưởng:</span>
                                <strong>MB Bank (Ngân Hàng Quân Đội)</strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom pb-1">
                                <span class="text-muted">Số tài khoản:</span>
                                <strong class="fs-6 text-primary">0359876543</strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom pb-1">
                                <span class="text-muted">Chủ tài khoản:</span>
                                <strong>NGUYEN VAN A</strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom pb-1">
                                <span class="text-muted">Số tiền cần thanh toán:</span>
                                <strong class="fs-6 text-danger"><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom pb-1">
                                <span class="text-muted">Nội dung chuyển khoản:</span>
                                <strong class="badge bg-warning text-dark fs-7"><?= htmlspecialchars($order['order_code']) ?></strong>
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>Hệ thống sẽ tự động cập nhật đơn hàng ngay sau khi nhận được thông báo biến động số dư.
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- THÔNG TIN GIAO HÀNG -->
        <div class="card border rounded-3 p-3 bg-light text-start mb-4">
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Địa Chỉ Nhận Hàng:</h6>
            <div class="small text-muted">
                <div><strong>Người nhận:</strong> <span class="text-dark fw-semibold"><?= htmlspecialchars($order['customer_name']) ?></span></div>
                <div><strong>Số điện thoại:</strong> <span class="text-dark fw-semibold"><?= htmlspecialchars($order['phone_decrypted'] ?? $order['phone']) ?></span></div>
                <div><strong>Địa chỉ giao:</strong> <span class="text-dark fw-semibold"><?= htmlspecialchars($order['address_decrypted'] ?? $order['address']) ?></span></div>
                <div><strong>Gian hàng phụ trách:</strong> <?= htmlspecialchars($order['shop_name']) ?></div>
            </div>
        </div>

        <!-- DANH SÁCH SẢN PHẨM TRONG ĐƠN -->
        <div class="table-responsive text-start mb-4">
            <table class="table table-bordered table-sm small align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-center">Đơn giá</th>
                        <th class="text-center">Số lượng</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                <?php if (!empty($item['variant_info'])): ?>
                                    <div class="text-muted" style="font-size: 11px;">Phân loại: <?= htmlspecialchars($item['variant_info']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= number_format($item['price'], 0, ',', '.') ?> đ</td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-end fw-bold"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> đ</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light">
                        <td colspan="3" class="text-end text-muted">Phí vận chuyển 3PL:</td>
                        <td class="text-end fw-bold">25.000 đ</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="3" class="text-end fw-bold">Tổng thanh toán:</td>
                        <td class="text-end fw-extrabold text-danger fs-6"><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- CÁC NÚT ĐIỀU HƯỚNG TIẾP THEO -->
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="index.php?page=order-tracking&id=<?= $order['id'] ?>" class="btn btn-primary px-4 fw-bold shadow-sm">
                <i class="bi bi-truck me-2"></i>Theo Dõi Vận Đơn 3PL
            </a>
            <a href="index.php?page=my-orders" class="btn btn-outline-secondary px-4 fw-semibold">
                <i class="bi bi-box-seam me-1"></i>Đơn Mua Của Tôi
            </a>
            <a href="index.php" class="btn btn-light border px-4">
                Tiếp tục mua sắm
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
