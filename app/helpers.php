<?php
declare(strict_types=1);

use App\Database;

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = ''): string { return '/' . ltrim($path, '/'); }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return current_user() !== null; }
function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }
function require_login(): void { if (!is_logged_in()) { $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/account'; flash('error', 'Please sign in to continue.'); redirect('sign-in'); } }
function require_admin(): void { require_login(); if (!is_admin()) { http_response_code(403); render('errors/403', ['title' => 'Access denied']); exit; } }
function csrf_token(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void { $sent = $_POST['_csrf'] ?? ''; if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) { http_response_code(419); render('errors/419', ['title' => 'Session expired']); exit; } }
function flash(string $key, ?string $message = null): ?string { if ($message !== null) { $_SESSION['_flash'][$key] = $message; return null; } $value = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $value; }
function render(string $view, array $data = []): void { extract($data, EXTR_SKIP); $viewFile = VIEW_PATH . '/' . $view . '.php'; if (!is_file($viewFile)) { http_response_code(500); echo 'View not found'; return; } require VIEW_PATH . '/layouts/main.php'; }
function money(float|int|string $amount): string { return '$' . number_format((float)$amount, 2); }
function old(string $key, string $default = ''): string { return e($_POST[$key] ?? $default); }
function cart_count(): int { $cart = $_SESSION['cart'] ?? []; if (is_logged_in()) { $stmt = Database::connection()->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id = ?'); $stmt->execute([current_user()['id']]); return (int)$stmt->fetchColumn(); } return array_sum(array_map('intval', $cart)); }
function cart_items(): array { $db = Database::connection(); if (is_logged_in()) { $stmt = $db->prepare('SELECT p.*, ci.quantity FROM cart_items ci JOIN products p ON p.id=ci.product_id WHERE ci.user_id=? ORDER BY ci.created_at DESC'); $stmt->execute([current_user()['id']]); return $stmt->fetchAll(); } $items = []; foreach (($_SESSION['cart'] ?? []) as $id => $qty) { $stmt = $db->prepare('SELECT * FROM products WHERE id=? AND active=1'); $stmt->execute([(int)$id]); $p = $stmt->fetch(); if ($p) { $p['quantity'] = (int)$qty; $items[] = $p; } } return $items; }
function cart_total(array $items): float { return array_reduce($items, fn($sum, $item) => $sum + ((float)$item['price'] * (int)$item['quantity']), 0.0); }
function product_image(array $product): string { return $product['image_url'] ?: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80'; }
