<?php
$pageTitle = "Khởi Tạo Gian Hàng Người Bán - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 650px;">
    <div class="card border rounded-4 bg-white shadow-sm p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 64px; height: 64px;">
                <i class="bi bi-shop-window fs-2"></i>
            </div>
            <h3 class="fw-bold mb-1">Mở Gian Hàng Bán Hàng</h3>
            <p class="text-muted small">Tài khoản của bạn sẽ kích hoạt thêm tính năng Kênh Người Bán (Seller Centre). Bạn vừa có thể mua hàng, vừa có thể đăng bán sản phẩm như Shopee.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="index.php?page=seller-register" method="POST">
            <?= \App\Services\CsrfService::field() ?>

            <div class="mb-3">
                <label class="form-label small fw-bold">Tên Gian Hàng / Tên Shop <span class="text-danger">*</span></label>
                <input type="text" name="shop_name" class="form-control" placeholder="Ví dụ: TechLab Studio hoặc Xưởng Mộc Decor..." required autofocus>
                <div class="form-text small">Tên gian hàng đại diện cho bạn trên sàn và xuất hiện trên phiếu gửi hàng 3PL.</div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Địa Chỉ Kho Lấy Hàng (Cho Bưu Tá GHN 3PL) <span class="text-danger">*</span></label>
                <textarea name="pickup_address" class="form-control" rows="2" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..." required></textarea>
                <div class="form-text small">Địa chỉ để bưu tá GHN / GHTK đến lấy hàng khi bạn phát sinh đơn hàng mới.</div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Mô Tả Gian Hàng</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Giới thiệu về các mặt hàng công nghệ chính hãng hoặc tác phẩm thủ công sáng tạo DIY bạn kinh doanh..."></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold">Ảnh Đại Diện Gian Hàng (URL Ảnh)</label>
                <input type="url" name="avatar" class="form-control" placeholder="https://images.unsplash.com/photo-...">
                <div class="form-text small">Có thể dùng ảnh từ Unsplash hoặc để trống để dùng ảnh mặc định.</div>
            </div>

            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-light border px-4">Để sau</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm flex-grow-1">
                    <i class="bi bi-check2-circle me-1"></i> Bắt Đầu Bán Hàng Ngay
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
