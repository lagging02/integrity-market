<?php
$pageTitle = "Giỏ Hàng Của Bạn - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';

// Nhóm sản phẩm theo từng Gian Hàng (Multi-Vendor Pattern)
$cartByShop = [];
foreach ($cart as $key => $item) {
    $shopId = $item['shop_id'] ?? 'default';
    if (!isset($cartByShop[$shopId])) {
        $cartByShop[$shopId] = [
            'shop_name' => $item['shop_name'] ?? 'Gian Hàng Đối Tác',
            'items'     => []
        ];
    }
    $cartByShop[$shopId]['items'][$key] = $item;
}
?>

<div class="container my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-cart3 text-primary me-2"></i>Giỏ Hàng Của Bạn</h4>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Tiếp tục mua sắm</a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>Đã cập nhật giỏ hàng thành công!</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <div class="card p-5 text-center border-0 shadow-sm bg-white rounded-3 my-4">
            <div class="mb-3">
                <i class="bi bi-cart-x text-muted" style="font-size: 4.5rem;"></i>
            </div>
            <h5 class="fw-bold">Giỏ hàng của bạn đang trống!</h5>
            <p class="text-muted">Hãy dạo quanh sàn để chọn những món đồ công nghệ hoặc tác phẩm sáng tạo DIY ưng ý nhé.</p>
            <div class="d-flex justify-content-center gap-2 mt-2">
                <a href="index.php?type=DIY" class="btn btn-danger px-4">Khám phá đồ DIY</a>
                <a href="index.php" class="btn btn-primary px-4">Khám phá toàn bộ sàn</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- DANH SÁCH SẢN PHẨM THEO GIAN HÀNG -->
            <div class="col-lg-8">
                <?php foreach ($cartByShop as $shopId => $shopGroup): ?>
                    <div class="card border rounded-3 mb-3 bg-white shadow-sm overflow-hidden">
                        <!-- HEADER GIAN HÀNG -->
                        <div class="card-header bg-light border-bottom d-flex align-items-center justify-content-between py-2 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shop text-primary"></i>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($shopGroup['shop_name']) ?></span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle small" style="font-size: 10px;">Xác Thực</span>
                            </div>
                            <span class="text-muted small">Vận chuyển qua GHN / GHTK</span>
                        </div>

                        <!-- CÁC MẶT HÀNG TRONG GIAN HÀNG NÀY -->
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small border-bottom bg-light bg-opacity-50">
                                        <th style="width: 45%;">Sản phẩm</th>
                                        <th class="text-center" style="width: 18%;">Đơn giá</th>
                                        <th class="text-center" style="width: 15%;">Số lượng</th>
                                        <th class="text-end" style="width: 17%;">Thành tiền</th>
                                        <th class="text-center" style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shopGroup['items'] as $key => $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded-2 border" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold fs-7">
                                                            <a href="index.php?page=product&id=<?= $item['product_id'] ?>" class="text-dark text-decoration-none">
                                                                <?= htmlspecialchars($item['name']) ?>
                                                            </a>
                                                        </h6>
                                                        <div class="d-flex gap-2">
                                                            <?php if (!empty($item['is_diy'])): ?>
                                                                <span class="badge-diy" style="font-size: 10px;">Đồ DIY</span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['variant_info'])): ?>
                                                                <span class="badge bg-light text-secondary border" style="font-size: 11px;">
                                                                    Phân loại: <?= htmlspecialchars($item['variant_info']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center fw-medium">
                                                <?= number_format($item['price'], 0, ',', '.') ?> đ
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border px-3 py-2 fw-bold">
                                                    <?= $item['quantity'] ?>
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold text-danger">
                                                <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> đ
                                            </td>
                                            <td class="text-center">
                                                <a href="index.php?page=cart-remove&key=<?= urlencode($key) ?>" class="btn btn-outline-danger btn-sm p-1 rounded-circle" title="Xóa món này">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="index.php?page=cart-clear" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng không?')">
                        <i class="bi bi-trash me-1"></i>Xóa sạch giỏ hàng
                    </a>
                </div>
            </div>

            <!-- TỔNG KẾT ĐƠN HÀNG (ORDER SUMMARY) -->
            <div class="col-lg-4">
                <div class="card border rounded-3 bg-white shadow-sm p-3 sticky-top" style="top: 80px;">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom">Tóm Tắt Đơn Hàng</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tổng tiền hàng:</span>
                        <span class="fw-bold"><?= number_format($totalAmount, 0, ',', '.') ?> đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phí vận chuyển 3PL (GHN):</span>
                        <span class="fw-bold text-primary">30.000 đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Bảo hiểm hàng hóa (P2P):</span>
                        <span class="text-success fw-bold">Miễn phí</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="fw-bold fs-6">Tổng thanh toán:</span>
                        <span class="fs-4 fw-extrabold text-danger"><?= number_format($totalAmount + 30000, 0, ',', '.') ?> đ</span>
                    </div>

                    <a href="index.php?page=checkout" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm mb-3">
                        <i class="bi bi-shield-check me-2"></i>Tiến Hành Đặt Hàng
                    </a>

                    <!-- CUSTOMER BENEFITS CALLOUT -->
                    <div class="p-3 bg-light rounded-2 border text-muted small">
                        <div class="d-flex align-items-center gap-2 mb-1 text-dark fw-bold">
                            <i class="bi bi-shield-check text-success"></i> Quyền Lợi Mua Hàng
                        </div>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-1" style="font-size: 11px;">
                            <li>• Được mở hộp kiểm tra hàng trước khi nhận (Đồng kiểm).</li>
                            <li>• Hỗ trợ đổi trả miễn phí trong 7 ngày nếu lỗi sản phẩm.</li>
                            <li>• Thanh toán an toàn, bảo vệ thông tin khách hàng.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
