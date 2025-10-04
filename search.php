<?php
session_start();
require 'db.php';
$query = isset($_GET['query']) ? $_GET['query'] : '';
$category_id = isset($_GET['category']) ? $_GET['category'] : '';
$where = [];
$params = [];
if ($query) {
    $where[] = "p.title LIKE ?";
    $params[] = "%$query%";
}
if ($category_id) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
}
$sql = "SELECT p.*, u.username FROM pins p JOIN users u ON p.user_id = u.id";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pins = $stmt->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Pinterest Clone</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
        .navbar { background: #e60023; padding: 10px; color: white; text-align: center; }
        .navbar a { color: white; margin: 0 15px; text-decoration: none; }
        .search-bar { text-align: center; margin: 20px; }
        .search-bar input { padding: 10px; width: 300px; border-radius: 20px; border: 1px solid #ccc; }
        .categories { text-align: center; margin: 20px; }
        .categories a { margin: 0 10px; text-decoration: none; color: #333; font-weight: bold; }
        .categories a:hover { color: #e60023; }
        .pin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; padding: 20px; }
        .pin { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .pin:hover { transform: scale(1.05); }
        .pin img { width: 100%; height: 200px; object-fit: cover; }
        .pin-info { padding: 10px; }
        .pin-info h3 { margin: 0; font-size: 16px; }
        .pin-info p { color: #666; font-size: 14px; }
        @media (max-width: 600px) { .pin-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); } }
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
            <input type="text" name="query" placeholder="Search pins..." value="<?= htmlspecialchars($query) ?>">
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
                    <div class="pin-info">
                        <h3><?= $pin['title'] ?></h3>
                        <p>By <?= $pin['username'] ?></p>
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
