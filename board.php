<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$boards = $pdo->query("SELECT * FROM boards WHERE user_id = $user_id")->fetchAll();

// Create new board
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_board'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO boards (user_id, name) VALUES (?, ?)");
        if ($stmt->execute([$user_id, $name])) {
            header("Location: board.php");
            exit;
        } else {
            $error = "Failed to create board.";
        }
    } else {
        $error = "Board name cannot be empty.";
    }
}

// Fetch pins for a specific board
$board_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$board_pins = [];
$board_name = '';
if ($board_id) {
    // Verify board exists and belongs to the user
    $stmt = $pdo->prepare("SELECT name FROM boards WHERE id = ? AND user_id = ?");
    $stmt->execute([$board_id, $user_id]);
    $board = $stmt->fetch();
    if ($board) {
        $board_name = $board['name'];
        $board_pins = $pdo->query("SELECT p.* FROM pins p JOIN board_pins bp ON p.id = bp.pin_id WHERE bp.board_id = $board_id")->fetchAll();
    } else {
        $error = "Board not found or you don't have access.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boards - Pinterest Clone</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background: #fff;
            color: #333;
        }
        .navbar {
            background: linear-gradient(90deg, #e60023, #b3001b);
            padding: 15px;
            color: white;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar a {
            color: white;
            margin: 0 20px;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            transition: color 0.3s, transform 0.2s;
        }
        .navbar a:hover {
            color: #ffd700;
            transform: scale(1.1);
        }
        .board-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .board-container h2 {
            color: #e60023;
            text-align: center;
            font-size: 28px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .create-board {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            margin-bottom: 30px;
            text-align: center;
        }
        .create-board input {
            padding: 12px;
            width: 300px;
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            margin-right: 10px;
        }
        .create-board button {
            background: #e60023;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s, transform 0.2s;
        }
        .create-board button:hover {
            background: #b3001b;
            transform: scale(1.05);
        }
        .error {
            color: #e60023;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .board-grid, .pin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .board, .pin {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeInUp 0.5s ease-in;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .board:hover, .pin:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
        .board h3, .pin h3 {
            padding: 15px;
            margin: 0;
            font-size: 16px;
            color: #333;
            text-align: center;
        }
        .pin img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .pin-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .pin:hover .pin-overlay {
            opacity: 1;
        }
        @media (max-width: 600px) {
            .board-grid, .pin-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
            .create-board input {
                width: 80%;
                margin-bottom: 10px;
            }
            .navbar a {
                margin: 0 10px;
                font-size: 14px;
            }
            .pin img {
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="upload_pin.php">Upload Pin</a>
        <a href="logout.php" onclick="redirect('logout.php')">Logout</a>
    </div>
    <div class="board-container">
        <h2>Your Boards</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <div class="create-board">
            <form method="POST">
                <input type="text" name="name" placeholder="Enter Board Name" required>
                <button type="submit" name="create_board">Create Board</button>
            </form>
        </div>
        <div class="board-grid">
            <?php if (empty($boards)): ?>
                <p style="text-align: center; color: #666;">No boards yet. Create one above!</p>
            <?php else: ?>
                <?php foreach ($boards as $board): ?>
                    <div class="board">
                        <a href="board.php?id=<?= $board['id'] ?>" onclick="redirect('board.php?id=<?= $board['id'] ?>')">
                            <h3><?= htmlspecialchars($board['name']) ?></h3>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if ($board_id && $board_name): ?>
            <h2>Pins in "<?= htmlspecialchars($board_name) ?>"</h2>
            <?php if (empty($board_pins)): ?>
                <p style="text-align: center; color: #666;">No pins in this board yet. Save some from the homepage!</p>
            <?php else: ?>
                <div class="pin-grid">
                    <?php foreach ($board_pins as $pin): ?>
                        <div class="pin">
                            <a href="view_pin.php?id=<?= $pin['id'] ?>" onclick="redirect('view_pin.php?id=<?= $pin['id'] ?>')">
                                <img src="<?= htmlspecialchars($pin['image_url']) ?>" alt="<?= htmlspecialchars($pin['title']) ?>">
                                <div class="pin-overlay">View Pin</div>
                                <h3><?= htmlspecialchars($pin['title']) ?></h3>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
    </script>
</body>
</html>
