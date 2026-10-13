<?php
$pageTitle = "Đăng Nhập Tài Khoản - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
$activeOtp = \App\Services\OtpService::getActiveSimulation();
?>

<div class="container my-5" style="max-width: 520px;">
    <!-- THÔNG BÁO MÔ PHỎNG SMS GATEWAY KHI YÊU CẦU OTP -->
    <div id="smsGatewayAlert" class="alert alert-warning border-warning shadow-sm mb-4 <?= $activeOtp ? '' : 'd-none' ?>" role="alert" style="border-left: 5px solid #f59e0b !important;">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="badge bg-dark text-white fw-bold"><i class="bi bi-chat-left-dots-fill me-1"></i>SMS GATEWAY (GHN / VIETTEL)</span>
            <span class="small text-muted" id="otpTimerText">Vừa xong</span>
        </div>
        <div class="small fw-semibold text-dark mt-2">
            [IntegrityMarket] Mã OTP đăng nhập bảo mật của bạn là: 
            <span class="badge bg-primary fs-6 px-2 py-1 mx-1 text-white font-monospace" id="otpCodeDisplay"><?= $activeOtp['plain_code'] ?? '------' ?></span>.
            <div class="text-muted fw-normal mt-1" style="font-size: 11px;">Mã có hiệu lực trong 3 phút. Tuyệt đối không chia sẻ mã này với bất kỳ ai để phòng tránh chiếm đoạt tài khoản.</div>
        </div>
    </div>

    <!-- KHUNG ĐĂNG NHẬP CHÍNH -->
    <div class="card border rounded-4 bg-white shadow-sm p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                <i class="bi bi-shield-lock-fill fs-3"></i>
            </div>
            <h4 class="fw-bold mb-1">Đăng Nhập</h4>
            <p class="text-muted small">Cổng xác thực an toàn chuẩn sàn TMĐT IntegrityMarket</p>
        </div>

        <!-- CÁC THÔNG BÁO HỆ THỐNG -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['notice']) && $_GET['notice'] === 'registered_success'): ?>
            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Đăng ký tài khoản thành công! Vui lòng đăng nhập để bắt đầu mua sắm hoặc mở Shop.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['notice']) && $_GET['notice'] === 'password_reset_success'): ?>
            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Mật khẩu đã được cập nhật thành công! Vui lòng đăng nhập với mật khẩu mới.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['notice']) && $_GET['notice'] === 'admin_required'): ?>
            <div class="alert alert-warning alert-dismissible fade show small" role="alert">
                <i class="bi bi-shield-lock-fill me-2"></i>Vui lòng đăng nhập bằng tài khoản Quản trị viên (Admin) để truy cập Bảng điều khiển bảo mật.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['notice']) && $_GET['notice'] === 'login_required'): ?>
            <div class="alert alert-info alert-dismissible fade show small" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>Vui lòng đăng nhập để tiếp tục thao tác.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- NAV TABS PHƯƠNG THỨC ĐĂNG NHẬP -->
        <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-4" id="loginMethodTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold small py-2" id="tab-password" data-bs-toggle="pill" data-bs-target="#panel-password" type="button" role="tab">
                    <i class="bi bi-key-fill me-1"></i> Mật khẩu
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold small py-2 position-relative" id="tab-otp" data-bs-toggle="pill" data-bs-target="#panel-otp" type="button" role="tab">
                    <i class="bi bi-phone-vibrate me-1"></i> Mã OTP SMS/Email
                    <span class="badge bg-danger position-absolute top-0 end-0 translate-middle p-1 rounded-circle" style="width: 8px; height: 8px;"></span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="loginMethodTabContent">
            <!-- TAB 1: ĐĂNG NHẬP BẰNG MẬT KHẨU (BCRYPT COST 12) -->
            <div class="tab-pane fade show active" id="panel-password" role="tabpanel">
                <form action="index.php?page=login" method="POST">
                    <?= \App\Services\CsrfService::field() ?>
                    <input type="hidden" name="auth_method" value="password">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Tên tài khoản hoặc Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control bg-light border-start-0 ps-0" placeholder="Tên đăng nhập hoặc email..." required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">Mật khẩu</label>
                            <a href="index.php?page=forgot-password" class="small text-decoration-none text-primary fw-semibold">Quên mật khẩu?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="loginPasswordInput" class="form-control bg-light border-start-0 border-end-0 ps-0" placeholder="Nhập mật khẩu..." required style="color: #334155;">
                            <button class="btn bg-light border border-start-0 text-muted" type="button" onclick="togglePasswordVisibility('loginPasswordInput', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-2 bg-light border rounded-3 mb-3 small">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold"><i class="bi bi-shield-check text-primary me-1"></i>Tài khoản Demo (Pass: <code>Password@123</code>):</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" role="button" onclick="document.querySelector('[name=username]').value='admin'; document.getElementById('loginPasswordInput').value='Password@123';">Admin: admin</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" role="button" onclick="document.querySelector('[name=username]').value='tech_seller'; document.getElementById('loginPasswordInput').value='Password@123';">Seller: tech_seller</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" role="button" onclick="document.querySelector('[name=username]').value='khachhang1'; document.getElementById('loginPasswordInput').value='Password@123';">Buyer: khachhang1</span>
                        </div>
                    </div>

                    <div class="form-check mb-4 small">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label text-muted" for="rememberMe">
                            Ghi nhớ phiên đăng nhập trên thiết bị này
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                        Đăng Nhập
                    </button>
                </form>
            </div>

            <!-- TAB 2: ĐĂNG NHẬP BẰNG MÃ OTP (SMS / EMAIL) -->
            <div class="tab-pane fade" id="panel-otp" role="tabpanel">
                <form action="index.php?page=login" method="POST" id="otpLoginForm">
                    <?= \App\Services\CsrfService::field() ?>
                    <input type="hidden" name="auth_method" value="otp">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Số điện thoại hoặc Email nhận OTP</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-phone"></i></span>
                            <input type="text" id="otpRecipientInput" name="otp_recipient" class="form-control bg-light border-start-0 ps-0" placeholder="Nhập số điện thoại hoặc email nhận OTP" value="<?= htmlspecialchars($activeOtp['recipient'] ?? '') ?>" required>
                            <button class="btn btn-outline-primary fw-bold" type="button" id="btnSendOtp" onclick="requestOtpCode()">
                                Gửi mã OTP
                            </button>
                        </div>
                        <div class="form-text small text-muted" id="otpSendNotice">Mã 6 chữ số sẽ được gửi qua SMS hoặc Email.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Mã xác thực OTP (6 chữ số)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                            <input type="text" name="otp_code" id="otpCodeInput" maxlength="6" class="form-control bg-light border-start-0 ps-0 fw-bold font-monospace fs-5 text-primary text-center" placeholder="Nhập 6 số OTP" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                            <span id="countdownContainer" class="d-none">Gửi lại mã sau: <strong id="countdownText" class="text-danger">60s</strong></span>
                            <span class="ms-auto"><i class="bi bi-clock-history me-1"></i>Hiệu lực 3 phút</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm mb-3">
                        <i class="bi bi-check2-circle me-1"></i> Xác Thực & Đăng Nhập
                    </button>
                </form>
            </div>
        </div>

        <!-- PHÂN CÁCH ĐA PHƯƠNG THỨC XÁC THỰC -->
        <div class="position-relative my-4 text-center">
            <hr class="text-muted">
            <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small fw-bold">
                HOẶC TIẾP TỤC VỚI
            </span>
        </div>

        <!-- CÁC PHƯƠNG THỨC XÁC THỰC HIỆN ĐẠI (GOOGLE SSO & PASSKEY BIOMETRICS) -->
        <div class="d-grid gap-2 mb-4">
            <!-- 1. GOOGLE IDENTITY SIGN-IN -->
            <button type="button" class="btn btn-outline-dark py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold" onclick="openGoogleSsoModal()">
                <svg width="18" height="18" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                Đăng nhập bằng Google
            </button>

            <!-- 2. BIOMETRIC PASSKEY / FIDO2 WEBAUTHN -->
            <button type="button" class="btn btn-outline-secondary py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold" onclick="openPasskeyModal()">
                <i class="bi bi-fingerprint fs-5 text-primary"></i>
                Đăng nhập bằng Passkey (Vân tay / Face ID)
            </button>
        </div>

        <div class="text-center small text-muted">
            Bạn chưa có tài khoản trên sàn? <a href="index.php?page=register" class="fw-bold text-primary text-decoration-none">Đăng ký ngay</a>
        </div>
    </div>
