<?php
$pageTitle = "Cổng Thanh Toán Ví ZaloPay - Đơn Hàng #" . htmlspecialchars($order['order_code']) . " - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';

// Tạo URL hiển thị hình ảnh QR code
$qrImageSource = !empty($qrCode) ? $qrCode : $orderUrl;
$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=" . urlencode($qrImageSource);
?>

<div class="container my-5" style="max-width: 820px;">
    <div class="card border-0 shadow rounded-4 bg-white overflow-hidden">
        <!-- HEADER ZALOPAY -->
        <div class="p-4 text-white d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: linear-gradient(135deg, #008fe5 0%, #0066cc 100%);">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white p-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <img src="assets/images/zalopay.png" alt="ZaloPay" style="height: 32px; object-fit: contain;">
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-white">Cổng Thanh Toán Ví Điện Tử ZaloPay</h4>
                    <div class="small opacity-75">Hệ thống Sandbox thử nghiệm - Mã đơn: <strong class="text-warning font-monospace"><?= htmlspecialchars($order['order_code']) ?></strong></div>
                </div>
            </div>
            <div class="text-end">
                <div class="small opacity-75">Hết hạn sau:</div>
                <div class="badge bg-white text-primary fs-6 px-3 py-2 fw-bold" id="countdownTimer">
                    <i class="bi bi-clock-history me-1"></i>14:59
                </div>
            </div>
        </div>

        <div class="p-4 p-md-5">
            <!-- VÙNG CẢNH BÁO TRẠNG THÁI REALTIME -->
            <div id="gatewayStatusAlert" class="d-none"></div>

            <div class="row g-4 align-items-center">
                <!-- CỘT TRÁI: MÃ QR ZALOPAY -->
                <div class="col-md-5 text-center">
                    <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm position-relative">
                        <img src="<?= htmlspecialchars($qrImageUrl) ?>" alt="Mã QR ZaloPay" class="img-fluid rounded-3 border" style="max-width: 250px; min-height: 250px;">
                        <div class="mt-2 text-center">
                            <span class="badge bg-primary px-3 py-1 rounded-pill">
                                <i class="bi bi-qr-code-scan me-1"></i> Quét bằng App ZaloPay
                            </span>
                        </div>
                    </div>
                    <div class="small text-muted mt-2">
                        <i class="bi bi-shield-check text-success me-1"></i> Hỗ trợ quét bằng <strong>App ZaloPay</strong> hoặc bất kỳ App Ngân hàng nào (VietQR).
                    </div>
                </div>

                <!-- CỘT PHẢI: CHI TIẾT ĐƠN HÀNG & NÚT THAO TÁC -->
                <div class="col-md-7">
                    <div class="bg-light rounded-3 p-3 border mb-3">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">Người nhận hàng:</span>
                            <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">Mã đơn hàng:</span>
                            <span class="font-monospace text-primary fw-bold"><?= htmlspecialchars($order['order_code']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">Phương thức:</span>
                            <span class="badge" style="background-color: #008fe5;">Ví Điện Tử ZaloPay</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 align-items-baseline">
                            <span class="text-muted small">Tổng thanh toán:</span>
                            <span class="fs-4 fw-extrabold text-danger"><?= number_format($order['total_amount'], 0, ',', '.') ?> đ</span>
                        </div>
                    </div>

                    <!-- THAO TÁC THANH TOÁN -->
                    <div class="d-flex flex-column gap-2">
                        <?php if (!empty($orderUrl)): ?>
                            <a href="<?= htmlspecialchars($orderUrl) ?>" class="btn btn-outline-primary w-100 fw-bold py-2" target="_blank">
                                <i class="bi bi-phone-vibrate me-1"></i> Mở Trực Tiếp Trên App ZaloPay (Mobile)
                            </a>
                        <?php endif; ?>

                        <!-- NÚT XÁC NHẬN TEST SANDBOX DÀNH CHO BẢO VỆ ĐỒ ÁN / KIỂM THỬ -->
                        <a href="index.php?page=zalopay-return&order_code=<?= urlencode($order['order_code']) ?>&amount=<?= (int)$order['total_amount'] ?>&sandbox_demo=1" class="btn btn-outline-success btn-sm w-100 fw-bold py-2">
                            <i class="bi bi-shield-check me-2"></i> Xác Nhận Đã Thanh Toán (Giả Lập Demo Sandbox)
                        </a>

                        <a href="index.php?page=cart" class="btn btn-link text-muted small text-decoration-none text-center mt-1">
                            <i class="bi bi-arrow-left me-1"></i> Hủy giao dịch và quay lại giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light p-3 text-center text-muted small border-top">
            <i class="bi bi-info-circle text-primary me-1"></i>
            Trang web đang tự động kiểm tra trạng thái thanh toán từ máy chủ ZaloPay theo thời gian thực (Real-time API Polling).
        </div>
    </div>
</div>

<script>
// 1. Đếm ngược 15 phút giữ đơn
let timeLeft = 15 * 60;
const timerEl = document.getElementById('countdownTimer');
const countdownInterval = setInterval(() => {
    timeLeft--;
    if (timeLeft <= 0) {
        clearInterval(countdownInterval);
        timerEl.innerHTML = '<span class="text-danger fw-bold">Hết hạn</span>';
        alert('Giao dịch đã hết hạn thanh toán. Vui lòng đặt lại đơn hàng.');
        window.location.href = 'index.php?page=cart';
        return;
    }
    const mins = Math.floor(timeLeft / 60).toString().padStart(2, '0');
    const secs = (timeLeft % 60).toString().padStart(2, '0');
    timerEl.innerHTML = `<i class="bi bi-clock-history me-1"></i>${mins}:${secs}`;
}, 1000);

// 2. Realtime Ajax Polling kiểm tra trực tiếp từ ZaloPay API mỗi 3s
const orderCode = "<?= htmlspecialchars($order['order_code']) ?>";
const orderId = "<?= htmlspecialchars($order['id']) ?>";
const checkInterval = setInterval(() => {
    fetch(`index.php?page=api-check-gateway&gateway=ZALOPAY&order_code=${encodeURIComponent(orderCode)}`)
        .then(res => res.json())
        .then(data => {
            if (data.is_paid) {
                clearInterval(checkInterval);
                window.location.href = `index.php?page=order-tracking&id=${orderId}&msg=paid`;
            } else if (data.is_cancelled) {
                // Người dùng đã ấn hủy trên app ZaloPay
                const statusAlert = document.getElementById('gatewayStatusAlert');
                if (statusAlert) {
                    statusAlert.className = 'alert alert-warning border small text-dark d-flex align-items-center gap-2 mb-4 p-3 shadow-sm';
                    statusAlert.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i><div><strong>Giao dịch đã bị hủy:</strong> Hệ thống nhận diện thao tác hủy giao dịch trên ZaloPay. Bạn có thể quét lại hoặc hủy để chọn phương thức khác.</div>';
                }
            }
        })
        .catch(err => console.log('Polling check error:', err));
}, 3000);
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
