<?php

namespace App\Services;

use PDO;
use PDOException;

class Database {
    // Az összes CRUD függvény ide kerül
    public static function getAll(PDO $db): array {
        $stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(PDO $db, int $id): ?array {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }

    public static function add(PDO $db, string $name, float $price, string $description, int $stock): bool {
        $stmt = $db->prepare("
            INSERT INTO products (name, price, description, stock, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$name, $price, $description, $stock]);
    }

    public static function update(PDO $db, int $id, string $name, float $price, string $description, int $stock): bool {
        $stmt = $db->prepare("
            UPDATE products
            SET name = ?, price = ?, description = ?, stock = ?
            WHERE id = ?
        ");
        return $stmt->execute([$name, $price, $description, $stock, $id]);
    }

    public static function delete(PDO $db, int $id): bool {
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
