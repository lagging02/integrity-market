<?php
$pageTitle = "IntegrityMarket - Thế Giới Công Nghệ & Sáng Tạo DIY";
require __DIR__ . '/../layouts/header.php';
?>

<!-- BANNER HERO CHUẨN DOANH NGHIỆP -->
<div class="bg-dark text-white py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary-subtle text-primary border border-primary-subtle mb-3 small fw-bold">
                    <i class="bi bi-patch-check-fill"></i> Nền Tảng Mua Sắm & Sáng Tạo Trực Tuyến
                </div>
                <h1 class="display-5 fw-extrabold tracking-tight mb-3">
                    Công Nghệ Đỉnh Cao & <br><span class="text-primary">Sáng Tạo DIY Độc Bản</span>
                </h1>
                <p class="lead text-secondary mb-4" style="max-width: 600px;">
                    Hệ sinh thái kết nối các thương hiệu công nghệ chính hãng cùng các xưởng chế tác thủ công DIY độc bản. Cam kết chất lượng, bảo mật thanh toán tuyệt đối và giao vận hỏa tốc toàn quốc.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="index.php?type=DIY" class="btn btn-warning px-4 py-2 fw-bold text-dark shadow-sm">
                        <i class="bi bi-palette me-2"></i>Khám Phá Đồ DIY Sáng Tạo
                    </a>
                    <a href="index.php?type=TECH" class="btn btn-outline-light px-4 py-2 fw-bold">
                        <i class="bi bi-laptop me-2"></i>Thiết Bị Công Nghệ
                    </a>
                    <a href="index.php?page=seller" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                        <i class="bi bi-shop me-2"></i>Mở Gian Hàng Ngay
                    </a>
                </div>

                <!-- Cam kết dịch vụ khách hàng -->
                <div class="row mt-4 pt-3 border-top border-secondary-subtle g-3 text-secondary small">
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-success fs-5"></i>
                        <span>Cam Kết Chính Hãng<br><strong class="text-white">Kiểm Định Kép 100%</strong></span>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-info fs-5"></i>
                        <span>Thanh Toán An Toàn<br><strong class="text-white">Mã Hóa Đa Lớp</strong></span>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-warning fs-5"></i>
                        <span>Vận Đơn 3PL Tự Động<br><strong class="text-white">Đồng Kiểm Khi Nhận</strong></span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5 d-none d-lg-block">
                <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-10 backdrop-blur">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-dark border-secondary h-100 text-white p-3">
                                <span class="badge bg-danger mb-2 w-auto align-self-start">DIY & Handmade</span>
                                <h6 class="fw-bold">Xưởng Mộc & Epoxy</h6>
                                <p class="small text-secondary mb-0">Chế tác độc bản, cá nhân hóa theo yêu cầu khách hàng.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-dark border-secondary h-100 text-white p-3">
                                <span class="badge bg-primary mb-2 w-auto align-self-start">Official Tech</span>
                                <h6 class="fw-bold">TechZone Pro</h6>
                                <p class="small text-secondary mb-0">Linh kiện điện tử, bàn phím cơ, bảo hành 24 tháng.</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-dark border-secondary text-white p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-success text-white p-2 rounded-3"><i class="bi bi-patch-check-fill fs-4"></i></div>
                                    <div>
                                        <div class="fw-bold">Quy Trình Kiểm Tra Kép (Dual Verification)</div>
                                        <div class="small text-secondary">Chống trùng lặp sản phẩm & thẩm định chất lượng người bán.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KHU VỰC LỌC DANH MỤC & LOẠI SẢN PHẨM -->
