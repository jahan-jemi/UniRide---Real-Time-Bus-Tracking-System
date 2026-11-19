<?php
session_start();
require '../backend/config.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, password_hash FROM admins WHERE username='$username'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        if (password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin'] = $admin['id'];
            header('Location: dashboard.php'); 
            exit;
        }
    }
    $message = "Wrong username or password";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UniRide Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card">
        <h1>🚌 UniRide Admin</h1>
        <form method="POST">
            <input name="username" placeholder="Username" required>
            <input name="password" type="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>
        <p class="msg"><?php echo htmlspecialchars($message); ?></p>
        <p class="hint">Default: admin / admin123</p>
    </div>
</body>
</html>