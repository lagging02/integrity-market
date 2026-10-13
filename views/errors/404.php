<?php
$pageTitle = "404 - Không Tìm Thấy Trang";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5 text-center py-5">
    <div class="mb-4">
        <span class="display-1 fw-extrabold text-muted">404</span>
    </div>
    <h3 class="fw-bold mb-2">Trang Bạn Yêu Cầu Không Tồn Tại</h3>
    <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
        Địa chỉ đường dẫn không chính xác hoặc trang đã được chuyển đi nơi khác. Vui lòng quay lại trang chủ.
    </p>
    <a href="index.php" class="btn btn-primary px-4 fw-bold shadow-sm">
        <i class="bi bi-house-door me-1"></i> Quay về Trang Chủ
    </a>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
