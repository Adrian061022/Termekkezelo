# Mini Webshop – PHP Projekt

Ez a projekt egy egyszerű **PHP alapú webshop-termékkezelő**, amely lehetővé teszi a termékek hozzáadását, szerkesztését, törlését és megtekintését **webes felületen** vagy **CLI parancssorban** is.  
A cél egy **tanulható, jól dokumentált, PDO-t használó** PHP-alkalmazás bemutatása.

---

## Projekt felépítése

```
/App
 ├── Models/
 │    └── Product.php
 ├── Services/
 │    └── ProductManager.php
 └── Views/
      └── index.view.php
index.php
```

---

## Alapfunkciók

- **PDO adatbázis kapcsolat** automatikus tábla létrehozással  
- **CRUD műveletek:** Create, Read, Update, Delete  
- **Egyszerű webes felület** termékek kezelésére  
- **CLI mód támogatása** (parancssori vezérlés)

---
## Felépítés

### Adatbázis kapcsolat (Product.php)
A Product osztály létrehozza a PDO kapcsolatot, ellenőrzi a tábla meglétét, és biztosítja az oszlopokat:

```php
$this->db = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8",
    $user,
    $pass
);
$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$this->createTableIfNotExists();
$this->addStockColumnIfMissing();
```
1. PDO kapcsolat a MySQL adatbázishoz.
2. Hibakezelés: kivétel dobása adatbázis hiba esetén.
3. Tábla létrehozása: createTableIfNotExists().
4. Stock oszlop ellenőrzése: addStockColumnIfMissing().

### Tábla létrehozása

```php
$sqlCreate = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price FLOAT NOT NULL,
    description TEXT,
    stock INT(11) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$this->db->exec($sqlCreate);
```
- IF NOT EXISTS: nem hozza létre újra, ha már létezik.
- AUTO_INCREMENT: automatikusan növekvő ID.
- created_at: automatikusan rögzíti a létrehozás idejét.

## CRUD függvények

### Összes termék lekérése:
```php
public function getAll(): array {
    $stmt = $this->db->query("SELECT * FROM products ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```
- ORDER BY id DESC: a legújabb termékek jelennek meg először.
- PDO::FETCH_ASSOC: asszociatív tömböt ad vissza.

### Termék hozzáadása:
  ```php
public function add(string $name, float $price, string $description, int $stock): bool {
    $stmt = $this->db->prepare(
        "INSERT INTO products (name, price, description, stock) VALUES (?, ?, ?, ?)"
    );
    return $stmt->execute([$name, $price, $description, $stock]);
}
```
- prepare + execute: biztonságos SQL beszúrás.
- Paraméterek ?-ekkel, hogy elkerüljük az SQL injectiont.

## Webes felület (index.view.php)

Az oldal lehetővé teszi a termékek listázását, szerkesztését és törlését:
```html
<h2>Új termék hozzáadása</h2>
<form method="post">
    <input type="hidden" name="action" value="add">
    <input type="text" name="name" placeholder="Termék neve" required>
    <input type="number" name="price" placeholder="Ár (Ft)" required>
    <textarea name="description" placeholder="Leírás"></textarea>
    <input type="number" name="stock" placeholder="Készlet" required>
    <button type="submit">Hozzáadás</button>
</form>
```
- action="add": POST kérés kezelése az index.php-ban.
- required: kötelező mezők.
- <textarea> a hosszabb leírásoknak.

### Terméklista
```html
<table>
    <thead>
        <tr>
            <th>ID</th><th>Név</th><th>Ár</th><th>Leírás</th><th>Készlet</th><th>Művelet</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['id']) ?></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['price']) ?> Ft</td>
                <td><?= htmlspecialchars($product['description']) ?></td>
                <td><?= htmlspecialchars($product['stock']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <button type="submit">🗑️ Törlés</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```
- htmlspecialchars: biztonságos megjelenítés.
- Külön form minden művelethez (törlés, szerkesztés).

## CLI támogatás (index.php)
A parancssorból is kezelhetők a termékek:

```php
$isCLI = php_sapi_name() === 'cli';

if ($isCLI) {
    $action = $argv[1] ?? null;
    switch ($action) {
        case 'add':
            $product->add($argv[2], (float)$argv[3], $argv[4] ?? '', (int)($argv[5] ?? 0));
            echo "Termék hozzáadva: {$argv[2]}\n";
            break;
        case 'delete':
            $product->delete((int)$argv[2]);
            echo "Termék törölve: ID {$argv[2]}\n";
            break;
        case 'list':
            $products = $product->getAll();
            foreach ($products as $p) {
                echo "{$p['id']} | {$p['name']} | {$p['price']} Ft | {$p['description']} | {$p['stock']}\n";
            }
            break;
    }
}
```
1. php_sapi_name() === 'cli': CLI környezet ellenőrzése.
2. $argv: parancssori argumentumok.
3 Switch/case a műveletekhez.

## Használat
1. Weben: Böngészőből az index.php-t megnyitva lehet termékeket kezelni.
2. CLI: Parancssorból:

- php index.php add "Laptop" 3500000 "Erős laptop" 5
- php index.php delete 3
- php index.php list

