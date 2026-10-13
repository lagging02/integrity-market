<?php
$pageTitle = "Quản Lý Sản Phẩm - " . htmlspecialchars($shop['shop_name']);
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-4">
    <!-- SELLER TOP BAR & SHOP HEADER -->
    <div class="card border-0 rounded-4 bg-white shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-box-seam text-primary me-2"></i>Kho Sản Phẩm Của Gian Hàng</h4>
                <p class="text-muted small mb-0">Quản lý danh sách sản phẩm công nghệ và đồ chế tác DIY độc bản của <?= htmlspecialchars($shop['shop_name']) ?>.</p>
            </div>
            <div>
                <a href="index.php?page=seller-add-product" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i>Thêm Sản Phẩm Mới
                </a>
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
                <a class="nav-link active fw-bold text-primary" href="index.php?page=seller-products">
                    <i class="bi bi-box-seam me-1"></i>Quản Lý Sản Phẩm (<?= count($products) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="index.php?page=seller-orders">
                    <i class="bi bi-receipt-cutoff me-1"></i>Xử Lý Đơn Hàng & In Tem 3PL
                </a>
            </li>
        </ul>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>Sản phẩm mới đã được đăng bán thành công lên sàn và tự động kiểm duyệt trùng lặp!</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- DANH SÁCH SẢN PHẨM -->
    <div class="card border rounded-3 bg-white shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th style="width: 40%;">Sản phẩm</th>
                        <th>Phân loại</th>
                        <th>Giá niêm yết</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Đã bán</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Gian hàng chưa có sản phẩm nào. Hãy bấm "Thêm Sản Phẩm Mới" để bắt đầu bán hàng!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= htmlspecialchars($p['image']) ?>" alt="" class="rounded border" style="width: 55px; height: 55px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-dark fs-7"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="text-muted" style="font-size: 11px;">Mã SKU: <code><?= htmlspecialchars($p['sku']) ?></code></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($p['is_diy']): ?>
                                        <span class="badge-diy d-inline-block">
                                            <i class="bi bi-palette me-1"></i>DIY: <?= htmlspecialchars($p['diy_craft_time'] ?? 'Thủ công') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-tech d-inline-block">
                                            <i class="bi bi-cpu me-1"></i>Công Nghệ
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-danger"><?= number_format($p['price'], 0, ',', '.') ?> đ</div>
                                    <?php if ($p['original_price'] > $p['price']): ?>
                                        <div class="text-muted text-decoration-line-through" style="font-size: 10px;"><?= number_format($p['original_price'], 0, ',', '.') ?> đ</div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1"><?= $p['stock'] ?></span>
                                </td>
                                <td class="text-center fw-medium"><?= $p['sales_count'] ?></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Đang Bán</span>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?page=product&id=<?= $p['id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm p-1 px-2" title="Xem trên sàn">
                                        <i class="bi bi-eye"></i> Xem
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
