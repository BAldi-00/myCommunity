<?php
    $pdo = new PDO(
        "mysql:host=db;dbname=community;charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['idItem'])) {
        $idItem = filter_var($_GET['idItem'], FILTER_VALIDATE_INT);
        if ($idItem) {
            $stmt = $pdo->prepare("DELETE FROM items WHERE idItem = ?");
            $stmt->execute([$idItem]);
            header("Location: delete.php");
            exit;
        }
    }

    
    $sql = "SELECT * FROM items ORDER BY idItem DESC";
    $query = $pdo->query($sql);
    $items = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Thief Simulator - Elimina Oggetti</title>
</head>
<body>

    <nav>
        <a href="index.php">Landing Page</a> | 
        <a href="insert.php">Aggiungi Oggetto</a> | 
        <a href="delete.php">Elimina Elementi</a>
    </nav>
    <hr>

    <h1>Gestione ed Eliminazione Oggetti</h1>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Valore ($)</th>
                <th>Azione</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->name ?? '') ?></td>
                        <td><?= htmlspecialchars($item->category ?? '') ?></td>
                        <td><?= htmlspecialchars($item->value ?? '') ?></td>
                        <td>
                            <!-- Usiamo idItem nel link senza mostrarlo visivamente in tabella -->
                            <a href="delete.php?idItem=<?= $item->idItem ?>" onclick="return confirm('Sei sicuro di voler eliminare questo elemento?');">Elimina</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nessun oggetto disponibile per l'eliminazione.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>