<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'Item.php';

$pdo = new PDO(
    "mysql:host=db;dbname=community;charset=utf8mb4",
    getenv("DB_USER"),
    getenv("DB_PASSWORD")
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$idItem = filter_input(INPUT_GET, 'idItem', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_POST, 'idItem', FILTER_VALIDATE_INT);

if (!$idItem) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE idItem = ?");
$stmt->execute([$idItem]);
$row = $stmt->fetch(PDO::FETCH_OBJ);

if (!$row) {
    die("Oggetto non trovato.");
}

$item = Item::fromStdClass($row);

if ($item->user_id !== (int)$_SESSION['user_id']) {
    http_response_code(403);
    die("Accesso negato: non sei l'autore di questo oggetto.");
}

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $value = filter_var($_POST['value'] ?? false, FILTER_VALIDATE_FLOAT);
    $weight = filter_var($_POST['weight'] ?? false, FILTER_VALIDATE_FLOAT);
    $is_on_blackbuy = isset($_POST['is_on_blackbuy']) ? 1 : 0;

    if ($name !== '' && $category !== '' && $value !== false && $weight !== false) {
        $updateStmt = $pdo->prepare(
            "UPDATE items
             SET name = ?, category = ?, value = ?, weight = ?, is_on_blackbuy = ?
             WHERE idItem = ? AND user_id = ?"
        );
        $updateStmt->execute([
            $name,
            $category,
            $value,
            $weight,
            $is_on_blackbuy,
            $item->idItem,
            (int)$_SESSION['user_id']
        ]);

        $item->name = $name;
        $item->category = $category;
        $item->value = $value;
        $item->weight = $weight;
        $item->is_on_blackbuy = (bool)$is_on_blackbuy;
        $messaggio = "Oggetto aggiornato con successo.";
    } else {
        $messaggio = "Errore: compila tutti i campi correttamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Thief Simulator - Modifica Oggetto</title>
</head>
<body>

    <nav>
        <a href="index.php">Landing Page</a> |
        <a href="insert.php">Aggiungi Oggetto</a> |
        <a href="delete.php">Gestione / Elimina</a> |
        <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
    </nav>
    <hr>

    <h1>Modifica oggetto</h1>

    <?php if ($messaggio !== ""): ?>
        <p><strong><?= htmlspecialchars($messaggio) ?></strong></p>
    <?php endif; ?>

    <form action="edit.php?idItem=<?= $item->idItem ?>" method="POST">
        <input type="hidden" name="idItem" value="<?= $item->idItem ?>">

        <label for="name">Nome:</label><br>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($item->name) ?>" required><br><br>

        <label for="category">Categoria:</label><br>
        <input type="text" id="category" name="category" value="<?= htmlspecialchars($item->category) ?>" required><br><br>

        <label for="value">Valore ($):</label><br>
        <input type="number" step="0.01" id="value" name="value" value="<?= htmlspecialchars((string)$item->value) ?>" required><br><br>

        <label for="weight">Peso (Kg):</label><br>
        <input type="number" step="0.1" id="weight" name="weight" value="<?= htmlspecialchars((string)$item->weight) ?>" required><br><br>

        <label>
            <input type="checkbox" name="is_on_blackbuy" value="1" <?= $item->is_on_blackbuy ? 'checked' : '' ?>> Presente su BlackBuy
        </label><br><br>

        <input type="submit" value="Salva modifiche">
    </form>

</body>
</html>