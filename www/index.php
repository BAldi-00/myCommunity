<?php
    $pdo = new PDO(
        "mysql:host=db;dbname=community;charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM items";
    $query = $pdo->query($sql);
    
    $items = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Thief Simulator - Landing Page</title>
</head>
<body>

    <nav>
        <a href="index.php">Landing Page</a> | 
        <a href="insert.php">Aggiungi Oggetto</a> | 
        <a href="delete.php">Elimina Elementi</a>
    </nav>
    <hr>

    <h1>Database Refurtiva - Thief Simulator</h1>
    
    <h2>Elenco degli oggetti</h2>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Valore ($)</th>
                <th>Peso (Kg)</th>
                <th>BlackBuy</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->name ?? '') ?></td>
                        <td><?= htmlspecialchars($item->category ?? '') ?></td>
                        <td><?= htmlspecialchars($item->value ?? '') ?></td>
                        <td><?= htmlspecialchars($item->weight ?? '') ?></td>
                        <td><?= ($item->is_on_blackbuy ?? 0) ? 'Sì' : 'No' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nessun oggetto trovato nel database.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>