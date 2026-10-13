<?php
namespace App\Controllers;

use App\Models\Product;
use App\Services\AuthService;

class ProductController {
    public function detail() {
        AuthService::startSession();
        $currentUser = AuthService::user();

        $id = $_GET['id'] ?? '';
        $product = Product::getById($id);

        if (!$product) {
            header('Location: index.php');
            exit;
        }

        // Giải mã JSON thuộc tính nếu có
        $attributes = !empty($product['attributes_json']) ? json_decode($product['attributes_json'], true) : [];

        require __DIR__ . '/../../views/buyer/product_detail.php';
    }
}
