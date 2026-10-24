<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Database;
use App\Repositories\ProductRepository;

final class CartController {
    public function index(): void { $items=cart_items(); render('cart/index',['title'=>'Your cart','items'=>$items,'total'=>cart_total($items)]); }
    public function add(): void {
        verify_csrf(); $id=(int)($_POST['product_id']??0); $qty=max(1,min(99,(int)($_POST['quantity']??1))); $product=(new ProductRepository())->find($id);
        if(!$product || (int)$product['stock']<1) { flash('error','This product is currently unavailable.'); redirect('cart'); }
        $qty=min($qty,(int)$product['stock']);
        if(is_logged_in()) { $s=Database::connection()->prepare('INSERT INTO cart_items (user_id,product_id,quantity) VALUES (?,?,?) ON CONFLICT(user_id,product_id) DO UPDATE SET quantity=MIN(cart_items.quantity+excluded.quantity,99)'); $s->execute([current_user()['id'],$id,$qty]); }
        else $_SESSION['cart'][$id]=min((int)($_SESSION['cart'][$id]??0)+$qty, (int)$product['stock'],99);
        flash('success','Added to your cart.'); redirect($_POST['return_to']??'cart');
    }
    public function update(): void { verify_csrf(); $id=(int)($_POST['product_id']??0); $qty=(int)($_POST['quantity']??1); $p=(new ProductRepository())->find($id); if(!$p) { flash('error','Product not found.'); redirect('cart'); } $qty=max(1,min(99,(int)$p['stock'],$qty)); if(is_logged_in()) Database::connection()->prepare('UPDATE cart_items SET quantity=? WHERE user_id=? AND product_id=?')->execute([$qty,current_user()['id'],$id]); else $_SESSION['cart'][$id]=$qty; redirect('cart'); }
    public function remove(): void { verify_csrf(); $id=(int)($_POST['product_id']??0); if(is_logged_in()) Database::connection()->prepare('DELETE FROM cart_items WHERE user_id=? AND product_id=?')->execute([current_user()['id'],$id]); else unset($_SESSION['cart'][$id]); flash('success','Item removed.'); redirect('cart'); }
    public function wishlist(): void { require_login(); $s=Database::connection()->prepare('SELECT p.* FROM wishlist_items w JOIN products p ON p.id=w.product_id WHERE w.user_id=? ORDER BY w.created_at DESC'); $s->execute([current_user()['id']]); render('cart/wishlist',['title'=>'Your wishlist','products'=>$s->fetchAll()]); }
    public function toggleWishlist(): void { require_login(); verify_csrf(); $id=(int)($_POST['product_id']??0); $db=Database::connection(); $s=$db->prepare('SELECT 1 FROM wishlist_items WHERE user_id=? AND product_id=?'); $s->execute([current_user()['id'],$id]); if($s->fetchColumn()) $db->prepare('DELETE FROM wishlist_items WHERE user_id=? AND product_id=?')->execute([current_user()['id'],$id]); else $db->prepare('INSERT OR IGNORE INTO wishlist_items (user_id,product_id) VALUES (?,?)')->execute([current_user()['id'],$id]); flash('success','Wishlist updated.'); redirect($_POST['return_to']??'wishlist'); }
}
