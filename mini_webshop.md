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

---
## Működési logika

A projekt kétféle módon futtatható:

1. **Webes módban** (böngészőben):  
   Az `index.php` betölti a `Views/index.view.php` nézetet, amely megjeleníti az űrlapot és a terméklistát.

2. **CLI módban** (parancssor):  
   A `php index.php add "Név" 1999 "Leírás" 5` parancs segítségével közvetlenül vezérelhetjük a webshopot.

---

## Product.php (Model réteg)

Ez a fájl felel az adatbázis-kezelésért és a termékek tárolásáért.  
PDO kapcsolatot hoz létre, és automatikusan létrehozza a `products` táblát, ha az nem létezik.

### Kódrészlet

```php
<?php
namespace App\Models;

use PDO;
use PDOException;

class Product {
    private $pdo;

    public function __construct() {
        $host = 'localhost';
        $db   = 'webshop';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            $this->createTableIfNotExists();
        } catch (PDOException $e) {
            die("Adatbázis hiba: " . $e->getMessage());
        }
    }

    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            price DECIMAL(10,2),
            description TEXT,
            stock INT DEFAULT 0
        )";
        $this->pdo->exec($sql);
    }
}
?>
```

### Magyarázat
- A **PDO** kapcsolat biztosítja, hogy biztonságos legyen az adatbázis-kezelés.  
- A `createTableIfNotExists()` automatikusan létrehozza a táblát, így az első indításkor sem lesz hiba.

---

## CRUD metódusok a Product osztályban

A termékek kezeléséhez szükséges függvények: `add`, `update`, `delete`, `getAll` és `getById`.

```php
public function add($name, $price, $description, $stock) {
    $stmt = $this->pdo->prepare("INSERT INTO products (name, price, description, stock)
                                 VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $price, $description, $stock]);
}

public function update($id, $name, $price, $description, $stock) {
    $stmt = $this->pdo->prepare("UPDATE products SET name=?, price=?, description=?, stock=? WHERE id=?");
    return $stmt->execute([$name, $price, $description, $stock, $id]);
}

public function delete($id) {
    $stmt = $this->pdo->prepare("DELETE FROM products WHERE id=?");
    return $stmt->execute([$id]);
}

public function getAll() {
    return $this->pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
}
```

---

## ProductManager.php (Service réteg)

Ez a réteg a **logikai műveletekért** felel.  
Közvetíti a CLI és a modellek közötti adatokat.

```php
<?php
namespace App\Services;

use App\Models\Product;

class ProductManager {
    private $product;

    public function __construct() {
        $this->product = new Product();
    }

    public function listAll() {
        $products = $this->product->getAll();
        foreach ($products as $p) {
            echo "{$p['id']}. {$p['name']} – {$p['price']} Ft ({$p['stock']} db)\n";
        }
    }
}
?>
```

### Megjegyzés
A `ProductManager` egyszerű **service rétegként** működik – különválasztja a logikát az adatkezeléstől.

---

## index.view.php (Nézet)

A nézet egy alap HTML felületet biztosít, amely űrlapot és terméklistát jelenít meg.

```php
<form method="POST" action="index.php">
    <input type="text" name="name" placeholder="Termék neve" required>
    <input type="number" name="price" placeholder="Ár (Ft)" required>
    <textarea name="description" placeholder="Leírás"></textarea>
    <input type="number" name="stock" placeholder="Készlet" required>
    <button type="submit" name="action" value="add">Hozzáadás</button>
</form>

<hr>

<table>
<tr><th>ID</th><th>Név</th><th>Ár</th><th>Készlet</th><th>Művelet</th></tr>
<?php foreach ($products as $p): ?>
<tr>
<td><?= $p['id'] ?></td>
<td><?= $p['name'] ?></td>
<td><?= $p['price'] ?> Ft</td>
<td><?= $p['stock'] ?></td>
<td>
  <a href="?edit=<?= $p['id'] ?>">✏️</a>
  <a href="?delete=<?= $p['id'] ?>">🗑️</a>
</td>
</tr>
<?php endforeach; ?>
</table>
```

---

## index.php (Fő belépési pont)

