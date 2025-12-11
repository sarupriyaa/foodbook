<?php
session_start();
include "db.php";

$message = "";

// When form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);
    $password = trim($_POST["password"]);
    $confirm = trim($_POST["confirmPassword"]);

    // Check password match
    if ($password !== $confirm) {
        $message = "<div class='message error'>❌ Passwords do not match!</div>";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $exists = $stmt->get_result();

        if ($exists->num_rows > 0) {
            $message = "<div class='message error'>❌ Email already registered!</div>";
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (name, email, address, password, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $name, $email, $address, $hashed);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $message = "<div class='message error'>❌ Registration failed!</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<?php include "navbar.php";?>
<div class="login-container">
    <div class="login-box">
        <h2>Create Account</h2>
        <p class="subtitle">Join RecipeBook today!</p>
        <?= $message ?>
        <form method="POST">

            <label>Name</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Address</label>
            <input type="text" name="address" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirmPassword" required>

            <button type="submit" class="btn-submit">Register</button>
        </form>
        <div class="login-bottom">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</div>
<?php include "footer.php";?>
</body>
</html>
