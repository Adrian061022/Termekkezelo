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