Ez a fájl dönti el, hogy webes vagy CLI módban fut a program.

```php
<?php
require_once 'App/Models/Product.php';
require_once 'App/Services/ProductManager.php';

use App\Models\Product;
use App\Services\ProductManager;

$manager = new ProductManager();

if (php_sapi_name() === 'cli') {
    $args = $argv;
    $command = $args[1] ?? null;

    switch ($command) {
        case 'add':
            $manager->product->add($args[2], $args[3], $args[4], $args[5]);
            echo "✅ Termék hozzáadva!\n";
            break;
        case 'list':
            $manager->listAll();
            break;
        default:
            echo "Használat: php index.php [add|list]\n";
    }
} else {
    $product = new Product();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
        $product->add($_POST['name'], $_POST['price'], $_POST['description'], $_POST['stock']);
    }

    $products = $product->getAll();
    include 'App/Views/index.view.php';
}
?>
```

---

## CLI parancsok

| Parancs | Leírás |
|----------|--------|
| `php index.php add "Név" 4990 "Leírás" 10` | Új termék hozzáadása |
| `php index.php list` | Összes termék listázása |
| `php index.php delete 2` | Termék törlése |
| `php index.php edit 1 "Új név" 5990 "Leírás" 15` | Termék frissítése |

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
            $this->db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $user,
                $pass
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTableIfNotExists();
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
        $check = $this->db->query("SHOW COLUMNS FROM products LIKE 'stock'");
        if ($check->rowCount() === 0) {
            $sqlAddColumn = "ALTER TABLE products ADD COLUMN stock INT(11) NOT NULL DEFAULT 0 AFTER description";
            $this->db->exec($sqlAddColumn);
        }
    }

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

class Database {
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
        $stmt = $db->prepare("INSERT INTO products (name, price, description, stock, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$name, $price, $description, $stock]);
    }

    public static function update(PDO $db, int $id, string $name, float $price, string $description, int $stock): bool {
        $stmt = $db->prepare("UPDATE products SET name = ?, price = ?, description = ?, stock = ? WHERE id = ?");
        return $stmt->execute([$name, $price, $description, $stock, $id]);
    }

    public static function delete(PDO $db, int $id): bool {
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
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
$isCLI = php_sapi_name() === 'cli';

if ($isCLI) {
    array_shift($argv);
    $action = $argv[0] ?? null;

    switch ($action) {
        case 'add':
            $name = $argv[1] ?? null;
            $price = isset($argv[2]) ? (float)$argv[2] : null;
            $description = $argv[3] ?? '';
            $stock = isset($argv[4]) ? (int)$argv[4] : 0;
            if ($name && $price !== null) $product->add($name,$price,$description,$stock);
            break;
        case 'delete':
            $id = isset($argv[1]) ? (int)$argv[1] : null;
            if ($id) $product->delete($id);
            break;
        case 'edit':
            $id = isset($argv[1]) ? (int)$argv[1] : null;
            $name = $argv[2] ?? null;
            $price = isset($argv[3]) ? (float)$argv[3] : null;
            $description = $argv[4] ?? '';
            $stock = isset($argv[5]) ? (int)$argv[5] : 0;
            if ($id && $name && $price !== null) $product->update($id,$name,$price,$description,$stock);
            break;
        case 'list':
            $products = $product->getAll();
            foreach($products as $p) echo "{$p['id']} | {$p['name']} | {$p['price']} Ft | {$p['description']} | {$p['stock']}\n";
            break;
    }
} else {
    $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $postAction = $_POST['action'] ?? null;

    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if ($postAction==='add') $product->add($_POST['name'],$_POST['price'],$_POST['description'],$_POST['stock']);
        elseif ($postAction==='delete') $product->delete($_POST['id']);
        elseif ($postAction==='edit') $product->update($_POST['id'],$_POST['name'],$_POST['price'],$_POST['description'],$_POST['stock']);
        elseif ($postAction==='edit_form') $editProduct = $product->getById($_POST['id']);
        header("Location: ".$_SERVER['PHP_SELF']); exit;
    }

    $products = $product->getAll();
    require_once 'App/Views/index.view.php';
}
```
</details> 
