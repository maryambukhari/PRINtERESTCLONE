<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $user_id = $_SESSION['user_id'];
    
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024;
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $file_name;

        if (!in_array($file_type, $allowed_types)) {
            $error = "Only JPEG, PNG, and GIF files are allowed.";
        } elseif ($file_size > $max_size) {
            $error = "File size exceeds 5MB limit.";
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $stmt = $pdo->prepare("INSERT INTO pins (user_id, title, description, image_url, category_id) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $title, $description, $target_file, $category_id])) {
                header("Location: profile.php");
                exit;
            } else {
                $error = "Failed to save pin to database.";
                unlink($target_file);
            }
        } else {
            $error = "Failed to upload image. Check folder permissions.";
        }
    } else {
        $error = "No image uploaded or upload error occurred.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Pin - Pinterest Clone</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(90deg, #e60023, #b3001b);
            padding: 15px;
            color: white;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .navbar a {
            color: white;
            margin: 0 20px;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s, transform 0.2s;
        }
        .navbar a:hover {
            color: #ffd700;
            transform: scale(1.1);
        }
        .upload-container {
            max-width: 500px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .upload-container h2 {
            color: #e60023;
            text-align: center;
            margin-bottom: 20px;
        }
        .upload-container input, .upload-container textarea, .upload-container select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .upload-container input:focus, .upload-container textarea:focus, .upload-container select:focus {
            border-color: #e60023;
            outline: none;
        }
        .upload-container textarea {
            resize: vertical;
            min-height: 100px;
        }
        .upload-container button {
            background: #e60023;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s, transform 0.2s;
        }
        .upload-container button:hover {
            background: #b3001b;
            transform: scale(1.02);
        }
        .error {
            color: #e60023;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .upload-container {
                width: 90%;
                padding: 20px;
            }
            .navbar a {
                margin: 0 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="board.php">Boards</a>
        <a href="logout.php" onclick="redirect('logout.php')">Logout</a>
    </div>
    <div class="upload-container">
        <h2>Upload a Pin</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Pin Title" required>
            <textarea name="description" placeholder="Description (optional)"></textarea>
            <select name="category_id" required>
                <option value="" disabled selected>Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="image" accept="image/jpeg,image/png,image/gif" required>
            <button type="submit">Upload Pin</button>
        </form>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
    </script>
</body>
</html>
