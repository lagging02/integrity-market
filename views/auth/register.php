<?php
$pageTitle = "Đăng Ký Tài Khoản - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 540px;">
    <div class="card border rounded-4 bg-white shadow-sm p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                <i class="bi bi-person-plus-fill fs-3"></i>
            </div>
            <h4 class="fw-bold mb-1">Đăng Ký Tài Khoản</h4>
            <p class="text-muted small">Tạo một tài khoản duy nhất để mua sắm và bán hàng trên IntegrityMarket</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="index.php?page=register" method="POST">
            <?= \App\Services\CsrfService::field() ?>

            <div class="mb-3">
                <label class="form-label small fw-bold">Họ và Tên Đầy Đủ <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" placeholder="Trần Văn A" required autofocus>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Tên Đăng Nhập <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" placeholder="username123" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Địa Chỉ Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Mật Khẩu <span class="text-danger">*</span></label>
                <input type="password" name="password" minlength="12" class="form-control" placeholder="Tối thiểu 12 ký tự (chữ hoa, thường, số, ký tự đặc biệt)" required>
                <div class="form-text small text-muted">
                    <i class="bi bi-shield-lock-fill text-primary me-1"></i><strong>Chính sách mật khẩu mạnh:</strong> Tối thiểu 12 ký tự, bắt buộc bao gồm chữ hoa (A-Z), chữ thường (a-z), chữ số (0-9) và ký tự đặc biệt (!@#$%...). Ví dụ: <code>Password@123</code>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Số Điện Thoại</label>
                    <input type="tel" name="phone" class="form-control" placeholder="0901234567">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Địa Chỉ Nhận Hàng</label>
                    <input type="text" name="address" class="form-control" placeholder="Quận/Huyện, Tỉnh/TP...">
                </div>
            </div>

            <div class="form-check mb-4 small text-muted">
                <input class="form-check-input" type="checkbox" id="termsCheck" checked required>
                <label class="form-check-label" for="termsCheck">
                    Tôi đồng ý với <a href="index.php?page=terms" class="text-decoration-none text-primary">Quy chế hoạt động</a> và <a href="index.php?page=privacy-policy" class="text-decoration-none text-primary">Chính sách bảo mật</a> của IntegrityMarket.
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                Đăng Ký Tài Khoản
            </button>

            <div class="text-center small text-muted">
                Đã có tài khoản? <a href="index.php?page=login" class="fw-bold text-primary text-decoration-none">Đăng nhập ngay</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