</div>

<!-- ================= MODAL GOOGLE SSO OAUTH2 IDENTITY SIMULATOR ================= -->
<div class="modal fade" id="googleSsoModal" tabindex="-1" aria-labelledby="googleSsoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <svg width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span class="fw-bold text-secondary fs-6">Đăng nhập bằng Google</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h5 class="fw-bold mb-1">Chọn tài khoản</h5>
                <p class="text-muted small mb-3">để tiếp tục đến <strong>IntegrityMarket</strong></p>

                <div class="list-group list-group-flush border rounded-3 mb-3">
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3" onclick="submitGoogleAuth('quanshin242005@gmail.com', 'Đỗ Minh Quân')">
                        <div class="bg-danger text-white rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">Q</div>
                        <div class="text-start">
                            <div class="fw-bold text-dark small">Đỗ Minh Quân</div>
                            <div class="text-muted" style="font-size: 12px;">quanshin242005@gmail.com</div>
                        </div>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3" onclick="submitGoogleAuth('buyer_demo@gmail.com', 'Khách Hàng Mua Sắm')">
                        <div class="bg-primary text-white rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">K</div>
                        <div class="text-start">
                            <div class="fw-bold text-dark small">Khách Hàng Mua Sắm</div>
                            <div class="text-muted" style="font-size: 12px;">buyer_demo@gmail.com</div>
                        </div>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3" onclick="submitGoogleAuth('seller_tech@gmail.com', 'Chủ Shop TechZone')">
                        <div class="bg-success text-white rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">S</div>
                        <div class="text-start">
                            <div class="fw-bold text-dark small">Chủ Shop TechZone</div>
                            <div class="text-muted" style="font-size: 12px;">seller_tech@gmail.com</div>
                        </div>
                    </button>
                </div>

                <form id="googleCustomForm" onsubmit="event.preventDefault(); submitGoogleAuth(document.getElementById('customGoogleEmail').value, 'Google User');">
                    <div class="input-group mb-2">
                        <input type="email" id="customGoogleEmail" class="form-control form-control-sm" placeholder="Sử dụng tài khoản Google khác..." required>
                        <button class="btn btn-sm btn-outline-secondary" type="submit">Tiếp tục</button>
                    </div>
                </form>

                <div class="text-muted mt-3" style="font-size: 11px;">
                    Để tiếp tục, Google sẽ chia sẻ tên, địa chỉ email, tùy chọn ngôn ngữ và ảnh hồ sơ của bạn với IntegrityMarket. Xem Chính sách quyền riêng tư.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL WEBAUTHN / BIOMETRIC PASSKEY ================= -->
