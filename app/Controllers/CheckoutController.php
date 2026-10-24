<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Database;

final class CheckoutController {
    public function index(): void { require_login(); $items=cart_items(); if(!$items) { flash('error','Your cart is empty.'); redirect('products'); } render('checkout/index',['title'=>'Checkout','items'=>$items,'subtotal'=>cart_total($items)]); }
    public function placeOrder(): void {
        require_login(); verify_csrf(); $name=trim((string)($_POST['shipping_name']??'')); $email=trim((string)($_POST['shipping_email']??'')); $address=trim((string)($_POST['shipping_address']??'')); $items=cart_items();
        if(!$items || $name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || mb_strlen($address)<8) { flash('error','Please complete the shipping form.'); redirect('checkout'); }
        $db=Database::connection(); $subtotal=cart_total($items); $shipping=$subtotal>=75?0:5.99; $tax=round($subtotal*0.08,2); $total=round($subtotal+$shipping+$tax,2);
        try { $db->beginTransaction(); foreach($items as $item) { $check=$db->prepare('SELECT stock FROM products WHERE id=?'); $check->execute([$item['id']]); $stock=(int)$check->fetchColumn(); if($stock<(int)$item['quantity']) throw new \RuntimeException('Not enough stock for '.$item['name']); }
            $number='SS-'.strtoupper(bin2hex(random_bytes(4))); $s=$db->prepare('INSERT INTO orders (user_id,order_number,subtotal,shipping,tax,total,shipping_name,shipping_email,shipping_address) VALUES (?,?,?,?,?,?,?,?,?)'); $s->execute([current_user()['id'],$number,$subtotal,$shipping,$tax,$total,$name,$email,$address]); $orderId=(int)$db->lastInsertId();
            foreach($items as $item) { $db->prepare('INSERT INTO order_items (order_id,product_id,product_name,unit_price,quantity,image_url) VALUES (?,?,?,?,?,?)')->execute([$orderId,$item['id'],$item['name'],$item['price'],$item['quantity'],$item['image_url']]); $db->prepare('UPDATE products SET stock=stock-? WHERE id=?')->execute([$item['quantity'],$item['id']]); }
            $db->prepare('DELETE FROM cart_items WHERE user_id=?')->execute([current_user()['id']]); unset($_SESSION['cart']); $db->commit(); flash('success','Order placed in demo mode. No payment was collected.'); redirect('order?id='.$orderId);
        } catch (\Throwable $e) { if($db->inTransaction()) $db->rollBack(); flash('error',$e instanceof \RuntimeException ? $e->getMessage() : 'Could not place your order. Please try again.'); redirect('checkout'); }
    }
}
