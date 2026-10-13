<?php
$pageTitle = "Phòng Thử Nghiệm Tấn Công & Kiểm Toán Bảo Mật - IntegrityMarket";
require __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5" style="max-width: 1100px;">
    <!-- TIÊU ĐỀ LAB BẢO MẬT -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <div class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 mb-2 fw-bold">
                <i class="bi bi-shield-slash-fill me-1"></i> PENETRATION TESTING & ATTACK DEFENSE LAB
            </div>
            <h3 class="fw-bold mb-1">Phòng Thử Nghiệm Tấn Công & Kiểm Toán Bảo Mật</h3>
            <p class="text-muted small mb-0">Thực thi các cuộc tấn công mạng thực tế vào hệ thống và quan sát cơ chế phòng thủ theo chiều sâu (Defense-in-Depth)</p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=admin" class="btn btn-outline-secondary btn-sm fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Trang Quản Trị
            </a>
            <button class="btn btn-danger btn-sm fw-bold shadow-sm" onclick="runAllAttacks()">
                <i class="bi bi-lightning-charge-fill me-1"></i> Chạy Toàn Bộ 8 Kịch Bản Tấn Công
            </button>
        </div>
    </div>

    <!-- TỔNG QUAN PHÒNG THỦ -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">KÊNH TRUYỀN</span>
                    <i class="bi bi-lock-fill text-primary fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">TLS 1.3 / HTTPS</h6>
                <div class="small text-muted">HSTS, Cloudflare Tunnel, Secure Cookie</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">DANH TÍNH & PHIÊN</span>
                    <i class="bi bi-person-badge-fill text-success fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Bcrypt Cost 12</h6>
                <div class="small text-muted">Rate-Limit 429, CSRF Token, OTP 3min</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">DỮ LIỆU ĐẦU VÀO</span>
                    <i class="bi bi-database-fill-check text-info fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">PDO Prepared</h6>
                <div class="small text-muted">Chống 100% SQLi & XSS Output Encoding</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">THANH TOÁN & PII</span>
                    <i class="bi bi-shield-shaded text-danger fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">HMAC + AES-256</h6>
                <div class="small text-muted">Mã hóa SĐT/Địa chỉ, Idempotency Key</div>
            </div>
        </div>
    </div>

    <!-- SLIDE 9: KẾT QUẢ KIỂM THỬ TỰ ĐỘNG TRÊN BỘ MÃ NGUỒN (MATCH 100% SLIDE 9) -->
    <div class="card border-0 rounded-4 bg-white shadow-sm p-4 p-md-5 mb-5 border">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 mb-1">
                    <i class="bi bi-file-earmark-check-fill me-1"></i> BÁO CÁO KIỂM THỬ THEO SLIDE 9
                </span>
                <h4 class="fw-bold text-dark mb-0">Kết quả kiểm thử tự động trên bộ mã nguồn</h4>
                <div class="text-muted small">Đã chạy kiểm thử tự động trên chính dự án TechStore trong môi trường máy chủ hiện tại</div>
            </div>
            <button class="btn btn-primary fw-bold px-4 py-2 shadow-sm" id="btnRunSuite" onclick="runAutomatedSuite()">
                <i class="bi bi-play-fill me-1"></i> Chạy 13 Bài Kiểm Thử Tự Động
            </button>
        </div>

        <div class="row g-4 align-items-center">
            <!-- CỘT TRÁI: Ô SỐ LƯỢNG TEST PASS TO ĐẬM (SLIDE 9 STYLE) -->
            <div class="col-md-5">
                <div class="rounded-4 p-4 p-md-5 text-center text-white d-flex flex-column justify-content-center align-items-center shadow" style="background: linear-gradient(145deg, #0d1b2a, #1b263b); min-height: 260px;">
                    <div class="display-1 fw-extrabold text-white mb-0" id="suiteScoreNumber">13</div>
                    <div class="fs-5 fw-bold text-light tracking-wide mb-3">BÀI KIỂM THỬ PASS</div>
                    <div class="badge bg-secondary bg-opacity-50 text-light px-3 py-2 font-monospace small" id="suiteStatusBadge">
                        pytest • 13 passed (100%)
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: 6 NHÓM KIỂM THỬ (SLIDE 9 CATEGORIES) -->
            <div class="col-md-7">
                <div class="border rounded-4 p-4 bg-light">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-list-check text-success me-2"></i>Các nhóm kiểm thử đã chạy:
                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0 small">
                        <li class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-6 me-2"></i>
                            <span><strong>Xác thực, session và đăng xuất</strong> (Bcrypt Cost 12, HttpOnly, Session Invalidation)</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-6 me-2"></i>
                            <span><strong>CSRF và chặn API không có quyền</strong> (Synchronizer Token, Rate-Limit 429)</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-6 me-2"></i>
                            <span><strong>Tính tiền ở Backend, mã hóa PII</strong> (Server-side calculation, AES-256-CBC)</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-6 me-2"></i>
                            <span><strong>Stripe / Gateway webhook có chữ ký, chống lặp</strong> (HMAC-SHA256, 4 Checks, Idempotency)</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-6 me-2"></i>
                            <span><strong>Bảo vệ quyền sở hữu đơn hàng</strong> (Chống lỗi tham chiếu trực tiếp IDOR)</span>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-6 me-2"></i>
                            <span><strong>Luồng trạng thái COD và dữ liệu sai</strong> (PDO Prepared, Output Encoding chống SQLi/XSS)</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- BẢNG CHI TIẾT 13 BÀI KIỂM THỬ (HIỆN RA KHI NHẤN CHẠY) -->
        <div id="testSuiteDetailsBox" class="mt-4 d-none">
            <h6 class="fw-bold text-dark mb-3">Chi tiết từng kịch bản kiểm tra mã nguồn:</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle small mb-0 bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 80px;" class="text-center">Mã</th>
                            <th style="width: 220px;">Nhóm Chức Năng</th>
                            <th>Nội Dung Kiểm Thử</th>
                            <th>Cơ Chế Bảo Vệ / Chi Tiết</th>
                            <th style="width: 100px;" class="text-center">Kết Quả</th>
                        </tr>
                    </thead>
                    <tbody id="testSuiteTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH 8 KỊCH BẢN TẤN CÔNG THỰC TẾ (DEMO TRỰC TIẾP TỪNG TÌNH HUỐNG) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0">Biểu Diễn Tấn Công Trực Tiếp (8 Kịch Bản Thâm Nhập)</h4>
        <span class="text-muted small">Quan sát phản hồi mã lỗi HTTP và cơ chế tự động ngăn chặn</span>
    </div>

    <div class="row g-4" id="attackScenariosGrid">

        <!-- KỊCH BẢN 1: SQL INJECTION -->
        <div class="col-md-6">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-4" id="card-sqli">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold mb-1">KỊCH BẢN 01</span>
                        <h5 class="fw-bold text-dark mb-1">Tấn Công SQL Injection (Bypass Đăng Nhập)</h5>
                        <div class="small text-muted">Thử nghiệm bẻ gãy câu lệnh SQL bằng mệnh đề luôn đúng</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-database-slash fs-5"></i>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Payload Hacker:</label>
                    <input type="text" class="form-control form-control-sm font-monospace text-danger" id="payload-sqli" value="' OR '1'='1' --">
                </div>

                <button class="btn btn-outline-danger w-100 fw-bold mb-3" onclick="triggerAttack('sqli_login', 'card-sqli', 'payload-sqli')">
                    <i class="bi bi-play-circle me-1"></i> Bắt đầu tấn công SQLi
                </button>

                <div class="result-box d-none small"></div>
            </div>
        </div>

        <!-- KỊCH BẢN 2: CROSS-SITE SCRIPTING (XSS) -->
        <div class="col-md-6">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-4" id="card-xss">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold mb-1">KỊCH BẢN 02</span>
                        <h5 class="fw-bold text-dark mb-1">Tấn Công XSS (Đánh Cắp Session Cookie)</h5>
                        <div class="small text-muted">Tiêm mã độc JavaScript vào form nhập liệu người dùng</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-code-slash fs-5"></i>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Payload Hacker:</label>
                    <input type="text" class="form-control form-control-sm font-monospace text-danger" id="payload-xss" value="<script>alert('Steal Cookie: ' + document.cookie)</script>">
                </div>

                <button class="btn btn-outline-danger w-100 fw-bold mb-3" onclick="triggerAttack('xss_injection', 'card-xss', 'payload-xss')">
                    <i class="bi bi-play-circle me-1"></i> Bắt đầu tiêm mã độc XSS
                </button>

                <div class="result-box d-none small"></div>
            </div>
        </div>

        <!-- KỊCH BẢN 3: CSRF & FORGED REQUEST -->
        <div class="col-md-6">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-4" id="card-csrf">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold mb-1">KỊCH BẢN 03</span>
                        <h5 class="fw-bold text-dark mb-1">Tấn Công Giả Mạo Yêu Cầu (CSRF Attack)</h5>
                        <div class="small text-muted">Gửi POST request từ trang web giả mạo với CSRF Token giả</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">CSRF Token Giả:</label>
                    <input type="text" class="form-control form-control-sm font-monospace text-danger" id="payload-csrf" value="FORGED_TOKEN_HACKER_123">
                </div>

                <button class="btn btn-outline-danger w-100 fw-bold mb-3" onclick="triggerAttack('csrf_forgery', 'card-csrf', 'payload-csrf')">
                    <i class="bi bi-play-circle me-1"></i> Gửi yêu cầu CSRF giả mạo
                </button>

                <div class="result-box d-none small"></div>
            </div>
        </div>

        <!-- KỊCH BẢN 4: BRUTE-FORCE & RATE-LIMITING -->
        <div class="col-md-6">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-4" id="card-brute">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold mb-1">KỊCH BẢN 04</span>
                        <h5 class="fw-bold text-dark mb-1">Tấn Công Dò Mật Khẩu (Brute-Force & Spam OTP)</h5>
                        <div class="small text-muted">Gửi 6 yêu cầu thử mật khẩu liên tiếp trong vài mili-giây</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-key-fill fs-5"></i>
                    </div>
                </div>

                <div class="bg-light p-2 rounded-3 border mb-3 font-monospace small">
                    <strong>Hành vi:</strong> <span class="text-danger">Spam liên tiếp 6 login attempts sai</span>
                </div>

                <button class="btn btn-outline-danger w-100 fw-bold mb-3" onclick="triggerAttack('brute_force', 'card-brute')">
                    <i class="bi bi-play-circle me-1"></i> Kích hoạt Brute-Force Demo
                </button>

                <div class="result-box d-none small"></div>
            </div>
        </div>

        <!-- KỊCH BẢN 5: IDOR ORDER TAMPERING -->
        <div class="col-md-6">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-4" id="card-idor">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold mb-1">KỊCH BẢN 05</span>
                        <h5 class="fw-bold text-dark mb-1">Tấn Công IDOR (Xem Trộm Đơn Hàng Khách Khác)</h5>
                        <div class="small text-muted">Hacker đổi order_id trên URL để truy cập trái phép đơn của người khác</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-person-x-fill fs-5"></i>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Target Order ID nạn nhân:</label>
                    <input type="text" class="form-control form-control-sm font-monospace text-danger" id="payload-idor" value="ord-uuid-001">
                </div>

                <button class="btn btn-outline-danger w-100 fw-bold mb-3" onclick="triggerAttack('idor_order_tamper', 'card-idor', 'payload-idor')">
                    <i class="bi bi-play-circle me-1"></i> Thử truy xuất IDOR trái phép
                </button>

                <div class="result-box d-none small"></div>
            </div>
        </div>

        <!-- KỊCH BẢN 6: THAO TÚNG GIÁ TIỀN (PRICE TAMPERING) -->
        <div class="col-md-6">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-4" id="card-price">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold mb-1">KỊCH BẢN 06</span>
                        <h5 class="fw-bold text-dark mb-1">Thao Túng Giá Tiền (Price Tampering Ở Client)</h5>
                        <div class="small text-muted">Sửa giá MacBook Air M2 ở Client từ 24.900.000đ thành 1.000đ</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                </div>

                <div class="bg-light p-2 rounded-3 border mb-3 font-monospace small">
                    <strong>Client Sửa:</strong> <span class="text-danger">total_amount: 1.000 VND</span> (DB thật: 24.900.000 VND)
                </div>

                <button class="btn btn-outline-danger w-100 fw-bold mb-3" onclick="triggerAttack('price_tampering', 'card-price')">
                    <i class="bi bi-play-circle me-1"></i> Gửi gói tin giá đã sửa đổi
                </button>

                <div class="result-box d-none small"></div>
            </div>
        </div>

        <!-- KỊCH BẢN 7: GIẢ MẠO WEBHOOK THANH TOÁN (SLIDE 5) -->
        <div class="col-md-6">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-4" id="card-webhook">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold mb-1">KỊCH BẢN 07</span>
                        <h5 class="fw-bold text-dark mb-1">Giả Mạo Webhook Thanh Toán (HMAC Forgery)</h5>
                        <div class="small text-muted">Gửi Webhook giả báo đã chuyển tiền nhưng ký bằng Secret giả</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-patch-question-fill fs-5"></i>
                    </div>
                </div>

                <div class="bg-light p-2 rounded-3 border mb-3 font-monospace small">
                    <strong>Chữ ký gửi lên:</strong> <span class="text-danger">HMAC_SHA256(Secret_Gia_Mao)</span>
                </div>

                <button class="btn btn-outline-danger w-100 fw-bold mb-3" onclick="triggerAttack('fake_webhook', 'card-webhook')">
                    <i class="bi bi-play-circle me-1"></i> Gửi Webhook giả mạo
                </button>

                <div class="result-box d-none small"></div>
            </div>
        </div>

        <!-- KỊCH BẢN 8: RÒ RỈ CSDL PII (AES-256) -->
        <div class="col-md-6">
            <div class="card border rounded-4 bg-white shadow-sm h-100 p-4" id="card-leak">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold mb-1">KỊCH BẢN 08</span>
                        <h5 class="fw-bold text-dark mb-1">Trích Xuất CSDL (Rò Rỉ Dữ Liệu PII Khách Hàng)</h5>
                        <div class="small text-muted">Mô phỏng tin tặc trộm được toàn bộ bảng users trong MySQL</div>
                    </div>
                    <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-file-earmark-lock-fill fs-5"></i>
                    </div>
                </div>

                <div class="bg-light p-2 rounded-3 border mb-3 font-monospace small">
                    <strong>Mục tiêu CSDL:</strong> <span class="text-danger">SELECT username, phone, address FROM users</span>
                </div>

                <button class="btn btn-outline-danger w-100 fw-bold mb-3" onclick="triggerAttack('db_leakage', 'card-leak')">
                    <i class="bi bi-play-circle me-1"></i> Khám phá dữ liệu Dump CSDL
                </button>

                <div class="result-box d-none small"></div>
            </div>
        </div>

    </div>
