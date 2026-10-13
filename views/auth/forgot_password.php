<?php
$pageTitle = "Quên Mật Khẩu - Khôi Phục Tài Khoản - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 480px;">
    <div class="card border rounded-4 bg-white shadow-sm p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                <i class="bi bi-key-fill fs-3 text-warning-emphasis"></i>
            </div>
            <h4 class="fw-bold mb-1">Khôi Phục Mật Khẩu</h4>
            <p class="text-muted small">Nhập email đăng ký để nhận liên kết đặt lại mật khẩu bảo mật</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <?php if (!empty($resetUrl)): ?>
                <div class="card bg-light border-primary p-3 mb-4 rounded-3 text-start small">
                    <div class="fw-bold text-primary mb-1"><i class="bi bi-envelope-check-fill me-1"></i>Mô phỏng Email gửi tới người dùng:</div>
                    <div class="text-muted mb-2">Hệ thống đã tạo mã Token an toàn (SHA-256) có hiệu lực 15 phút.</div>
                    <a href="<?= htmlspecialchars($resetUrl) ?>" class="btn btn-sm btn-primary w-100 fw-bold">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Mở Liên Kết Đặt Lại Mật Khẩu
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="index.php?page=forgot-password" method="POST">
            <?= \App\Services\CsrfService::field() ?>

            <div class="mb-4">
                <label class="form-label small fw-bold">Địa chỉ Email đăng ký tài khoản</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control bg-light border-start-0 ps-0" placeholder="name@example.com" required autofocus>
                </div>
                <div class="form-text small text-muted">Chúng tôi sẽ gửi một liên kết xác thực một lần duy nhất.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                Gửi Liên Kết Xác Thực
            </button>

            <div class="text-center small">
                <a href="index.php?page=login" class="text-decoration-none fw-bold text-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại Đăng nhập
                </a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