---

## Fejlesztési környezet

- **PHP 8+**
- **MySQL / MariaDB**
- **XAMPP / Localhost**
- Böngésző: Chrome, Edge, Firefox

---

<details><summary>App/Models/Product.php</summary>

```php
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

```
</details>
<details><summary>App/Services/ProductManager.php</summary>

```php

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
```
</details>
<details><summary>App/Views/index.view.php</summary>

```php

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Mini Webshop - Termékkezelő</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f8f9fa; }
        h1 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #eee; }
        form { margin-top: 20px; background: white; padding: 15px; border-radius: 8px; }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 8px 15px; cursor: pointer; background: #2c3e50; color: white; border: none; border-radius: 5px; }
        button:hover { background: #34495e; }
    </style>
</head>
<body>
    <h1>🛒 Mini Webshop - Termékkezelő</h1>

    <h2>Új termék hozzáadása</h2>
    <form method="post">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Termék neve" required>
        <input type="number" name="price" placeholder="Ár (Ft)" required>
        <textarea name="description" placeholder="Leírás"></textarea>
        <input type="number" name="stock" placeholder="Készlet" required>
        <button type="submit">Hozzáadás</button>
    </form>

    <?php if(isset($editProduct)): ?>
    <h2>Termék szerkesztése</h2>
    <form method="post">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
        <input type="text" name="name" value="<?= htmlspecialchars($editProduct['name']) ?>" required>
        <input type="number" name="price" value="<?= htmlspecialchars($editProduct['price']) ?>" required>
        <textarea name="description"><?= htmlspecialchars($editProduct['description']) ?></textarea>
        <input type="number" name="stock" value="<?= htmlspecialchars($editProduct['stock']) ?>" required>
        <button type="submit">Mentés</button>
    </form>
    <?php endif; ?>

    <h2>Terméklista</h2>
    <?php if (empty($products)): ?>
        <p>Nincs termék az adatbázisban.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Név</th><th>Ár</th><th>Leírás</th><th>Készlet</th><th>Művelet</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['id']) ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['price']) ?> Ft</td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                        <td><?= htmlspecialchars($product['stock']) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <button type="submit">🗑️ Törlés</button>
                            </form>

                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="edit_form">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <button type="submit">✏️ Szerkesztés</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>

```
</details>

<details><summary>index.php</summary>

```php

<?php
require_once 'App/Models/Product.php';
use App\Models\Product;

$product = new Product();

// --- Meghatározzuk a környezetet ---
$isCLI = php_sapi_name() === 'cli';

// --- CLI argumentumok feldolgozása ---
if ($isCLI) {
    array_shift($argv); // index.php eltávolítása
    $action = $argv[0] ?? null;

    switch ($action) {
        case 'add':
            $name = $argv[1] ?? null;
            $price = isset($argv[2]) ? (float)$argv[2] : null;
            $description = $argv[3] ?? '';
            $stock = isset($argv[4]) ? (int)$argv[4] : 0;

            if ($name && $price !== null) {
                $product->add($name, $price, $description, $stock);
                echo "Termék hozzáadva: $name\n";
            } else {
                echo "Hiba: add <name> <price> [description] [stock]\n";
            }
            break;

        case 'delete':
            $id = isset($argv[1]) ? (int)$argv[1] : null;
            if ($id) {
                $product->delete($id);
                echo "Termék törölve: ID $id\n";
            } else {
                echo "Hiba: delete <id>\n";
            }
            break;

        case 'edit':
            $id = isset($argv[1]) ? (int)$argv[1] : null;
            $name = $argv[2] ?? null;
            $price = isset($argv[3]) ? (float)$argv[3] : null;
            $description = $argv[4] ?? '';
            $stock = isset($argv[5]) ? (int)$argv[5] : 0;

            if ($id && $name && $price !== null) {
                $product->update($id, $name, $price, $description, $stock);
                echo "Termék frissítve: ID $id\n";
            } else {
                echo "Hiba: edit <id> <name> <price> [description] [stock]\n";
            }
            break;

        case 'list':
            $products = $product->getAll();
            if (empty($products)) {
                echo "Nincs termék az adatbázisban.\n";
            } else {
                foreach ($products as $p) {
                    echo "{$p['id']} | {$p['name']} | {$p['price']} Ft | {$p['description']} | {$p['stock']}\n";
                }
            }
            break;

        default:
           
            break;
    }

} else {
    // --- Webes környezet ---
    $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $postAction = $_POST['action'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($postAction === 'add') {
            $product->add($_POST['name'], $_POST['price'], $_POST['description'], $_POST['stock']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } elseif ($postAction === 'delete') {
            $product->delete($_POST['id']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } elseif ($postAction === 'edit') {
            $product->update($_POST['id'], $_POST['name'], $_POST['price'], $_POST['description'], $_POST['stock']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } elseif ($postAction === 'edit_form') {
            $editProduct = $product->getById($_POST['id']);
        }
    }

    // --- Webes lista megjelenítése ---
    $products = $product->getAll();
    require_once 'App/Views/index.view.php';
}

```
</details> 