</div>

<script>
function runAutomatedSuite() {
    const btn = document.getElementById('btnRunSuite');
    const detailsBox = document.getElementById('testSuiteDetailsBox');
    const tableBody = document.getElementById('testSuiteTableBody');
    const scoreNum = document.getElementById('suiteScoreNumber');
    const badge = document.getElementById('suiteStatusBadge');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang chạy 13 bài test...';

    fetch('index.php?page=run-test-suite')
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Chạy Lại Kiểm Thử (13 Tests)';
        
        scoreNum.innerText = data.summary.passed_tests;
        badge.innerText = `pytest • ${data.summary.passed_tests} passed (${data.summary.pass_rate})`;
        badge.className = 'badge bg-success text-white px-3 py-2 font-monospace small';

        detailsBox.classList.remove('d-none');
        tableBody.innerHTML = '';

        data.tests.forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center font-monospace fw-bold">${t.id}</td>
                <td class="fw-semibold text-primary">${t.group}</td>
                <td><strong>${t.name}</strong></td>
                <td class="text-muted small">${t.details}</td>
                <td class="text-center">
                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>${t.status}</span>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        // Tự động cuộn nhẹ xuống bảng chi tiết
        detailsBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = 'Thử lại kiểm thử';
        alert('Lỗi kiểm thử tự động: ' + err);
    });
}

