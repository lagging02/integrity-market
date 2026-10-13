<?php
$pageTitle = htmlspecialchars($product['name']) . " - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-4">
    <!-- BREADCRUMBS -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="index.php?cat=<?= $product['category_id'] ?>" class="text-decoration-none"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <!-- CHI TIẾT SẢN PHẨM -->
    <div class="bg-white rounded-3 border p-4 mb-4">
        <div class="row g-4">
            <!-- ẢNH SẢN PHẨM -->
            <div class="col-lg-5">
                <div class="position-relative border rounded-3 overflow-hidden bg-light text-center">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid w-100" style="max-height: 450px; object-fit: cover;">
                    <div class="position-absolute top-0 start-0 m-3">
                        <?php if ($product['is_diy']): ?>
                            <span class="badge bg-danger shadow-sm px-3 py-2 fs-7">
                                <i class="bi bi-stars me-1"></i>Sáng Tạo DIY & Thủ Công
                            </span>
                        <?php else: ?>
                            <span class="badge bg-primary shadow-sm px-3 py-2 fs-7">
                                <i class="bi bi-patch-check-fill me-1"></i>Công Nghệ Chính Hãng
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CAM KẾT BẢO MẬT & VẬN CHUYỂN -->
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <div class="p-2 border rounded-2 text-center bg-light small">
                            <i class="bi bi-shield-check text-success fs-5 d-block mb-1"></i>
                            <span class="fw-bold">Thanh Toán An Toàn</span>
                            <div class="text-muted" style="font-size: 11px;">Mã hóa đa lớp & Bảo vệ PII</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-2 text-center bg-light small">
                            <i class="bi bi-truck text-primary fs-5 d-block mb-1"></i>
                            <span class="fw-bold">Vận Đơn 3PL Tự Động</span>
                            <div class="text-muted" style="font-size: 11px;">Đồng bộ GHN / GHTK</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- THÔNG TIN & NÚT MUA HÀNG -->
            <div class="col-lg-7">
                <!-- Thông tin Gian hàng (Shop) -->
                <div class="d-flex align-items-center justify-content-between p-2 px-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shop-window text-primary fs-5"></i>
                        <div>
                            <span class="small text-muted">Phân phối bởi Gian hàng:</span>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($product['shop_name']) ?></div>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-star-fill text-warning me-1"></i>5.0 Đánh giá tích cực
                    </span>
                </div>

                <!-- Tên Sản Phẩm -->
                <h3 class="fw-extrabold text-dark mb-2"><?= htmlspecialchars($product['name']) ?></h3>

                <!-- Đánh giá và Lượt bán -->
                <div class="d-flex align-items-center gap-3 small text-muted mb-3">
                    <span class="text-warning fw-bold"><i class="bi bi-star-fill"></i> <?= number_format($product['rating'], 1) ?> sao</span>
                    <span>•</span>
                    <span>Đã bán: <strong class="text-dark"><?= $product['sales_count'] ?></strong></span>
                    <span>•</span>
                    <span>Mã SKU: <span class="badge bg-light text-secondary border"><?= htmlspecialchars($product['sku']) ?></span></span>
                </div>

                <!-- Khối Giá Bán -->
                <div class="p-3 bg-light rounded-3 mb-3 d-flex align-items-baseline gap-3">
                    <span class="fs-2 fw-extrabold text-danger"><?= number_format($product['price'], 0, ',', '.') ?> đ</span>
                    <?php if ($product['original_price'] > $product['price']): ?>
                        <span class="fs-5 text-muted text-decoration-line-through"><?= number_format($product['original_price'], 0, ',', '.') ?> đ</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                            Tiết kiệm <?= number_format(100 - ($product['price'] / $product['original_price'] * 100)) ?>%
                        </span>
                    <?php endif; ?>
                </div>

                <!-- ĐẶC ĐIỂM RIÊNG CỦA HÀNG DIY SO VỚI HÀNG TECH -->
                <?php if ($product['is_diy']): ?>
                    <div class="alert alert-warning border-warning-subtle d-flex gap-3 align-items-start mb-3">
                        <i class="bi bi-hammer fs-3 text-warning-emphasis"></i>
                        <div>
                            <h6 class="fw-bold text-warning-emphasis mb-1">Quy Trình Chế Tác Thủ Công / Custom Made:</h6>
                            <p class="small mb-1 text-dark">
                                Sản phẩm này được xưởng thủ công chế tác tỉ mỉ. Thời gian hoàn thiện ước tính: 
                                <strong class="badge bg-warning text-dark"><?= htmlspecialchars($product['diy_craft_time'] ?? '1-2 ngày') ?></strong>
                            </p>
                            <span class="text-muted small">Khách hàng có thể để lại ghi chú kích thước/màu sắc riêng khi đặt hàng.</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info border-info-subtle d-flex gap-3 align-items-start mb-3">
                        <i class="bi bi-shield-check fs-3 text-primary"></i>
                        <div>
                            <h6 class="fw-bold text-primary-emphasis mb-1">Chính Sách Bảo Hành Thiết Bị Công Nghệ:</h6>
                            <p class="small mb-0 text-dark">
                                Bảo hành <strong>24 tháng</strong> chính hãng 1 đổi 1. Hỗ trợ kích hoạt bảo hành điện tử qua số serial / IMEI.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- THÔNG SỐ KỸ THUẬT HOẶC BIẾN THỂ THỦ CÔNG (ATTRIBUTES JSON) -->
                <?php if (!empty($attributes)): ?>
                    <div class="mb-3">
                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Thông Số & Đặc Tính Kỹ Thuật:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered small mb-0">
                                <tbody>
                                    <?php foreach ($attributes as $key => $val): ?>
                                        <tr>
                                            <th class="bg-light w-25 text-capitalize"><?= htmlspecialchars($key) ?></th>
                                            <td><?= htmlspecialchars($val) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- FORM ĐẶT MUA HÀNG -->
                <form action="index.php?page=cart-add" method="POST" class="mt-4">
                    <?= \App\Services\CsrfService::field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <!-- Lựa chọn biến thể nếu có -->
                    <?php if (!empty($attributes)): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Lựa Chọn Phân Loại:</label>
                            <select name="variant" class="form-select form-select-sm" style="max-width: 320px;">
                                <option value="Tiêu chuẩn (Mặc định)">Tiêu chuẩn (Mặc định)</option>
                                <?php foreach ($attributes as $k => $v): ?>
                                    <option value="<?= htmlspecialchars($k . ': ' . $v) ?>"><?= htmlspecialchars($k . ': ' . $v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- Số lượng -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <label class="form-label small fw-bold text-muted mb-0">Số lượng:</label>
                        <div class="input-group" style="width: 130px;">
                            <input type="number" name="quantity" class="form-control text-center" value="1" min="1" max="<?= $product['stock'] ?>">
                        </div>
                        <span class="small text-muted">Kho hàng: <strong><?= $product['stock'] ?></strong> sản phẩm</span>
                    </div>

                    <!-- Nút Thêm vào giỏ & Mua ngay -->
                    <div class="d-flex flex-wrap gap-3">
                        <button type="submit" class="btn btn-outline-primary btn-lg px-4 fw-bold">
                            <i class="bi bi-cart-plus me-2"></i>Thêm Vào Giỏ Hàng
                        </button>
                        <button type="submit" formaction="index.php?page=cart-add&buy_now=1" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                            <i class="bi bi-lightning-charge-fill me-1"></i>Mua Ngay (1-Click)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MÔ TẢ CHI TIẾT SẢN PHẨM -->
        <div class="border-top mt-5 pt-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-card-text me-2 text-primary"></i>Mô Tả Chi Tiết Sản Phẩm</h5>
            <div class="text-secondary" style="line-height: 1.8;">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
