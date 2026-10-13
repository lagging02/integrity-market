<?php
namespace App\Controllers;

use App\Models\Product;
use App\Services\AuthService;

class CartController {
    public function view() {
        AuthService::startSession();
        $currentUser = AuthService::user();
        $cart = $_SESSION['cart'] ?? [];

        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        require __DIR__ . '/../../views/buyer/cart.php';
    }

    public function add() {
        AuthService::startSession();
        $productId = $_POST['product_id'] ?? $_GET['id'] ?? '';
        $qty = max(1, (int)($_POST['quantity'] ?? $_GET['qty'] ?? 1));
        $variant = $_POST['variant'] ?? $_GET['variant'] ?? null;

        $product = Product::getById($productId);
        if ($product) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            $cartKey = $productId . ($variant ? '_' . md5($variant) : '');

            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['quantity'] += $qty;
            } else {
                $_SESSION['cart'][$cartKey] = [
                    'product_id'   => $product['id'],
                    'shop_id'      => $product['shop_id'],
                    'shop_name'    => $product['shop_name'],
                    'name'         => $product['name'],
                    'price'        => $product['price'],
                    'image'        => $product['image'],
                    'is_diy'       => $product['is_diy'],
                    'quantity'     => $qty,
                    'variant_info' => $variant
                ];
            }
        }

        if (isset($_GET['buy_now'])) {
            header('Location: index.php?page=checkout');
        } else {
            header('Location: index.php?page=cart&msg=added');
        }
        exit;
    }

    public function remove() {
        AuthService::startSession();
        $key = $_GET['key'] ?? '';
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
        header('Location: index.php?page=cart');
        exit;
    }

    public function clear() {
        AuthService::startSession();
        $_SESSION['cart'] = [];
        header('Location: index.php?page=cart');
        exit;
    }
}