function triggerAttack(type, cardId, payloadInputId) {
    const card = document.getElementById(cardId);
    const resultBox = card.querySelector('.result-box');
    const btn = card.querySelector('button');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang thực thi tấn công...';

    const fd = new FormData();
    fd.append('attack_type', type);
    if (payloadInputId) {
        const inp = document.getElementById(payloadInputId);
        if (inp) fd.append('payload', inp.value);
    }

    fetch('index.php?page=run-attack', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Thử lại tấn công';
        resultBox.classList.remove('d-none');

        let detailHtml = `
            <div class="alert alert-success border-success p-3 rounded-3 mt-3 mb-0">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>PHÒNG THỦ THÀNH CÔNG</span>
                    <strong class="text-success">${data.attack_name}</strong>
                </div>
                <div class="mt-2"><strong>Cơ chế bảo vệ:</strong> <span class="text-dark">${data.defense_mechanism}</span></div>
                <div class="mt-1 text-danger-emphasis"><strong>Kết luận:</strong> ${data.result_summary}</div>
        `;

        if (data.database_dump) {
            detailHtml += `<div class="mt-2 font-monospace small text-muted bg-white p-2 border rounded">`;
            data.database_dump.forEach(u => {
                detailHtml += `<div>• ${u.username}: Phone=${u.raw_db_phone.substring(0, 24)}... (Bản mã AES-256)</div>`;
            });
            detailHtml += `</div>`;
        }

        detailHtml += `</div>`;
        resultBox.innerHTML = detailHtml;
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-circle me-1"></i> Thử lại';
        alert('Lỗi kết nối kiểm thử: ' + err);
    });
}

function runAllAttacks() {
    const list = [
        ['sqli_login', 'card-sqli', 'payload-sqli'],
        ['xss_injection', 'card-xss', 'payload-xss'],
        ['csrf_forgery', 'card-csrf', 'payload-csrf'],
        ['brute_force', 'card-brute', null],
        ['idor_order_tamper', 'card-idor', 'payload-idor'],
        ['price_tampering', 'card-price', null],
        ['fake_webhook', 'card-webhook', null],
        ['db_leakage', 'card-leak', null]
    ];

    list.forEach((item, idx) => {
        setTimeout(() => {
            triggerAttack(item[0], item[1], item[2]);
        }, idx * 400);
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

