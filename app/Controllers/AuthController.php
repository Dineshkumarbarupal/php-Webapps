<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Database;

final class AuthController {
    public function loginForm(): void { if(is_logged_in()) redirect('account'); render('auth/login',['title'=>'Sign in']); }
    public function registerForm(): void { if(is_logged_in()) redirect('account'); render('auth/register',['title'=>'Create account']); }
    public function register(): void {
        verify_csrf(); $name=trim((string)($_POST['name']??'')); $email=strtolower(trim((string)($_POST['email']??''))); $password=(string)($_POST['password']??'');
        if (mb_strlen($name)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<10) { flash('error','Enter a name, valid email, and password with at least 10 characters.'); redirect('sign-up'); }
        try { $s=Database::connection()->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)'); $s->execute([mb_substr($name,0,100),$email,password_hash($password,PASSWORD_DEFAULT)]); $id=(int)Database::connection()->lastInsertId(); session_regenerate_id(true); $_SESSION['user']=['id'=>$id,'name'=>$name,'email'=>$email,'role'=>'customer']; flash('success','Welcome! Your account is ready.'); redirect('account'); }
        catch (\PDOException $e) { flash('error','That email may already be registered. Try signing in.'); redirect('sign-up'); }
    }
    public function login(): void {
        verify_csrf(); $email=strtolower(trim((string)($_POST['email']??''))); $password=(string)($_POST['password']??'');
        $s=Database::connection()->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email=?'); $s->execute([$email]); $user=$s->fetch();
        if (!$user || !password_verify($password,$user['password_hash'])) { usleep(250000); flash('error','Email or password is incorrect.'); redirect('sign-in'); }
        session_regenerate_id(true); $_SESSION['user']=['id'=>(int)$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
        $intended=$_SESSION['intended']??'account'; unset($_SESSION['intended']);
        foreach (($_SESSION['cart']??[]) as $pid=>$qty) { $s=Database::connection()->prepare('INSERT INTO cart_items (user_id,product_id,quantity) VALUES (?,?,?) ON CONFLICT(user_id,product_id) DO UPDATE SET quantity=MIN(cart_items.quantity+excluded.quantity,99)'); $s->execute([$user['id'],(int)$pid,(int)$qty]); }
        unset($_SESSION['cart']); flash('success','You are signed in.'); redirect($intended);
    }
    public function logout(): void { verify_csrf(); $_SESSION=[]; if(ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain']??'',$p['secure'],$p['httponly']); } session_destroy(); redirect(''); }
}
