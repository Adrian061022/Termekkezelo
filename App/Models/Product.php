<?php
namespace App\Models;

use PDO;
use PDOException;

class Product {
    private PDO $db;

    public function __construct() {
        $host = "localhost";
        $user = "root";
        $pass = "";
        $dbname = "webshop";

        try {
            // Adatbázis kapcsolat
            $this->db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $user,
                $pass
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Tábla létrehozása, ha nem létezik
            $this->createTableIfNotExists();

            // Oszlop hozzáadása, ha nincs
            $this->addStockColumnIfMissing();

        } catch (PDOException $e) {
            die("Adatbázis hiba: " . $e->getMessage());
        }
    }

    private function createTableIfNotExists(): void {
        $sqlCreate = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            price FLOAT NOT NULL,
            description TEXT,
            stock INT(11) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->exec($sqlCreate);
    }

    private function addStockColumnIfMissing(): void {
        // Ellenőrzi, hogy a stock oszlop létezik-e
        $check = $this->db->query("SHOW COLUMNS FROM products LIKE 'stock'");
        if ($check->rowCount() === 0) {
            $sqlAddColumn = "ALTER TABLE products 
                             ADD COLUMN stock INT(11) NOT NULL DEFAULT 0 
                             AFTER description";
            $this->db->exec($sqlAddColumn);
        }
    }

    // --- Adatkezelő metódusok ---

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }

    public function add(string $name, float $price, string $description, int $stock): bool {
        $stmt = $this->db->prepare("INSERT INTO products (name, price, description, stock) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $price, $description, $stock]);
    }

    public function update(int $id, string $name, float $price, string $description, int $stock): bool {
        $stmt = $this->db->prepare("UPDATE products SET name=?, price=?, description=?, stock=? WHERE id=?");
        return $stmt->execute([$name, $price, $description, $stock, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

?>
