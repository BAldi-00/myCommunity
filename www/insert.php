<?php
    // Connessione al database
    $pdo = new PDO(
        "mysql:host=db;dbname=community;charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );

    $messaggio = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $category = trim($_POST['category']);
        $value = filter_var($_POST['value'] ?? false, FILTER_VALIDATE_FLOAT);
        $weight = filter_var($_POST['weight'] ?? false, FILTER_VALIDATE_FLOAT);
        $is_on_blackbuy = isset($_POST['is_on_blackbuy']) ? 1 : 0;

        if ($name !== '' && $category !== '' && $value !== false && $weight !== false) {
            $sql = "INSERT INTO items (name, category, value, weight, is_on_blackbuy) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $category, $value, $weight, $is_on_blackbuy]);
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
    <title>Thief Simulator - Aggiungi Oggetto</title>
</head>
<body>

    <nav>
        <a href="index.php">Home</a> | 
        <a href="insert.php">Aggiungi Oggetto</a> | 
        <a href="delete.php">Elimina Elementi</a>
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
        <input type="number" id="value" name="value" step="0.01" required><br><br>

        <label for="weight">Peso (Kg):</label><br>
        <input type="number" step="0.1" id="weight" name="weight" required><br><br>

        <label>
            <input type="checkbox" name="is_on_blackbuy" value="1"> Presente su BlackBuy
        </label><br><br>

        <input type="submit" value="Salva Oggetto">
    </form>

</body>
</html>