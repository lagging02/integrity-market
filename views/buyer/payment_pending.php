<?php
$pageTitle = "Chờ Thanh Toán Đơn Hàng #" . htmlspecialchars($order['order_code']) . " - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 880px;">
    <!-- TIÊU ĐỀ TRẠNG THÁI -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="spinner-grow text-warning" role="status" style="width: 2.2rem; height: 2.2rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>
                    <h4 class="fw-extrabold text-dark mb-0">Đang Chờ Quét Mã Thanh Toán</h4>
                    <div class="text-muted small">Mã đơn hàng: <strong class="text-primary font-monospace fs-6"><?= htmlspecialchars($order['order_code']) ?></strong></div>
                </div>
            </div>
            <div class="text-end">
                <div class="small text-muted mb-1">Thời gian giữ đơn còn lại:</div>
                <div class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6 px-3 py-2 fw-bold" id="countdownTimer">
                    <i class="bi bi-clock-history me-1"></i>14:59
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-center mt-2">
            <!-- CỘT TRÁI: MÃ QR NAPAS 247 -->
            <div class="col-md-5 text-center">
                <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm position-relative">
                    <img src="<?= htmlspecialchars($vietqrUrl) ?>" alt="Mã VietQR Thanh Toán" class="img-fluid rounded-3" style="max-width: 240px; min-height: 240px;">
                    <div class="small text-muted mt-2 fw-semibold">
                        <i class="bi bi-qr-code-scan text-primary me-1"></i>Quét bằng App Ngân Hàng
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: THÔNG TIN TÀI KHOẢN & SỐ TIỀN CHÍNH XÁC -->
            <div class="col-md-7">
                <div class="alert alert-light border d-flex align-items-center gap-2 py-2 px-3 mb-3 text-muted small">
                    <i class="bi bi-shield-check text-success fs-5"></i>
                    <div>Mã QR tự động điền sẵn <strong>Số tiền</strong> và <strong>Nội dung</strong> chính xác.</div>
                </div>

                <div class="d-flex flex-column gap-2 small">
                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                        <span class="text-muted">Ngân hàng thụ hưởng:</span>
                        <strong class="text-dark">MB Bank (Ngân Hàng Quân Đội)</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                        <span class="text-muted">Số tài khoản:</span>
                        <div class="d-flex align-items-center gap-2">
                            <strong class="fs-6 text-primary font-monospace" id="accNumber">0359876543</strong>
                            <button class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="copyText('accNumber', this)">Sao chép</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                        <span class="text-muted">Chủ tài khoản:</span>
                        <strong class="text-dark">NGUYEN VAN A</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                        <span class="text-muted">Số tiền cần chuyển:</span>
                        <div class="d-flex align-items-center gap-2">
                            <strong class="fs-5 text-danger font-monospace"><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</strong>
                            <button class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="copyValue('<?= $order['total_amount'] ?>', this)">Sao chép</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border">
                        <span class="text-muted">Nội dung chuyển khoản:</span>
                        <div class="d-flex align-items-center gap-2">
                            <strong class="badge bg-warning text-dark font-monospace fs-6" id="orderContent"><?= htmlspecialchars($order['order_code']) ?></strong>
                            <button class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="copyText('orderContent', this)">Sao chép</button>
                        </div>
                    </div>
                </div>

                <!-- THANH TỰ ĐỘNG CHỜ XÁC THỰC -->
                <div class="mt-4 p-3 bg-light rounded-3 border text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2 text-primary small fw-bold mb-1">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        <span id="statusMessage">Hệ thống đang chờ ngân hàng thông báo biến động số dư...</span>
                    </div>
                    <div class="text-muted" style="font-size: 11px;">
                        Sau khi bạn chuyển khoản thành công trên app ngân hàng, trang này sẽ <strong>tự động chuyển sang trạng thái ĐÃ THANH TOÁN</strong>.
                    </div>
                </div>

                <!-- NÚT MÔ PHỎNG DÀNH CHO BÁO CÁO THUYẾT MINH -->
                <form action="index.php?page=confirm-pending-payment" method="POST" class="mt-3">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                    <button type="submit" class="btn btn-outline-success btn-sm w-100 fw-bold py-2 shadow-sm">
                        <i class="bi bi-patch-check-fill me-1"></i> [DEMO]: Giả lập Ngân hàng báo tiền về (Webhook Success)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TÓM TẮT ĐƠN HÀNG RÚT GỌN -->
    <div class="card border rounded-3 bg-white p-3 shadow-sm">
        <div class="d-flex justify-content-between align-items-center small text-muted">
            <div>
                <strong>Người nhận:</strong> <?= htmlspecialchars($order['customer_name']) ?> | 
                <strong>Đơn vị vận chuyển:</strong> <?= htmlspecialchars($order['shipping_carrier']) ?>
            </div>
            <div>
                <a href="index.php?page=my-orders" class="text-decoration-none">Xem danh sách đơn mua</a>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Đồng hồ đếm ngược 15 phút
let timeLeft = 15 * 60;
const timerEl = document.getElementById('countdownTimer');
const timerInterval = setInterval(() => {
    timeLeft--;
    if (timeLeft <= 0) {
        clearInterval(timerInterval);
        timerEl.innerText = "Hết hạn";
        return;
    }
    const mins = Math.floor(timeLeft / 60);
    const secs = timeLeft % 60;
    timerEl.innerHTML = `<i class="bi bi-clock-history me-1"></i>${mins}:${secs < 10 ? '0' : ''}${secs}`;
}, 1000);

// 2. Tự động kiểm tra trạng thái thanh toán Realtime (Polling Ajax mỗi 3 giây)
const orderId = "<?= htmlspecialchars($order['id']) ?>";
const pollInterval = setInterval(() => {
    fetch(`index.php?page=check-payment-status&id=${orderId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.is_paid) {
                clearInterval(pollInterval);
                document.getElementById('statusMessage').innerText = "Thanh toán thành công! Đang chuyển hướng...";
                setTimeout(() => {
                    window.location.href = `index.php?page=order-success&id=${orderId}&msg=paid`;
                }, 800);
            }
        })
        .catch(err => console.log('Polling error:', err));
}, 3000);

// 3. Hàm copy tiện lợi
function copyText(elementId, btn) {
    const text = document.getElementById(elementId).innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        btn.innerText = "Đã chép!";
        setTimeout(() => btn.innerText = "Sao chép", 1500);
    });
}
function copyValue(val, btn) {
    navigator.clipboard.writeText(val).then(() => {
        btn.innerText = "Đã chép!";
        setTimeout(() => btn.innerText = "Sao chép", 1500);
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