<div class="container my-4">
    <!-- Nút Lọc Phân Loại -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 bg-white p-3 rounded-3 border">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-bold text-muted me-2"><i class="bi bi-funnel me-1"></i>Bộ lọc:</span>
            <a href="index.php" class="btn btn-sm <?= empty($_GET['type']) && empty($_GET['cat']) ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
                Tất cả (<?= count($products) ?>)
            </a>
            <a href="index.php?type=DIY" class="btn btn-sm <?= ($_GET['type'] ?? '') === 'DIY' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3">
                <i class="bi bi-palette me-1"></i>Đồ Sáng Tạo DIY / Thủ Công
            </a>
            <a href="index.php?type=TECH" class="btn btn-sm <?= ($_GET['type'] ?? '') === 'TECH' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
                <i class="bi bi-cpu me-1"></i>Thiết Bị Công Nghệ
            </a>
        </div>

        <!-- Dropdown danh mục chi tiết -->
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-tags me-1"></i>Danh Mục Sản Phẩm
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="index.php">Tất cả danh mục</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li><a class="dropdown-item" href="index.php?cat=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- TIÊU ĐỀ KHU VỰC VÀ TÌM KIẾM -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                <?php
                if (!empty($_GET['q'])) {
                    echo 'Kết quả tìm kiếm cho: "' . htmlspecialchars($_GET['q']) . '"';
                } elseif (($_GET['type'] ?? '') === 'DIY') {
                    echo '🎨 Đồ Sáng Tạo DIY & Thủ Công Nghệ Thuật';
                } elseif (($_GET['type'] ?? '') === 'TECH') {
                    echo '💻 Thiết Bị & Linh Kiện Công Nghệ Chính Hãng';
                } else {
                    echo '📦 Sản Phẩm Nổi Bật Trên Sàn';
                }
                ?>
            </h4>
            <p class="text-muted small mb-0">Tìm thấy <?= count($products) ?> sản phẩm đạt tiêu chuẩn kiểm duyệt.</p>
        </div>
    </div>

    <!-- DANH SÁCH SẢN PHẨM (PRODUCT GRID) -->
    <?php if (empty($products)): ?>
        <div class="text-center py-5 bg-white rounded-3 border my-4">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h5 class="fw-bold mt-3">Không tìm thấy sản phẩm phù hợp</h5>
            <p class="text-muted">Vui lòng thử tìm kiếm với từ khóa khác hoặc bỏ các tiêu chí lọc.</p>
            <a href="index.php" class="btn btn-primary btn-sm px-4">Xem tất cả sản phẩm</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($products as $p): ?>
                <div class="col">
                    <div class="card-product">
                        <!-- Ảnh sản phẩm & Badge phân loại -->
                        <div class="position-relative">
                            <a href="index.php?page=product&id=<?= $p['id'] ?>">
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                            </a>
                            <div class="position-absolute top-0 start-0 m-2">
                                <?php if ($p['is_diy']): ?>
                                    <span class="badge-diy shadow-sm">
                                        <i class="bi bi-stars me-1"></i>Thủ Công DIY
                                    </span>
                                <?php else: ?>
                                    <span class="badge-tech shadow-sm">
                                        <i class="bi bi-patch-check-fill me-1"></i>Chính Hãng
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Thân card -->
                        <div class="card-product-body">
                            <div>
                                <!-- Tên Gian hàng (Shop) -->
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <i class="bi bi-shop text-muted small"></i>
                                    <span class="text-muted small fw-medium text-truncate" style="max-width: 180px;">
                                        <?= htmlspecialchars($p['shop_name']) ?>
                                    </span>
                                </div>

                                <!-- Tên sản phẩm -->
                                <h6 class="fw-bold mb-2">
                                    <a href="index.php?page=product&id=<?= $p['id'] ?>" class="text-dark text-decoration-none" title="<?= htmlspecialchars($p['name']) ?>">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </a>
                                </h6>
                            </div>

                            <div>
                                <!-- Đánh giá sao & lượt bán -->
                                <div class="d-flex align-items-center gap-2 small text-muted mb-2">
                                    <span class="text-warning"><i class="bi bi-star-fill"></i> <?= number_format($p['rating'], 1) ?></span>
                                    <span>•</span>
                                    <span>Đã bán <?= $p['sales_count'] ?></span>
                                </div>

                                <!-- Giá bán -->
                                <div class="d-flex align-items-baseline justify-content-between mb-3">
                                    <div>
                                        <span class="price-current"><?= number_format($p['price'], 0, ',', '.') ?> đ</span>
                                        <?php if ($p['original_price'] > $p['price']): ?>
                                            <span class="price-original"><?= number_format($p['original_price'], 0, ',', '.') ?> đ</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-success-subtle text-success small">Sẵn <?= $p['stock'] ?></span>
                                </div>

                                <!-- Nút mua nhanh & Xem -->
                                <div class="d-flex gap-2">
                                    <a href="index.php?page=product&id=<?= $p['id'] ?>" class="btn btn-outline-secondary btn-sm w-50 fw-semibold">
                                        Chi tiết
                                    </a>
                                    <form action="index.php?page=cart-add" method="POST" class="w-50">
                                        <?= \App\Services\CsrfService::field() ?>
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                                            <i class="bi bi-cart-plus me-1"></i>Thêm
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- KHU VỰC GIAN HÀNG TIÊU BIỂU (MULTI-VENDOR SHOWCASE) -->
    <div class="mt-5 pt-4 border-top">
        <h4 class="fw-bold mb-3"><i class="bi bi-patch-check text-primary me-2"></i>Gian Hàng Xác Thực Tiêu Biểu</h4>
        <div class="row g-4">
            <?php foreach ($shops as $shop): ?>
                <div class="col-md-6">
                    <div class="card p-3 border rounded-3 bg-white h-100">
                        <div class="d-flex gap-3 align-items-center">
                            <img src="<?= htmlspecialchars($shop['avatar'] ?? 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=200') ?>" alt="<?= htmlspecialchars($shop['shop_name']) ?>" class="rounded-circle border" style="width: 65px; height: 65px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($shop['shop_name']) ?></h5>
                                    <span class="badge bg-primary-subtle text-primary"><i class="bi bi-check2-circle"></i> Đã Xác Thực</span>
                                </div>
                                <p class="text-muted small mb-1 mt-1"><?= htmlspecialchars($shop['description']) ?></p>
                                <div class="d-flex align-items-center gap-3 small text-muted">
                                    <span><i class="bi bi-star-fill text-warning me-1"></i><?= number_format($shop['rating'], 1) ?></span>
                                    <span>•</span>
                                    <span><i class="bi bi-truck text-primary me-1"></i>3PL Chuẩn GHN / GHTK</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
