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
