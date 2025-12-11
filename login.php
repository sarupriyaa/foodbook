<?php
session_start();
include "db.php";
$error = "";

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        // Set session
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] == "admin") {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: home.php");
        }
        exit();
    } else {
        $error = "<div class='message error'>Invalid login details</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<?php include "navbar.php";?>
<div class="login-container">
    <div class="login-box">
        <h2>Login</h2>
        <p class="subtitle">Access your RecipeBook account</p>
        <?php
        if (isset($_GET["registered"])) {
            echo "<div class='message success'>Account created successfully! Please log in.</div>";
        }
        echo $error;
        ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn-submit">Login</button>
        </form>
        <div class="login-bottom">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </div>
</div>
<?php include "footer.php";?>
</body>
</html>
