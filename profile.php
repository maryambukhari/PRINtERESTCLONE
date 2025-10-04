<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$user = $pdo->query("SELECT * FROM users WHERE id = $user_id")->fetch();
$pins = $pdo->query("SELECT * FROM pins WHERE user_id = $user_id")->fetchAll();
$boards = $pdo->query("SELECT * FROM boards WHERE user_id = $user_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Pinterest Clone</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
        .navbar { background: #e60023; padding: 10px; color: white; text-align: center; }
        .navbar a { color: white; margin: 0 15px; text-decoration: none; }
        .profile-container { text-align: center; padding: 20px; }
        .profile-container h2 { color: #e60023; }
        .pin-grid, .board-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; padding: 20px; }
        .pin, .board { background: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .pin:hover, .board:hover { transform: scale(1.05); }
        .pin img { width: 100%; height: 200px; object-fit: cover; }
        .pin-info, .board-info { padding: 10px; }
        .pin-info h3, .board-info h3 { margin: 0; font-size: 16px; }
        @media (max-width: 600px) { .pin-grid, .board-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); } }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="upload_pin.php">Upload Pin</a>
        <a href="board.php">Boards</a>
        <a href="logout.php" onclick="redirect('logout.php')">Logout</a>
    </div>
    <div class="profile-container">
        <h2>Welcome, <?= $user['username'] ?></h2>
        <h3>Your Pins</h3>
        <div class="pin-grid">
            <?php foreach ($pins as $pin): ?>
                <div class="pin">
                    <a href="view_pin.php?id=<?= $pin['id'] ?>" onclick="redirect('view_pin.php?id=<?= $pin['id'] ?>')">
                        <img src="<?= $pin['image_url'] ?>" alt="<?= $pin['title'] ?>">
                        <div class="pin-info">
                            <h3><?= $pin['title'] ?></h3>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <h3>Your Boards</h3>
        <div class="board-grid">
            <?php foreach ($boards as $board): ?>
                <div class="board">
                    <a href="board.php?id=<?= $board['id'] ?>" onclick="redirect('board.php?id=<?= $board['id'] ?>')">
                        <div class="board-info">
                            <h3><?= $board['name'] ?></h3>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
    </script>
</body>
</html>
