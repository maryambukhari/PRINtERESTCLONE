<?php
session_start();
require 'db.php';
$pin_id = $_GET['id'];
$pin = $pdo->query("SELECT p.*, u.username FROM pins p JOIN users u ON p.user_id = u.id WHERE p.id = $pin_id")->fetch();
if (!$pin) {
    header("Location: index.php");
    exit;
}
if (isset($_SESSION['user_id'])) {
    $boards = $pdo->query("SELECT * FROM boards WHERE user_id = {$_SESSION['user_id']}")->fetchAll();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $board_id = $_POST['board_id'];
        $stmt = $pdo->prepare("INSERT INTO board_pins (board_id, pin_id) VALUES (?, ?)");
        $stmt->execute([$board_id, $pin_id]);
        header("Location: board.php?id=$board_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pin - Pinterest Clone</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
        .navbar { background: #e60023; padding: 10px; color: white; text-align: center; }
        .navbar a { color: white; margin: 0 15px; text-decoration: none; }
        .pin-container { max-width: 600px; margin: 20px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .pin-container img { width: 100%; border-radius: 10px; }
        .pin-container h2 { color: #e60023; }
        .pin-container p { color: #666; }
        .save-form select, .save-form button { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .save-form button { background: #e60023; color: white; border: none; cursor: pointer; }
        .save-form button:hover { background: #b3001b; }
        @media (max-width: 600px) { .pin-container { width: 90%; } }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Profile</a>
            <a href="upload_pin.php">Upload Pin</a>
            <a href="board.php">Boards</a>
            <a href="logout.php" onclick="redirect('logout.php')">Logout</a>
        <?php else: ?>
            <a href="signup.php" onclick="redirect('signup.php')">Signup</a>
            <a href="login.php" onclick="redirect('login.php')">Login</a>
        <?php endif; ?>
    </div>
    <div class="pin-container">
        <img src="<?= $pin['image_url'] ?>" alt="<?= $pin['title'] ?>">
        <h2><?= $pin['title'] ?></h2>
        <p><?= $pin['description'] ?></p>
        <p>By <?= $pin['username'] ?></p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form class="save-form" method="POST">
                <select name="board_id" required>
                    <?php foreach ($boards as $board): ?>
                        <option value="<?= $board['id'] ?>"><?= $board['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Save to Board</button>
            </form>
        <?php endif; ?>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
    </script>
</body>
</html>
