<?php
session_start();
$conn = new mysqli("localhost", "root", "", "recipebook");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $token = bin2hex(random_bytes(50)); // generate reset token
        $conn->query("UPDATE users SET reset_token='$token' WHERE email='$email'");

        // Normally you send email. For demo, just show link:
        $reset_link = "http://localhost/recipebook/reset_password.php?token=$token";
        $message = "Password reset link: <a href='$reset_link'>$reset_link</a>";
    } else {
        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Forgot Password</h2>
            <p>Enter your email to reset password</p>

            <?php if (!empty($message)) { echo "<div class='message'>$message</div>"; } ?>

            <form method="POST" action="">
                <label>Email</label>
                <input type="email" name="email" required>
                <button type="submit" class="btn-submit">Send Reset Link</button>
            </form>
        </div>
    </div>
</body>
</html>