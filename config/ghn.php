<?php
/**
 * CẤU HÌNH KẾT NỐI API ĐỐI TÁC VẬN CHUYỂN 3PL GIAO HÀNG NHANH (GHN)
 * Tài liệu chuẩn: https://developer.ghn.dev/vi/docs/getting-started/overview
 */
return [
    // Bật true = Môi trường thử nghiệm Staging (https://dev-online-gateway.ghn.vn)
    // Bật false = Môi trường vận hành thực tế Production (https://online-gateway.ghn.vn)
    'sandbox'         => false,
    'dev_api_url'     => 'https://dev-online-gateway.ghn.vn/shiip/public-api/',
    'prod_api_url'    => 'https://online-gateway.ghn.vn/shiip/public-api/',
    'client_id'       => 5573761,
    'shop_id'         => 6723370, // Tài khoản GHN chính thức của bạn (0836701999)
    'token'           => 'f172c112-78d7-4a3e-b3c7-ef230f0814d1',
    'origin_province' => 'Hồ Chí Minh',
    'origin_district' => 'Quận 1',
    'from_district_id'=> 1442,
    'from_ward_code'  => '20308',
    'default_fee'     => 28000
];
