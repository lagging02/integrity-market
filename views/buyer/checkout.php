<?php
$pageTitle = "Thanh Toán Đơn Hàng An Toàn - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';

// Tính toán lại
$subtotal = $totalAmount;
$shippingFee = 30000;
$grandTotal = $subtotal + $shippingFee;

// Dữ liệu người nhận (Tự động điền nếu đã đăng nhập và đã có hồ sơ)
$defaultName = $currentUser['full_name'] ?? '';
$defaultPhone = '';
$defaultAddress = '';
if (!empty($currentUser['id'])) {
    try {
        $pdo = \App\Models\Database::getInstance();
        $uStmt = $pdo->prepare("SELECT phone, address FROM users WHERE id = ?");
        $uStmt->execute([$currentUser['id']]);
        $uData = $uStmt->fetch();
        if ($uData) {
            $defaultPhone = \App\Services\EncryptionService::decryptField($uData['phone'] ?? '');
            $defaultAddress = \App\Services\EncryptionService::decryptField($uData['address'] ?? '');
        }
    } catch (\Exception $e) {}
}
?>

<div class="container my-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-shield-check text-primary me-2"></i>Thanh Toán Đơn Hàng</h3>
        <p class="text-muted small mb-0">Vui lòng kiểm tra địa chỉ nhận hàng và lựa chọn phương thức thanh toán phù hợp.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="index.php?page=checkout" method="POST">
        <?= \App\Services\CsrfService::field() ?>
        <div class="row g-4">
            <!-- CỘT TRÁI: ĐỊA CHỈ & PHƯƠNG THỨC THANH TOÁN -->
            <div class="col-lg-7">
                <!-- 1. ĐỊA CHỈ NHẬN HÀNG -->
                <div class="card border rounded-3 bg-white shadow-sm p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-danger"></i> 1. Địa Chỉ Nhận Hàng (Đơn Vị Vận Chuyển 3PL)
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Họ và Tên Người Nhận <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($defaultName) ?>" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Nhận Đơn Hàng <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" placeholder="example@gmail.com" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Số Điện Thoại Nhận Hàng <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($defaultPhone) ?>" placeholder="0901234567" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Đơn Vị 3PL Vận Chuyển <span class="text-danger">*</span></label>
                            <select name="shipping_carrier" class="form-select shadow-sm" id="carrierSelect" onchange="updateShipping(this)">
                                <option value="GHN Express - Tiêu Chuẩn" data-fee="28000" data-time="2 - 3 ngày" selected>GHN Express - Tiêu Chuẩn (28.000 đ)</option>
                                <option value="GHN Express - Hỏa Tốc" data-fee="22000" data-time="Trong 24 giờ">GHN Express - Hỏa Tốc (22.000 đ)</option>
                                <option value="GHN Tiết Kiệm" data-fee="18000" data-time="3 - 5 ngày">GHN Tiết Kiệm (18.000 đ)</option>
                            </select>
                            <div class="form-text small text-primary fw-semibold mt-1" id="deliveryEstimate">
                                <i class="bi bi-clock-history me-1"></i>Thời gian dự kiến: <strong>2 - 3 ngày</strong> (Kho Tổng TP.HCM)
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted d-flex align-items-center gap-2 mb-3 p-2 rounded-3">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <div>
                            <strong>Bưu cục xuất kho:</strong> Kho Tổng TP. Hồ Chí Minh (GHN Shop #6723370). Hỗ trợ chính sách <strong>Đồng kiểm hàng</strong> khi nhận.
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Địa Chỉ Giao Hàng Chi Tiết <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..." required><?= htmlspecialchars($defaultAddress) ?></textarea>
                    </div>
                </div>

                <!-- 2. PHƯƠNG THỨC THANH TOÁN -->
                <div class="card border rounded-3 bg-white shadow-sm p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-credit-card-2-front-fill text-primary"></i> 2. Phương Thức Thanh Toán
                        </h5>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                            <i class="bi bi-shield-lock-fill me-1"></i>PCI-DSS Secure
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-2 mb-3">
                        <!-- VNPAY -->
                        <label class="border rounded-3 p-3 bg-white d-flex align-items-center gap-3 shadow-sm position-relative" for="pay_vnpay" style="cursor: pointer; transition: all 0.2s;">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" id="pay_vnpay" value="VNPAY" checked>
                            <img src="assets/images/vnpay.png" alt="VNPAY" style="height: 38px; width: 100px; object-fit: contain;">
                            <div>
                                <span class="fw-bold text-dark fs-6 d-block">Cổng VNPAY (ATM / VNPAY-QR / Visa)</span>
                                <span class="small text-muted">Thanh toán tức thì qua Sandbox VNPAY</span>
                            </div>
                            <span class="badge bg-danger ms-auto">Khuyên dùng</span>
                        </label>

                        <!-- ZALOPAY -->
                        <label class="border rounded-3 p-3 bg-white d-flex align-items-center gap-3 shadow-sm" for="pay_zalopay" style="cursor: pointer; transition: all 0.2s;">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" id="pay_zalopay" value="ZALOPAY">
                            <img src="assets/images/zalopay.png" alt="ZaloPay" style="height: 38px; width: 100px; object-fit: contain;">
                            <div>
                                <span class="fw-bold text-dark fs-6 d-block">Ví Điện Tử ZaloPay</span>
                                <span class="small text-muted">Quét mã QR ZaloPay / VietQR động</span>
                            </div>
                        </label>

                        <!-- MOMO -->
                        <label class="border rounded-3 p-3 bg-white d-flex align-items-center gap-3 shadow-sm" for="pay_momo" style="cursor: pointer; transition: all 0.2s;">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" id="pay_momo" value="MOMO">
                            <img src="assets/images/momo.svg" alt="MoMo" style="height: 38px; width: 100px; object-fit: contain;">
                            <div>
                                <span class="fw-bold text-dark fs-6 d-block">Ví Điện Tử MoMo</span>
                                <span class="small text-muted">Cổng thanh toán MoMo Sandbox All-in-One</span>
                            </div>
                        </label>

                        <!-- PAYPAL -->
                        <label class="border rounded-3 p-3 bg-white d-flex align-items-center gap-3 shadow-sm" for="pay_paypal" style="cursor: pointer; transition: all 0.2s;">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" id="pay_paypal" value="PAYPAL">
                            <img src="assets/images/paypal.png" alt="PayPal" style="height: 38px; width: 100px; object-fit: contain;">
                            <div>
                                <span class="fw-bold text-dark fs-6 d-block">Cổng Quốc Tế PayPal</span>
                                <span class="small text-muted">Thanh toán thẻ quốc tế / Tài khoản PayPal USD</span>
                            </div>
                        </label>

                        <!-- COD -->
                        <label class="border rounded-3 p-3 bg-white d-flex align-items-center gap-3 shadow-sm" for="pay_cod" style="cursor: pointer; transition: all 0.2s;">
                            <input class="form-check-input mt-0 fs-5" type="radio" name="payment_method" id="pay_cod" value="COD">
                            <img src="assets/images/cod.svg" alt="COD" style="height: 38px; width: 100px; object-fit: contain;">
                            <div>
                                <span class="fw-bold text-dark fs-6 d-block">Thanh Toán Khi Nhận Hàng (COD)</span>
                                <span class="small text-muted">Trả tiền mặt cho bưu tá GHN khi nhận bưu kiện</span>
                            </div>
                            <span class="badge bg-secondary ms-auto">Đồng Kiểm 3PL</span>
                        </label>
                    </div>

                    <!-- KHỐI HƯỚNG DẪN TEST THANH TOÁN SANDBOX -->
                    <div class="border rounded-3 p-3 bg-light-subtle small border-primary-subtle">
                        <div class="fw-bold text-primary mb-1 d-flex align-items-center gap-1">
                            <i class="bi bi-info-circle-fill"></i> Hướng Dẫn Dữ Liệu Test Sandbox (Dành cho Chấm Đồ Án & Pentest):
                        </div>
                        <ul class="mb-0 ps-3 text-muted" style="font-size: 12px; line-height: 1.6;">
                            <li><strong>Thẻ test VNPAY:</strong> Ngân hàng <code>NCB</code> - Số thẻ: <code>9704198526191432198</code> - Tên: <code>NGUYEN VAN A</code> - Ngày: <code>07/15</code> - OTP: <code>123456</code>.</li>
                            <li><strong>ZaloPay / MoMo:</strong> Quét QR trên ứng dụng hoặc sử dụng nút <em>Xác nhận Demo Sandbox</em> tại trang kết quả.</li>
                            <li><strong>COD:</strong> Tạo đơn ngay, đơn hàng tự động kích hoạt mã vận đơn 3PL GHN Express.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: TÓM TẮT ĐƠN HÀNG VÀ XÁC NHẬN -->
            <div class="col-lg-5">
                <div class="card border rounded-3 bg-white shadow-sm p-4 sticky-top" style="top: 80px;">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom">Đơn Hàng Của Bạn (<?= count($cart) ?> món)</h5>

                    <!-- DANH SÁCH SẢN PHẨM RÚT GỌN -->
                    <div class="d-flex flex-column gap-3 mb-3" style="max-height: 280px; overflow-y: auto;">
                        <?php foreach ($cart as $item): ?>
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="rounded border" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold text-truncate small" style="max-width: 190px;"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="text-muted small">x<?= $item['quantity'] ?> <?= !empty($item['is_diy']) ? '<span class="badge-diy" style="font-size:9px;">DIY</span>' : '' ?></div>
                                    </div>
                                </div>
                                <div class="fw-bold small text-nowrap">
                                    <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> đ
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr>

                    <!-- TÍNH TOÁN TIỀN -->
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Tiền hàng:</span>
                        <span class="text-dark fw-bold"><?= number_format($subtotal, 0, ',', '.') ?> đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Phí vận chuyển GHN (TP.HCM):</span>
                        <span class="text-primary fw-bold" id="displayShippingFee">28.000 đ</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="fw-bold">Tổng thanh toán:</span>
                        <span class="fs-3 fw-extrabold text-danger" id="displayGrandTotal"><?= number_format($subtotal + 28000, 0, ',', '.') ?> đ</span>
                    </div>

                    <!-- NÚT ĐẶT HÀNG -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm mb-3">
                        <i class="bi bi-lock-fill me-2"></i>XÁC NHẬN VÀ ĐẶT HÀNG
                    </button>

                    <div class="text-center text-muted small" style="font-size: 11px;">
                        <i class="bi bi-shield-check text-success me-1"></i> Giao dịch bảo mật & Đồng kiểm hàng khi nhận (3PL GHN).
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const subtotal = <?= (int)$subtotal ?>;
function updateShipping(select) {
    const opt = select.options[select.selectedIndex];
    const fee = parseInt(opt.getAttribute('data-fee')) || 28000;
    const time = opt.getAttribute('data-time') || '2 - 3 ngày';
    const grand = subtotal + fee;
    document.getElementById('displayShippingFee').innerText = fee.toLocaleString('vi-VN') + ' đ';
    document.getElementById('displayGrandTotal').innerText = grand.toLocaleString('vi-VN') + ' đ';
    const estElem = document.getElementById('deliveryEstimate');
    if (estElem) {
        estElem.innerHTML = '<i class="bi bi-clock-history me-1"></i>Thời gian dự kiến: <strong>' + time + '</strong> (Kho Tổng TP.HCM)';
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
