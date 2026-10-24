<?php
declare(strict_types=1);
namespace App;
use PDO;

final class Database {
    private static ?PDO $pdo = null;
    public static function connection(): PDO {
        if (self::$pdo) return self::$pdo;
        $dir = BASE_PATH . '/storage';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        self::$pdo = new PDO('sqlite:' . $dir . '/shop.sqlite', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        self::$pdo->exec('PRAGMA foreign_keys = ON');
        return self::$pdo;
    }
    public static function migrate(): void {
        $sql = [
            "CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, role TEXT NOT NULL DEFAULT 'customer', created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)",
            "CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT NOT NULL UNIQUE, name TEXT NOT NULL, category TEXT NOT NULL, description TEXT NOT NULL, price REAL NOT NULL, compare_at REAL, stock INTEGER NOT NULL DEFAULT 0, rating REAL NOT NULL DEFAULT 0, review_count INTEGER NOT NULL DEFAULT 0, image_url TEXT NOT NULL, badge TEXT, specs TEXT NOT NULL DEFAULT '[]', active INTEGER NOT NULL DEFAULT 1, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)",
            "CREATE TABLE IF NOT EXISTS cart_items (user_id INTEGER NOT NULL, product_id INTEGER NOT NULL, quantity INTEGER NOT NULL DEFAULT 1, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(user_id, product_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE)",
            "CREATE TABLE IF NOT EXISTS wishlist_items (user_id INTEGER NOT NULL, product_id INTEGER NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(user_id, product_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE)",
            "CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, order_number TEXT NOT NULL UNIQUE, status TEXT NOT NULL DEFAULT 'placed', subtotal REAL NOT NULL, shipping REAL NOT NULL DEFAULT 0, tax REAL NOT NULL DEFAULT 0, total REAL NOT NULL, shipping_name TEXT NOT NULL, shipping_email TEXT NOT NULL, shipping_address TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id))",
            "CREATE TABLE IF NOT EXISTS order_items (id INTEGER PRIMARY KEY AUTOINCREMENT, order_id INTEGER NOT NULL, product_id INTEGER, product_name TEXT NOT NULL, unit_price REAL NOT NULL, quantity INTEGER NOT NULL, image_url TEXT, FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE)",
            "CREATE TABLE IF NOT EXISTS reviews (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, product_id INTEGER NOT NULL, rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5), title TEXT NOT NULL, body TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE(user_id, product_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE)"
        ];
        foreach ($sql as $statement) self::connection()->exec($statement);
        self::connection()->exec('CREATE INDEX IF NOT EXISTS idx_products_category ON products(category)');
        self::connection()->exec('CREATE INDEX IF NOT EXISTS idx_products_active ON products(active)');
    }
    public static function seed(): void {
        $db = self::connection();
        if ((int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn() > 0) return;
        $products = [
            ['everyday-wireless-headphones','Everyday Wireless Headphones','Electronics','Comfortable wireless headphones with rich sound, a foldable design, and long battery life.',59.99,89.99,42,4.5,128,'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=900&q=85','Best seller',['Bluetooth 5.3','Up to 35 hours battery','Foldable design']],
            ['smart-watch-series','Smart Watch Series','Electronics','A versatile smartwatch with fitness tracking, notifications, and a bright touch display.',89.00,119.00,25,4.3,86,'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=900&q=85','Deal',['Heart-rate tracking','Water resistant','7-day battery']],
            ['minimal-backpack','Minimal Everyday Backpack','Fashion','A durable everyday backpack with a padded laptop sleeve and smart storage compartments.',44.50,59.00,31,4.6,73,'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=900&q=85',null,['Water-resistant fabric','15-inch laptop sleeve','20L capacity']],
            ['ceramic-coffee-set','Ceramic Coffee Set','Home & Kitchen','A minimalist ceramic cup set for slow mornings, afternoon coffee, and thoughtful gifting.',24.95,32.00,60,4.7,51,'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?w=900&q=85','Top rated',['Set of 4','Dishwasher safe','Stoneware ceramic']],
            ['running-shoes-flex','Flex Running Shoes','Sports & Outdoors','Lightweight running shoes designed for daily walks, gym sessions, and weekend runs.',67.00,79.00,16,4.4,94,'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=900&q=85','Popular',['Breathable mesh','Cushioned sole','Lightweight build']],
            ['portable-speaker','Portable Bluetooth Speaker','Electronics','Compact portable speaker with clear audio, splash resistance, and a handy carry loop.',39.95,49.95,27,4.5,111,'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=900&q=85','Deal',['Bluetooth connectivity','Splash resistant','12-hour playback']],
            ['desk-lamp-led','Adjustable LED Desk Lamp','Home & Kitchen','A clean, adjustable LED desk lamp with multiple brightness settings for work or study.',31.99,39.99,18,4.2,38,'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=900&q=85',null,['3 brightness levels','Touch control','Low-energy LED']],
            ['water-bottle-steel','Stainless Steel Bottle','Sports & Outdoors','A double-wall insulated bottle made for commuting, workouts, and weekend adventures.',19.99,24.99,75,4.3,57,'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=900&q=85',null,['750 ml capacity','Double-wall insulation','Leak-resistant lid']]
        ];
        $stmt = $db->prepare('INSERT INTO products (slug,name,category,description,price,compare_at,stock,rating,review_count,image_url,badge,specs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($products as $p) {
            [$slug,$name,$cat,$desc,$price,$compare,$stock,$rating,$count,$img,$badge,$specs] = $p;
            $stmt->execute([$slug,$name,$cat,$desc,$price,$compare,$stock,$rating,$count,$img,$badge,json_encode($specs)]);
        }
    }
}
