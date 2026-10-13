<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;
use App\Services\AuthService;

class HomeController {
    public function index() {
        AuthService::startSession();
        $currentUser = AuthService::user();

        $catId = !empty($_GET['cat']) ? (int)$_GET['cat'] : null;
        $type = !empty($_GET['type']) ? trim($_GET['type']) : null; // 'TECH' or 'DIY'
        $search = !empty($_GET['q']) ? trim($_GET['q']) : null;

        $categories = Category::getAll();
        $products = Product::getAll($catId, $type, $search);
        $shops = Shop::getAll();

        require __DIR__ . '/../../views/buyer/home.php';
    }

    public function privacyPolicy() {
        AuthService::startSession();
        $currentUser = AuthService::user();
        require __DIR__ . '/../../views/pages/privacy_policy.php';
    }

    public function terms() {
        AuthService::startSession();
        $currentUser = AuthService::user();
        require __DIR__ . '/../../views/pages/terms.php';
    }

    public function shippingPolicy() {
        AuthService::startSession();
        $currentUser = AuthService::user();
        require __DIR__ . '/../../views/pages/shipping_policy.php';
    }

    public function returnPolicy() {
        AuthService::startSession();
        $currentUser = AuthService::user();
        require __DIR__ . '/../../views/pages/return_policy.php';
    }
}
