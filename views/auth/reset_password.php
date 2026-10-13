<?php
$pageTitle = "Đặt Lại Mật Khẩu Mới - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 480px;">
    <div class="card border rounded-4 bg-white shadow-sm p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                <i class="bi bi-shield-check fs-3"></i>
            </div>
            <h4 class="fw-bold mb-1">Thiết Lập Mật Khẩu Mới</h4>
            <p class="text-muted small">Tạo mật khẩu mạnh mới cho tài khoản: <strong><?= htmlspecialchars($email ?? '') ?></strong></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($error) || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <form action="index.php?page=reset-password" method="POST">
                <?= \App\Services\CsrfService::field() ?>
                <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Mật khẩu mới <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="newPassword" class="form-control bg-light border-start-0 ps-0" placeholder="Tối thiểu 8 ký tự" required autofocus>
                    </div>
                    <div class="form-text small text-muted">Bảo mật bằng băm Bcrypt Salt Cost 12.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="confirm_password" class="form-control bg-light border-start-0 ps-0" placeholder="Nhập lại mật khẩu mới" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                    Cập Nhật Mật Khẩu
                </button>
            </form>
        <?php else: ?>
            <div class="text-center">
                <a href="index.php?page=forgot-password" class="btn btn-warning w-100 fw-bold">Yêu cầu liên kết mới</a>
            </div>
        <?php endif; ?>

        <div class="text-center small">
            <a href="index.php?page=login" class="text-decoration-none fw-bold text-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại Đăng nhập
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
