<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'Item.php';

$pdo = new PDO(
    "mysql:host=db;dbname=community;charset=utf8mb4",
    getenv("DB_USER"),
    getenv("DB_PASSWORD")
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = new Item();
    $item->name = trim($_POST['name'] ?? '');
    $item->category = trim($_POST['category'] ?? '');
    $value = filter_var($_POST['value'] ?? false, FILTER_VALIDATE_FLOAT);
    $weight = filter_var($_POST['weight'] ?? false, FILTER_VALIDATE_FLOAT);
    $item->is_on_blackbuy = isset($_POST['is_on_blackbuy']);
    $item->user_id = (int)$_SESSION['user_id'];

    if ($item->name !== '' && $item->category !== '' && $value !== false && $weight !== false) {
        $item->value = $value;
        $item->weight = $weight;

        $sql = "INSERT INTO items (name, category, value, weight, is_on_blackbuy, user_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $item->name,
            $item->category,
            $item->value,
            $item->weight,
            $item->is_on_blackbuy ? 1 : 0,
            $item->user_id
        ]);
        $messaggio = "Oggetto inserito con successo!";
    } else {
        $messaggio = "Errore: compila tutti i campi correttamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Thief Simulator - Inserisci Oggetto</title>
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

    <h1>Inserisci un nuovo oggetto</h1>

    <?php if ($messaggio !== ""): ?>
        <p><strong><?= htmlspecialchars($messaggio) ?></strong></p>
    <?php endif; ?>

    <form action="insert.php" method="POST">
        <label for="name">Nome:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="category">Categoria:</label><br>
        <input type="text" id="category" name="category" required><br><br>

        <label for="value">Valore ($):</label><br>
        <input type="number" step="0.01" id="value" name="value" required><br><br>

        <label for="weight">Peso (Kg):</label><br>
        <input type="number" step="0.1" id="weight" name="weight" required><br><br>

        <label>
            <input type="checkbox" name="is_on_blackbuy" value="1"> Presente su BlackBuy
        </label><br><br>

        <input type="submit" value="Salva Oggetto">
    </form>

</body>
</html>