<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Gửi Hàng 3PL - <?= htmlspecialchars($order['order_code']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; padding: 20px; font-family: monospace, sans-serif; }
        .label-container {
            width: 105mm;
            min-height: 148mm;
            background: #fff;
            margin: 0 auto;
            border: 2px dashed #000;
            padding: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            color: #000;
        }
        .barcode {
            letter-spacing: 4px;
            font-size: 26px;
            font-weight: 900;
            text-align: center;
            font-family: 'Courier New', Courier, monospace;
            background: #f8fafc;
            border: 1px solid #000;
            padding: 8px 0;
            margin: 6px 0;
        }
        .barcode-bars {
            height: 40px;
            background: repeating-linear-gradient(90deg, #000, #000 2px, #fff 2px, #fff 5px, #000 5px, #000 8px, #fff 8px, #fff 10px);
            width: 90%;
            margin: 0 auto 5px auto;
        }
        .box-title { font-weight: bold; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #000; margin-bottom: 4px; }
        .content-text { font-size: 11px; line-height: 1.3; }
        @media print {
            body { background: none; padding: 0; }
            .no-print { display: none !important; }
            .label-container { border: 1px solid #000; box-shadow: none; margin: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="text-center mb-3 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 fw-bold shadow-sm">
        🖨️ In Phiếu Gửi Hàng (A6 Standard)
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary px-3 ms-2">Đóng cửa sổ</button>
</div>

<div class="label-container">
    <!-- HEADER ĐƠN VỊ VẬN CHUYỂN -->
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
        <div>
            <h5 class="fw-bold mb-0 text-uppercase"><?= htmlspecialchars($order['shipping_carrier']) ?></h5>
            <small style="font-size: 9px;">DỊCH VỤ CHUYỂN PHÁT NHANH B2C</small>
        </div>
        <div class="text-end">
            <div class="badge bg-dark text-white fs-6">SGN-BTH-08</div>
            <div style="font-size: 9px;">MÃ BƯU CỤC PHÁT</div>
        </div>
    </div>

    <!-- MÃ VẠCH VÀ TRACKING CODE -->
    <div class="text-center my-2">
        <div class="barcode-bars"></div>
        <div class="barcode">*<?= htmlspecialchars($order['tracking_code'] ?? 'GHN882910VN') ?>*</div>
        <div class="fw-bold" style="font-size: 11px;">MÃ VẬN ĐƠN: <?= htmlspecialchars($order['tracking_code'] ?? 'GHN882910VN') ?></div>
    </div>

    <!-- THÔNG TIN NGƯỜI GỬI & NGƯỜI NHẬN -->
    <div class="row g-2 border-top border-bottom py-2 my-2">
        <!-- Người gửi -->
        <div class="col-6 border-end">
            <div class="box-title">Từ: (Người Gửi)</div>
            <div class="content-text">
                <strong><?= htmlspecialchars($order['shop_name']) ?></strong><br>
                Kho hàng TMĐT Lai Tạo, Q. Bình Thạnh, TP.HCM<br>
                SĐT: 0988.112.233
            </div>
        </div>
        <!-- Người nhận -->
        <div class="col-6">
            <div class="box-title">Đến: (Người Nhận)</div>
            <div class="content-text">
                <strong class="fs-6"><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                <strong>SĐT: <?= htmlspecialchars($order['phone']) ?></strong><br>
                <?= htmlspecialchars($order['address']) ?>
            </div>
        </div>
    </div>

    <!-- THÔNG TIN ĐƠN HÀNG & THU HỘ -->
    <div class="row g-2 border-bottom pb-2 mb-2">
        <div class="col-7">
            <div class="box-title">Nội Dung Gói Hàng (SL):</div>
            <div class="content-text">
                <?php foreach ($order['items'] as $item): ?>
                    - <?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)<br>
                <?php endforeach; ?>
                <em class="text-muted" style="font-size: 10px;">Mã đơn sàn: <?= htmlspecialchars($order['order_code']) ?></em>
            </div>
        </div>
        <div class="col-5 border-start text-center">
            <div class="box-title">Tiền Thu Hộ (COD):</div>
            <div class="fw-bold fs-5 text-danger my-1">
                <?= $order['payment_status'] === 'PAID' ? '0 VNĐ' : number_format($order['total_amount'], 0, ',', '.') . ' đ' ?>
            </div>
            <div style="font-size: 9px; font-weight: bold;">
                <?= $order['payment_status'] === 'PAID' ? 'ĐÃ THANH TOÁN ONLINE' : 'THU TIỀN KHI GIAO' ?>
            </div>
        </div>
    </div>

    <!-- CHỈ DẪN BẢO MẬT & CHỮ KÝ -->
    <div class="content-text">
        <div><strong>Chỉ dẫn bưu tá:</strong> Cho khách xem hàng, không cho thử. Chuyển hoàn sau 3 lần giao thất bại.</div>
        <div class="mt-2 text-muted" style="font-size: 9px;">
            Hệ thống IntegrityMarket • Dữ liệu mã hóa AES-256 PII • In lúc <?= date('H:i d/m/Y') ?>
        </div>
    </div>

    <div class="row text-center mt-3 pt-2 border-top">
        <div class="col-6" style="font-size: 10px;">
            <strong>Chữ ký người gửi</strong><br><br><br>
            <span>(Đã ký)</span>
        </div>
        <div class="col-6" style="font-size: 10px;">
            <strong>Chữ ký người nhận</strong><br><br><br>
            <span>(Ký và ghi rõ họ tên)</span>
        </div>
    </div>
</div>

</body>
</html>
