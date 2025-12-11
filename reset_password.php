<?php
session_start();
$conn = new mysqli("localhost", "root", "", "recipebook");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$message = "";
if (isset($_GET["token"])) {
    $token = $_GET["token"];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = $_POST["password"];
        $sql = "SELECT * FROM users WHERE reset_token='$token' LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $conn->query("UPDATE users SET password='$new_password', reset_token=NULL WHERE reset_token='$token'");
            $message = "Password updated successfully. <a href='login.php'>Login</a>";
        } else {
            $message = "Invalid or expired token!";
        }
    }
} else {
    $message = "No token provided!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Reset Password</h2>
            <?php if (!empty($message)) { echo "<div class='message'>$message</div>"; } ?>
            <form method="POST" action="">
                <label>New Password</label>
                <input type="password" name="password" required>
                <button type="submit" class="btn-submit">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html>