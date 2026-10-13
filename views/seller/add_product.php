<?php
$pageTitle = "Đăng Bán Sản Phẩm Mới - " . htmlspecialchars($shop['shop_name']);
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-4" style="max-width: 850px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-plus-circle text-primary me-2"></i>Đăng Bán Sản Phẩm Mới</h4>
            <p class="text-muted small mb-0">Hỗ trợ sản phẩm Công nghệ chính hãng hoặc tác phẩm Thủ công & Sáng tạo DIY.</p>
        </div>
        <a href="index.php?page=seller-products" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Quay lại kho
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border rounded-3 bg-white shadow-sm p-4">
        <form action="index.php?page=seller-add-product" method="POST">
            <?= \App\Services\CsrfService::field() ?>
            <!-- 1. THÔNG TIN CƠ BẢN -->
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">1. Thông Tin Cơ Bản</h6>

            <div class="mb-3">
                <label class="form-label small fw-bold">Tên Sản Phẩm <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Ví dụ: Đèn Ngủ Gỗ Epoxy Thủ Công hoặc Bàn Phím Cơ Custom..." required>
                <div class="form-text small">Tên sản phẩm sẽ được kiểm tra chống trùng lặp trong gian hàng.</div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Danh Mục Ngành Hàng <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (<?= $cat['type'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Mã SKU (Tùy chọn)</label>
                    <input type="text" name="sku" class="form-control" placeholder="Tự động tạo nếu để trống">
                </div>
            </div>

            <!-- 2. PHÂN LOẠI HÀNG THỦ CÔNG DIY VS CÔNG NGHỆ -->
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">2. Tính Chất Sản Phẩm (Thủ Công DIY / Công Nghệ)</h6>

            <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="diySwitch" name="is_diy" value="1" onchange="toggleDiyFields(this.checked)">
                    <label class="form-check-label fw-bold text-dark" for="diySwitch">
                        Đây là Sản Phẩm Thủ Công / Đồ Sáng Tạo DIY (Custom Made)
                    </label>
                </div>
                <div class="small text-muted mb-2">
                    Bật tùy chọn này nếu sản phẩm là đồ handmade, chế tác riêng hoặc kit bàn phím tự lắp ráp cần thời gian chuẩn bị.
                </div>

                <div id="diyDetails" style="display: none;" class="mt-3 pt-3 border-top">
                    <label class="form-label small fw-bold text-warning-emphasis">Thời Gian Hoàn Thiện / Chế Tác Dự Kiến:</label>
                    <input type="text" name="diy_craft_time" class="form-control form-control-sm" placeholder="Ví dụ: 1-2 ngày, 3-5 ngày hoặc Có sẵn kit">
                </div>
            </div>

            <!-- 3. GIÁ & KHO HÀNG -->
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">3. Giá Bán & Tồn Kho</h6>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Giá Bán Niêm Yết (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" placeholder="450000" min="1000" step="1000" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Giá Gốc Trước Giảm (VNĐ)</label>
                    <input type="number" name="original_price" class="form-control" placeholder="590000" min="0" step="1000">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Số Lượng Tồn Kho Sẵn Có</label>
                    <input type="number" name="stock" class="form-control" value="15" min="1">
                </div>
            </div>

            <!-- 4. THÔNG SỐ VÀ BIẾN THỂ -->
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">4. Thuộc Tính / Thông Số Kỹ Thuật (Key-Value)</h6>

            <div class="row g-2 mb-2">
                <div class="col-5">
                    <input type="text" name="attributes[Thuộc tính 1]" class="form-control form-control-sm" placeholder="Tên (ví dụ: Chất liệu hoặc Chip)">
                </div>
                <div class="col-7">
                    <input type="text" name="attributes[Giá trị 1]" class="form-control form-control-sm" placeholder="Giá trị (ví dụ: Gỗ Me Tây hoặc Apple M2)">
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-5">
                    <input type="text" name="attributes[Thuộc tính 2]" class="form-control form-control-sm" placeholder="Tên (ví dụ: Kích thước hoặc RAM)">
                </div>
                <div class="col-7">
                    <input type="text" name="attributes[Giá trị 2]" class="form-control form-control-sm" placeholder="Giá trị (ví dụ: Cao 20cm hoặc 16GB)">
                </div>
            </div>

            <!-- 5. HÌNH ẢNH & MÔ TẢ -->
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">5. Hình Ảnh & Mô Tả</h6>

            <div class="mb-3">
                <label class="form-label small fw-bold">Đường Dẫn Hình Ảnh (URL)</label>
                <input type="url" name="image" class="form-control" placeholder="https://images.unsplash.com/photo-...">
                <div class="form-text small">Có thể dùng ảnh từ Unsplash hoặc link CDN hình ảnh.</div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold">Mô Tả Sản Phẩm Chi Tiết</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Mô tả chất liệu, quy trình làm ra tác phẩm, chính sách bảo hành..."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?page=seller-products" class="btn btn-light border px-4">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="bi bi-cloud-arrow-up me-1"></i>Đăng Bán Ngay
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDiyFields(isChecked) {
    document.getElementById('diyDetails').style.display = isChecked ? 'block' : 'none';
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
