<?php
$currentUser = \App\Services\AuthService::user();
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'IntegrityMarket - Sàn Giao Dịch Thương Mại Điện Tử' ?></title>
    
    <!-- Open Graph & Social Media Preview (Facebook, Zalo, Messenger, Telegram) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://shop.itgslab.io.vn/">
    <meta property="og:title" content="IntegrityMarket - Thế Giới Công Nghệ & Sáng Tạo DIY">
    <meta property="og:description" content="Nền tảng thương mại điện tử bảo mật chuẩn PCI-DSS & OWASP Top 10. Tích hợp thanh toán VNPAY, MoMo, ZaloPay, PayPal và vận chuyển 3PL GHN Express.">
    <meta property="og:image" content="https://shop.itgslab.io.vn/assets/images/og_thumbnail.png">
    <meta property="og:image:secure_url" content="https://shop.itgslab.io.vn/assets/images/og_thumbnail.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="IntegrityMarket Official Brand Logo">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://shop.itgslab.io.vn/">
    <meta name="twitter:title" content="IntegrityMarket - Thế Giới Công Nghệ & Sáng Tạo DIY">
    <meta name="twitter:description" content="Nền tảng thương mại điện tử bảo mật chuẩn PCI-DSS & OWASP Top 10. Tích hợp thanh toán VNPAY, MoMo, ZaloPay, PayPal và vận chuyển 3PL GHN Express.">
    <meta name="twitter:image" content="https://shop.itgslab.io.vn/assets/images/og_thumbnail.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-hover: #1d4ed8;
            --diy-accent: #ea580c;
            --tech-accent: #0284c7;
            --bg-light: #f8fafc;
        }
        * { font-family: 'Plus Jakarta Sans', -apple-system, sans-serif; }
        body { background-color: var(--bg-light); color: #0f172a; min-height: 100vh; display: flex; flex-direction: column; }
        .main-content { flex: 1; }
        .top-utility-bar { background: #f8fafc; color: #64748b; font-size: 12px; border-bottom: 1px solid #e2e8f0; padding: 5px 0; }
        .top-utility-bar a { color: #64748b; text-decoration: none; transition: 0.2s; }
        .top-utility-bar a:hover { color: var(--brand-primary); }
        .navbar-main { background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .brand-logo { font-weight: 800; font-size: 20px; text-decoration: none; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .badge-diy { background: #ffedd5; color: #c2410c; font-weight: 700; font-size: 11px; padding: 4px 8px; border-radius: 6px; border: 1px solid #fdba74; }
        .badge-tech { background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 11px; padding: 4px 8px; border-radius: 6px; border: 1px solid #7dd3fc; }
        .card-product { border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; transition: all 0.25s ease; overflow: hidden; height: 100%; display: flex; flex-direction: column; }
        .card-product:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.08); border-color: #cbd5e1; }
        .card-product img { height: 210px; width: 100%; object-fit: cover; }
        .card-product-body { padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .price-current { font-size: 18px; font-weight: 800; color: #e11d48; }
        .price-original { font-size: 13px; color: #94a3b8; text-decoration: line-through; margin-left: 6px; }

        /* HEADER RESPONSIVE & FLUID SCALING */
        .header-container {
            max-width: 1600px;
            margin: 0 auto;
        }
        .header-search-form {
            flex: 1 1 180px;
            min-width: 130px;
            max-width: 380px;
            margin: 0 0.5rem;
            transition: all 0.2s ease;
        }
        .header-search-input {
            font-size: 13.5px;
        }
        .header-search-input::placeholder {
            color: #94a3b8 !important;
            opacity: 0.55 !important;
            font-weight: 400 !important;
        }
        .nav-link-scale {
            font-size: 13.5px;
            padding: 0.35rem 0.45rem !important;
            transition: all 0.15s ease;
        }
        .btn-auth {
            font-size: 13px;
            white-space: nowrap;
        }

        /* Khi màn hình cỡ vừa / laptop / cửa sổ thu nhỏ (992px - 1240px) */
        @media (min-width: 992px) and (max-width: 1240px) {
            .navbar-main .brand-logo .fw-extrabold { font-size: 16px !important; }
            .navbar-main .brand-logo .brand-icon-box { width: 34px !important; height: 34px !important; }
            .navbar-main .brand-logo .brand-icon-box i { font-size: 1.05rem !important; }
            .header-search-form { min-width: 120px !important; max-width: 210px !important; margin: 0 0.25rem !important; }
            .header-search-form .btn { padding: 0.3rem 0.5rem !important; font-size: 12.5px !important; }
            .navbar-nav { gap: 0.15rem !important; }
            .nav-link-scale { font-size: 12px !important; padding: 0.25rem 0.3rem !important; }
            .badge-hot-nav { font-size: 9px !important; padding: 2px 4px !important; }
            .btn-auth { font-size: 12px !important; padding: 0.25rem 0.5rem !important; }
            .btn-cart-nav { width: 34px !important; height: 34px !important; }
            .btn-cart-nav i { font-size: 1rem !important; }
        }

        /* Khi màn hình laptop lớn (1241px - 1400px) */
        @media (min-width: 1241px) and (max-width: 1400px) {
            .header-search-form { min-width: 160px; max-width: 300px; margin: 0 0.5rem; }
            .nav-link-scale { font-size: 13px; padding: 0.35rem 0.4rem !important; }
            .navbar-nav { gap: 0.35rem !important; }
        }

        /* Khi màn hình mobile / tablet portrait (< 992px) */
        @media (max-width: 991.98px) {
            .header-search-form { width: 100% !important; max-width: 100% !important; margin: 10px 0 !important; }
            .navbar-nav { flex-direction: column; align-items: flex-start !important; width: 100%; gap: 0.25rem !important; }
            .nav-link-scale { width: 100%; padding: 0.5rem 0 !important; font-size: 14px !important; }
            .auth-btn-group { width: 100%; margin-top: 0.5rem; justify-content: stretch; }
            .auth-btn-group a { flex: 1; text-align: center; }
        }
    </style>
</head>
<body>

<!-- TOP BAR DOANH NGHIỆP THỰC TẾ -->
<div class="top-utility-bar">
    <div class="container-fluid px-3 px-xl-4 px-xxl-5 header-container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <a href="index.php?page=seller"><i class="bi bi-shop me-1"></i>Kênh Người Bán</a>
            <span class="opacity-50">|</span>
            <a href="index.php?page=register"><i class="bi bi-person-plus me-1"></i>Mở Gian Hàng</a>
            <span class="opacity-50">|</span>
            <a href="index.php?page=order-tracking"><i class="bi bi-truck me-1"></i>Tra Cứu Vận Đơn 3PL</a>
            <span class="opacity-50 d-none d-md-inline">|</span>
            <a href="index.php?page=shipping-policy" class="d-none d-md-inline"><i class="bi bi-shield-check me-1"></i>Chính Sách Giao Nhận</a>
        </div>
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <a href="index.php?page=privacy-policy" class="d-none d-sm-inline"><i class="bi bi-shield-lock me-1"></i>Bảo Mật Thông Tin</a>
            <span class="opacity-50 d-none d-sm-inline">|</span>
            <a href="index.php?page=return-policy"><i class="bi bi-question-circle me-1"></i>Trợ Giúp 24/7</a>
            <span class="opacity-50">|</span>
            <span><i class="bi bi-globe me-1"></i>Tiếng Việt</span>
        </div>
    </div>
</div>

<!-- THANH ĐIỀU HƯỚNG CHÍNH -->
<nav class="navbar navbar-expand-lg navbar-main sticky-top py-2">
    <div class="container-fluid px-3 px-xl-4 px-xxl-5 header-container d-flex align-items-center justify-content-between">
        <a class="brand-logo me-2 me-xl-4 text-decoration-none flex-shrink-0" href="index.php">
            <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm brand-icon-box" style="width: 40px; height: 40px;">
                <i class="bi bi-bag-check-fill fs-5"></i>
            </div>
            <div>
                <div class="fw-extrabold fs-5 lh-1 text-dark">Integrity<span class="text-primary">Market</span></div>
                <div class="brand-sub d-none d-xl-block" style="font-size: 11px; color: #64748b; font-weight: 600; margin-top: 2px;">Thế Giới Công Nghệ & Sáng Tạo DIY</div>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <!-- Ô TÌM KIẾM SẢN PHẨM FLUID SCALING -->
            <form class="d-flex my-2 my-lg-0 header-search-form" action="index.php" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-2"><i class="bi bi-search"></i></span>
                    <input class="form-control bg-light border-start-0 ps-1 header-search-input" type="search" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Tìm kiếm" autocomplete="off">
                    <button class="btn btn-primary px-2 px-xl-3 fw-semibold text-nowrap" type="submit">Tìm kiếm</button>
                </div>
            </form>

            <ul class="navbar-nav align-items-center gap-1 gap-xl-2 ms-auto mb-2 mb-lg-0 flex-nowrap">
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-nowrap nav-link-scale <?= empty($_GET['type']) && empty($_GET['cat']) ? 'text-primary' : '' ?>" href="index.php">
                        <i class="bi bi-grid me-1"></i>Tất cả
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-danger text-nowrap nav-link-scale" href="index.php?type=DIY">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1 badge-hot-nav">HOT</span>Đồ DIY<span class="d-none d-xxl-inline"> Sáng Tạo</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-info-emphasis text-nowrap nav-link-scale" href="index.php?type=TECH">
                        <i class="bi bi-cpu me-1"></i>Công Nghệ
                    </a>
                </li>

                <!-- TRA CỨU ĐƠN HÀNG (DÀNH CHO MỌI KHÁCH HÀNG KỂ CẢ KHÁCH VÃNG LAI) -->
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-dark text-nowrap nav-link-scale <?= (($_GET['page'] ?? '') === 'order-tracking') ? 'text-primary' : '' ?>" href="index.php?page=order-tracking">
                        <i class="bi bi-truck me-1 text-primary"></i>Tra Cứu Đơn
                    </a>
                </li>

                <!-- GIỎ HÀNG -->
                <li class="nav-item position-relative ms-1 flex-shrink-0">
                    <a class="btn btn-light btn-sm p-2 rounded-circle border d-flex align-items-center justify-content-center btn-cart-nav" style="width: 38px; height: 38px;" href="index.php?page=cart" title="Giỏ hàng">
                        <i class="bi bi-cart3 fs-5 text-dark"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 11px;">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- KHU VỰC TÀI KHOẢN -->
                <?php if ($currentUser): ?>
                    <li class="nav-item dropdown ms-1 ms-xl-2 flex-shrink-0">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 gap-xl-2 py-1 px-2" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 13px;">
                                <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                            </div>
                            <span class="fw-semibold text-dark fs-7 d-none d-sm-inline"><?= htmlspecialchars($currentUser['username']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li class="dropdown-header">Tài khoản: <span class="badge bg-primary"><?= $currentUser['role'] ?></span></li>
                            <li><a class="dropdown-item" href="index.php?page=my-orders"><i class="bi bi-box-seam me-2"></i>Đơn mua của tôi</a></li>
                            <?php if (!empty($currentUser['shop'])): ?>
                                <li><a class="dropdown-item text-primary fw-bold" href="index.php?page=seller"><i class="bi bi-speedometer2 me-2"></i>Kênh Người Bán (<?= htmlspecialchars($currentUser['shop']['shop_name']) ?>)</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item text-success fw-bold" href="index.php?page=seller-register"><i class="bi bi-shop-window me-2"></i>Mở Shop Bán Hàng</a></li>
                            <?php endif; ?>
                            <?php if ($currentUser['role'] === 'ADMIN'): ?>
                                <li><a class="dropdown-item text-danger fw-bold" href="index.php?page=admin"><i class="bi bi-gear-fill me-2"></i>Sàn Quản Trị (Admin Panel)</a></li>
                                <li><a class="dropdown-item" href="index.php?page=admin-security"><i class="bi bi-people-fill me-2"></i>Kiểm Soát Tài Khoản & Giao Dịch</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?page=logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item d-flex align-items-center gap-1 gap-xl-2 ms-1 ms-xl-2 flex-shrink-0 auth-btn-group">
                        <a class="btn btn-outline-secondary btn-sm px-2 px-xl-3 fw-semibold btn-auth" href="index.php?page=register">
                            Đăng ký
                        </a>
                        <a class="btn btn-primary btn-sm px-2 px-xl-3 fw-bold shadow-sm btn-auth" href="index.php?page=login">
                            Đăng nhập
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content">
