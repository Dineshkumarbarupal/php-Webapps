<?php
declare(strict_types=1);
namespace App\Repositories;
use App\Database;

final class ProductRepository {
    public function featured(int $limit = 8): array { $stmt = Database::connection()->prepare('SELECT * FROM products WHERE active=1 ORDER BY rating DESC, review_count DESC LIMIT ?'); $stmt->bindValue(1, $limit, \PDO::PARAM_INT); $stmt->execute(); return $stmt->fetchAll(); }
    public function find(int $id): ?array { $s=Database::connection()->prepare('SELECT * FROM products WHERE id=? AND active=1'); $s->execute([$id]); return $s->fetch() ?: null; }
    public function bySlug(string $slug): ?array { $s=Database::connection()->prepare('SELECT * FROM products WHERE slug=? AND active=1'); $s->execute([$slug]); return $s->fetch() ?: null; }
    public function listing(array $filters): array {
        $where = ['active=1']; $params = [];
        if (!empty($filters['q'])) { $where[]='(name LIKE ? OR description LIKE ? OR category LIKE ?)'; $q='%'.$filters['q'].'%'; array_push($params,$q,$q,$q); }
        if (!empty($filters['category'])) { $where[]='category=?'; $params[]=$filters['category']; }
        if (isset($filters['min']) && $filters['min'] !== '') { $where[]='price>=?'; $params[]=(float)$filters['min']; }
        if (isset($filters['max']) && $filters['max'] !== '') { $where[]='price<=?'; $params[]=(float)$filters['max']; }
        $sort = match($filters['sort'] ?? 'featured') { 'price-asc' => 'price ASC', 'price-desc' => 'price DESC', 'rating' => 'rating DESC, review_count DESC', 'newest' => 'created_at DESC', default => 'rating DESC, review_count DESC' };
        $sql='SELECT * FROM products WHERE '.implode(' AND ',$where).' ORDER BY '.$sort;
        $s=Database::connection()->prepare($sql); $s->execute($params); return $s->fetchAll();
    }
    public function categories(): array { return Database::connection()->query('SELECT category, COUNT(*) total FROM products WHERE active=1 GROUP BY category ORDER BY category')->fetchAll(); }
}
