<?php
session_start();
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pinterest Clone</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: linear-gradient(135deg, #f5f7fa, #c3cfe2); display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); width: 300px; text-align: center; }
        .login-container h2 { color: #e60023; }
        .login-container input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        .login-container button { background: #e60023; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
        .login-container button:hover { background: #b3001b; }
        .error { color: red; }
        @media (max-width: 600px) { .login-container { width: 90%; } }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php" onclick="redirect('signup.php')">Signup</a></p>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
    </script>
</body>
</html>
