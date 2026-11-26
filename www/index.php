<?php
session_start();
include 'items.php';

$pdo = new PDO(
    "mysql:host=db;dbname=community;charset=utf8mb4",
    getenv("DB_USER"),
    getenv("DB_PASSWORD")
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT items.*, users.username AS author_name
        FROM items
        LEFT JOIN users ON items.user_id = users.id
        ORDER BY items.idItem DESC";
$query = $pdo->query($sql);
$rows = $query->fetchAll(PDO::FETCH_OBJ);
$items = array_map(fn($r) => Item::fromStdClass($r), $rows);

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Thief Simulator - Home</title>
</head>
<body>

    <nav>
    <a href="index.php">Home</a> |
    <a href="login.php">Login</a> |
    <a href="register.php">Registrazione</a> |
    <a href="insert.php">Inserisci</a> |
    <a href="edit.php">Modifica / Elimina</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        | <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username'] ?? '') ?>)</a>
    <?php endif; ?>
</nav>
<hr>

    <h1>Thief Simulator</h1>

    <?php if ($currentUserId === null): ?>
        <p>Effettua il <a href="login.php">Login</a> per inserire, modificare o eliminare i tuoi oggetti.</p>
    <?php else: ?>
        <p>Loggato come <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong>.</p>
    <?php endif; ?>

    <h2>Elenco degli oggetti</h2>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Valore ($)</th>
                <th>Peso (Kg)</th>
                <th>BlackBuy</th>
                <th>Autore</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->name) ?></td>
                        <td><?= htmlspecialchars($item->category) ?></td>
                        <td><?= htmlspecialchars((string)$item->value) ?></td>
                        <td><?= htmlspecialchars((string)$item->weight) ?></td>
                        <td><?= $item->is_on_blackbuy ? 'Sì' : 'No' ?></td>
                        <td><?= htmlspecialchars($item->author_name ?? 'Anonimo') ?></td>
                        <td>
                            <?php if ($currentUserId !== null && $item->user_id === $currentUserId): ?>
                                <a href="edit.php?idItem=<?= $item->idItem ?>">Modifica</a> |
                                <a href="edit.php?delete=<?= $item->idItem ?>" onclick="return confirm('Sei sicuro di voler eliminare questo elemento?');">Elimina</a>
                            <?php elseif ($currentUserId === null): ?>
                                <a href="login.php">Accedi</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nessun oggetto trovato nel database.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>