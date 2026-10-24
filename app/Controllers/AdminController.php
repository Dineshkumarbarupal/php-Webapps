<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Database;
use App\Repositories\ProductRepository;

final class AdminController {
    public function index(): void { require_admin(); render('admin/index',['title'=>'Store admin','products'=>(new ProductRepository())->listing(['sort'=>'newest'])]); }
    public function saveProduct(): void {
        require_admin(); verify_csrf(); $db=Database::connection();
        $id=(int)($_POST['id']??0); $name=trim((string)($_POST['name']??'')); $category=trim((string)($_POST['category']??'')); $desc=trim((string)($_POST['description']??''));
        $price=(float)($_POST['price']??0); $stock=max(0,(int)($_POST['stock']??0)); $image=trim((string)($_POST['image_url']??''));
        if($name===''||$category===''||$desc===''||$price<=0||!filter_var($image,FILTER_VALIDATE_URL)) { flash('error','Complete all product fields with a valid image URL and price.'); redirect('admin'); }
        $slug=strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$name),'-')).'-'.substr(bin2hex(random_bytes(3)),0,6);
        if($id) $db->prepare('UPDATE products SET name=?,category=?,description=?,price=?,stock=?,image_url=? WHERE id=?')->execute([$name,$category,$desc,$price,$stock,$image,$id]);
        else $db->prepare('INSERT INTO products (slug,name,category,description,price,stock,image_url) VALUES (?,?,?,?,?,?,?)')->execute([$slug,$name,$category,$desc,$price,$stock,$image]);
        flash('success','Product saved.'); redirect('admin');
    }
    public function deleteProduct(): void { require_admin(); verify_csrf(); $id=(int)($_POST['id']??0); Database::connection()->prepare('UPDATE products SET active=0 WHERE id=?')->execute([$id]); flash('success','Product archived.'); redirect('admin'); }
}
