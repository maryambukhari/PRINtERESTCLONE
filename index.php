<?php
session_start();
require 'db.php';
$pins = $pdo->query("SELECT p.*, u.username FROM pins p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 20")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinterest Clone - Home</title>
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
        .search-bar {
            text-align: center;
            margin: 30px 20px;
        }
        .search-bar input {
            padding: 12px 20px;
            width: 350px;
            max-width: 90%;
            border-radius: 25px;
            border: 1px solid #ddd;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .search-bar input:focus {
            border-color: #e60023;
            box-shadow: 0 0 10px rgba(230, 0, 35, 0.3);
            outline: none;
        }
        .categories {
            text-align: center;
            margin: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        .categories a {
            background: #f4f4f4;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .categories a:hover {
            background: #e60023;
            color: white;
            transform: translateY(-2px);
        }
        .pin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .pin {
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
        .pin:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
        .pin img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .pin-info {
            padding: 15px;
            text-align: center;
        }
        .pin-info h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
            font-weight: bold;
        }
        .pin-info p {
            color: #666;
            font-size: 14px;
            margin: 5px 0 0;
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
            .pin-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
            .search-bar input {
                width: 80%;
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
    <div class="search-bar">
        <form action="search.php" method="GET">
            <input type="text" name="query" placeholder="Search for inspiration...">
        </form>
    </div>
    <div class="categories">
        <?php foreach ($categories as $category): ?>
            <a href="search.php?category=<?= $category['id'] ?>"><?= $category['name'] ?></a>
        <?php endforeach; ?>
    </div>
    <div class="pin-grid">
        <?php foreach ($pins as $pin): ?>
            <div class="pin">
                <a href="view_pin.php?id=<?= $pin['id'] ?>" onclick="redirect('view_pin.php?id=<?= $pin['id'] ?>')">
                    <img src="<?= $pin['image_url'] ?>" alt="<?= $pin['title'] ?>">
                    <div class="pin-overlay">View Pin</div>
                    <div class="pin-info">
                        <h3><?= htmlspecialchars($pin['title']) ?></h3>
                        <p>By <?= htmlspecialchars($pin['username']) ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
    </script>
</body>
</html>
