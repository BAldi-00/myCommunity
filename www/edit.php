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

$currentUserId = (int)$_SESSION['user_id'];
$messaggio = "";
$itemToEdit = null;

// Eliminazione tramite ?delete=idItem
if (isset($_GET['delete'])) {
    $deleteId = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($deleteId) {
        $check = $pdo->prepare("SELECT * FROM items WHERE idItem = ?");
        $check->execute([$deleteId]);
        $row = $check->fetch(PDO::FETCH_OBJ);

        if ($row) {
            $target = Item::fromStdClass($row);
            if ($target->user_id !== $currentUserId) {
                $messaggio = "Non puoi eliminare un oggetto di un altro utente.";
            } else {
                $del = $pdo->prepare("DELETE FROM items WHERE idItem = ? AND user_id = ?");
                $del->execute([$deleteId, $currentUserId]);
                header("Location: edit.php");
                exit;
            }
        }
    }
}

// Salvataggio modifica tramite POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_edit'])) {
    $editId = filter_var($_POST['idItem'] ?? null, FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $value = filter_var($_POST['value'] ?? false, FILTER_VALIDATE_FLOAT);
    $weight = filter_var($_POST['weight'] ?? false, FILTER_VALIDATE_FLOAT);
    $is_on_blackbuy = isset($_POST['is_on_blackbuy']) ? 1 : 0;

    if ($editId) {
        $check = $pdo->prepare("SELECT * FROM items WHERE idItem = ?");
        $check->execute([$editId]);
        $row = $check->fetch(PDO::FETCH_OBJ);

        if ($row) {
            $target = Item::fromStdClass($row);
            if ($target->user_id !== $currentUserId) {
                $messaggio = "Non puoi modificare un oggetto di un altro utente.";
            } elseif ($name !== '' && $category !== '' && $value !== false && $weight !== false) {
                $upd = $pdo->prepare(
                    "UPDATE items
                     SET name = ?, category = ?, value = ?, weight = ?, is_on_blackbuy = ?
                     WHERE idItem = ? AND user_id = ?"
                );
                $upd->execute([
                    $name,
                    $category,
                    $value,
                    $weight,
                    $is_on_blackbuy,
                    $editId,
                    $currentUserId
                ]);
                $messaggio = "Oggetto aggiornato con successo.";
            } else {
                $messaggio = "Errore: compila tutti i campi correttamente.";
            }
        }
    }
}

// Caricamento oggetto selezionato per modifica (?idItem=...)
$selectedId = filter_input(INPUT_GET, 'idItem', FILTER_VALIDATE_INT);
if ($selectedId) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE idItem = ?");
    $stmt->execute([$selectedId]);
    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if ($row) {
        $candidate = Item::fromStdClass($row);
        if ($candidate->user_id !== $currentUserId) {
            $messaggio = "Accesso negato: questo oggetto non è tuo.";
        } else {
            $itemToEdit = $candidate;
        }
    }
}

// Oggetti dell'utente loggato
$listStmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY idItem DESC");
$listStmt->execute([$currentUserId]);
$myItems = array_map(fn($r) => Item::fromStdClass($r), $listStmt->fetchAll(PDO::FETCH_OBJ));
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Thief Simulator - Modifica / Elimina</title>
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

    <h1>Modifica / Elimina oggetti</h1>

    <?php if ($messaggio !== ""): ?>
        <p><strong><?= htmlspecialchars($messaggio) ?></strong></p>
    <?php endif; ?>

    <?php if ($itemToEdit !== null): ?>
        <h2>Modifica oggetto</h2>
        <form action="edit.php?idItem=<?= $itemToEdit->idItem ?>" method="POST">
            <input type="hidden" name="idItem" value="<?= $itemToEdit->idItem ?>">

            <label for="name">Nome:</label><br>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($itemToEdit->name) ?>" required><br><br>

            <label for="category">Categoria:</label><br>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($itemToEdit->category) ?>" required><br><br>

            <label for="value">Valore ($):</label><br>
            <input type="number" step="0.01" id="value" name="value" value="<?= htmlspecialchars((string)$itemToEdit->value) ?>" required><br><br>

            <label for="weight">Peso (Kg):</label><br>
            <input type="number" step="0.1" id="weight" name="weight" value="<?= htmlspecialchars((string)$itemToEdit->weight) ?>" required><br><br>

            <label>
                <input type="checkbox" name="is_on_blackbuy" value="1" <?= $itemToEdit->is_on_blackbuy ? 'checked' : '' ?>> Presente su BlackBuy
            </label><br><br>

            <input type="submit" name="save_edit" value="Salva modifiche">
        </form>
        <hr>
    <?php endif; ?>

    <h2>I tuoi oggetti</h2>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Valore ($)</th>
                <th>Peso (Kg)</th>
                <th>BlackBuy</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($myItems) > 0): ?>
                <?php foreach ($myItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->name) ?></td>
                        <td><?= htmlspecialchars($item->category) ?></td>
                        <td><?= htmlspecialchars((string)$item->value) ?></td>
                        <td><?= htmlspecialchars((string)$item->weight) ?></td>
                        <td><?= $item->is_on_blackbuy ? 'Sì' : 'No' ?></td>
                        <td>
                            <a href="edit.php?idItem=<?= $item->idItem ?>">Modifica</a> |
                            <a href="edit.php?delete=<?= $item->idItem ?>" onclick="return confirm('Sei sicuro di voler eliminare questo elemento?');">Elimina</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Non hai ancora inserito alcun oggetto.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>