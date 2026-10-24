<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Database;

final class AccountController {
    public function account(): void { require_login(); render('account/index',['title'=>'Your account']); }
    public function orders(): void { require_login(); $s=Database::connection()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC'); $s->execute([current_user()['id']]); render('account/orders',['title'=>'Your orders','orders'=>$s->fetchAll()]); }
    public function order(): void {
        require_login(); $s=Database::connection()->prepare('SELECT * FROM orders WHERE id=? AND user_id=?'); $s->execute([(int)($_GET['id']??0),current_user()['id']]); $order=$s->fetch();
        if(!$order) { http_response_code(404); render('errors/404',['title'=>'Order not found']); return; }
        $i=Database::connection()->prepare('SELECT * FROM order_items WHERE order_id=?'); $i->execute([$order['id']]);
        render('account/order',['title'=>'Order '.$order['order_number'],'order'=>$order,'items'=>$i->fetchAll()]);
    }
}
