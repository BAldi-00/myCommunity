<?php
session_start();

$pdo = new PDO(
    "mysql:host=db;dbname=community;charset=utf8mb4",
    getenv("DB_USER"),
    getenv("DB_PASSWORD")
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $messaggio = "Inserisci nome utente e password.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);

        if ($check->fetch(PDO::FETCH_OBJ)) {
            $messaggio = "Nome utente già esistente.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $ins->execute([$username, $hash]);
            $messaggio = "Registrazione completata. Ora puoi effettuare il login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
</head>
<body>

    <nav>
        <a href="index.php">Landing Page</a> |
        <a href="login.php">Login</a> |
        <a href="register.php">Registrazione</a>
    </nav>
    <hr>

    <h1>Registrazione</h1>

    <?php if ($messaggio !== ""): ?>
        <p><strong><?= htmlspecialchars($messaggio) ?></strong></p>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <label for="username">Nome utente:</label><br>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Registrati">
    </form>

    <p>Hai già un account? <a href="login.php">Vai al login</a></p>

</body>
</html>