<div class="modal fade" id="passkeyModal" tabindex="-1" aria-labelledby="passkeyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content rounded-4 border-0 shadow text-center p-4">
            <div class="mb-3">
                <div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Scanning...</span>
                </div>
            </div>
            <h5 class="fw-bold mb-1">Xác thực Sinh trắc học / Passkey</h5>
            <p class="text-muted small mb-3">Chạm vào cảm biến vân tay hoặc nhìn vào camera Face ID của thiết bị để xác thực</p>
            
            <div class="p-3 bg-light rounded-3 border small text-muted text-start mb-3">
                <i class="bi bi-shield-lock-fill text-success me-1"></i> Chuẩn <strong>FIDO2 / WebAuthn</strong> không gửi mật khẩu qua Internet. Khóa riêng tư (Private Key) được bảo vệ trong chip bảo mật TPM thiết bị của bạn.
            </div>

            <form action="index.php?page=login" method="POST" id="passkeyAuthForm">
                <?= \App\Services\CsrfService::field() ?>
                <input type="hidden" name="auth_method" value="passkey">
                <input type="hidden" name="passkey_user" id="passkeyUserInput" value="buyer1">

                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold">Chọn danh tính thiết bị đã đăng ký Passkey:</label>
                    <select class="form-select form-select-sm" onchange="document.getElementById('passkeyUserInput').value = this.value;">
                        <option value="buyer1">buyer1 (Khách Hàng Demo - iPhone 16 Pro / FaceID)</option>
                        <option value="seller1">seller1 (Người Bán Sáng Tạo - MacBook TouchID)</option>
                        <option value="admin_master">admin_master (Quản Trị Viên Sàn - YubiKey FIDO2)</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light w-50" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary w-50 fw-bold">Xác Nhận Vân Tay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FORM ẨN SUBMIT GOOGLE SSO -->
