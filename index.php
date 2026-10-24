<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Router;
use App\Controllers\StoreController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\AccountController;
use App\Controllers\CheckoutController;
use App\Controllers\AdminController;

$router = new Router();

$router->get('/', [StoreController::class, 'home']);
$router->get('/products', [StoreController::class, 'products']);
$router->get('/product', [StoreController::class, 'product']);
$router->post('/review', [StoreController::class, 'review']);

$router->get('/sign-in', [AuthController::class, 'loginForm']);
$router->post('/sign-in', [AuthController::class, 'login']);
$router->get('/sign-up', [AuthController::class, 'registerForm']);
$router->post('/sign-up', [AuthController::class, 'register']);
$router->post('/sign-out', [AuthController::class, 'logout']);

$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->get('/wishlist', [CartController::class, 'wishlist']);
$router->post('/wishlist/toggle', [CartController::class, 'toggleWishlist']);

$router->get('/checkout', [CheckoutController::class, 'index']);
$router->post('/checkout', [CheckoutController::class, 'placeOrder']);
$router->get('/orders', [AccountController::class, 'orders']);
$router->get('/order', [AccountController::class, 'order']);
$router->get('/account', [AccountController::class, 'account']);

$router->get('/admin', [AdminController::class, 'index']);
$router->post('/admin/product/save', [AdminController::class, 'saveProduct']);
$router->post('/admin/product/delete', [AdminController::class, 'deleteProduct']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
