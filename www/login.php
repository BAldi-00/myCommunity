<?php
session_start();

$pdo = new PDO(
    "mysql:host=db;dbname=community;charset=utf8mb4",
    getenv("DB_USER"),
    getenv("DB_PASSWORD")
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$errore = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if ($user && password_verify($password, $user->password_hash)) {
        $_SESSION['user_id'] = (int)$user->id;
        $_SESSION['username'] = $user->username;
        header("Location: index.php");
        exit;
    } else {
        $errore = "Credenziali non valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

    <nav>
        <a href="index.php">Landing Page</a> |
        <a href="login.php">Login</a> |
        <a href="register.php">Registrazione</a>
    </nav>
    <hr>

    <h1>Login</h1>

    <?php if ($errore !== ""): ?>
        <p><strong><?= htmlspecialchars($errore) ?></strong></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label for="username">Nome utente:</label><br>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Accedi">
    </form>

    <p>Non hai un account? <a href="register.php">Registrati qui</a></p>

</body>
</html>