<form id="hiddenGoogleForm" action="index.php?page=login" method="POST" class="d-none">
    <?= \App\Services\CsrfService::field() ?>
    <input type="hidden" name="auth_method" value="google">
    <input type="hidden" name="google_email" id="hiddenGoogleEmail" value="">
    <input type="hidden" name="google_name" id="hiddenGoogleName" value="">
</form>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function requestOtpCode() {
    const recipient = document.getElementById('otpRecipientInput').value.trim();
    if (!recipient) {
        alert('Vui lòng nhập Số điện thoại hoặc Email nhận OTP!');
        document.getElementById('otpRecipientInput').focus();
        return;
    }

    const btn = document.getElementById('btnSendOtp');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang gửi...';

    const formData = new FormData();
    formData.append('recipient', recipient);
    formData.append('channel', recipient.includes('@') ? 'EMAIL' : 'SMS');

    fetch('index.php?page=send-otp', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo Toast SMS Gateway mô phỏng thực tế
            const alertBox = document.getElementById('smsGatewayAlert');
            alertBox.classList.remove('d-none');
            document.getElementById('otpCodeDisplay').innerText = data.demo_code;
            document.getElementById('otpCodeInput').value = data.demo_code; // Tự động điền mô phỏng SMS Autofill trên di động
            document.getElementById('otpSendNotice').innerText = data.message;

            // Bắt đầu đếm ngược 60s
            startCountdown(60);
        } else {
            alert(data.error || 'Có lỗi khi gửi OTP!');
            btn.disabled = false;
            btn.innerHTML = 'Gửi mã OTP';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối tới máy chủ gửi OTP!');
        btn.disabled = false;
        btn.innerHTML = 'Gửi mã OTP';
    });
}

function startCountdown(seconds) {
    const btn = document.getElementById('btnSendOtp');
    const container = document.getElementById('countdownContainer');
    const text = document.getElementById('countdownText');
    container.classList.remove('d-none');

    let remaining = seconds;
    const interval = setInterval(() => {
        remaining--;
        text.innerText = remaining + 's';
        btn.innerHTML = `Gửi lại (${remaining}s)`;

        if (remaining <= 0) {
            clearInterval(interval);
            btn.disabled = false;
            btn.innerHTML = 'Gửi lại mã OTP';
            container.classList.add('d-none');
        }
    }, 1000);
}

function openGoogleSsoModal() {
    const modal = new bootstrap.Modal(document.getElementById('googleSsoModal'));
    modal.show();
}

function submitGoogleAuth(email, name) {
    document.getElementById('hiddenGoogleEmail').value = email;
    document.getElementById('hiddenGoogleName').value = name;
    document.getElementById('hiddenGoogleForm').submit();
}

function openPasskeyModal() {
    const modal = new bootstrap.Modal(document.getElementById('passkeyModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
