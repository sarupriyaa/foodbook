<?php
session_start();
include "db.php";

$message = "";

// If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirmPassword = trim($_POST["confirmPassword"]);

    // Check password match
    if ($password !== $confirmPassword) {
        $message = "❌ Passwords do not match!";
    } else {
        
        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "❌ Email already registered!";
        } else {

            // Hash password
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $insert->bind_param("sss", $username, $email, $hashedPass);

            if ($insert->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $message = "❌ Registration failed!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register - RecipeBook</title>
    <link rel="stylesheet" href="login.css"> <!-- SAME DESIGN AS LOGIN -->
</head>

<body>

<?php include "navbar.php"; ?>

<div class="login-container">  <!-- same wrapper -->

    <div class="login-box">  <!-- same animated box -->

        <h2>Create Account</h2>
        <p class="subtitle">Join RecipeBook for free</p>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($message)) { ?>
            <div class="message error"><?= $message ?></div>
        <?php } ?>

        <form method="POST">

            <label>Username</label>
            <input type="text" name="username" placeholder="Choose a username" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Create a password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirmPassword" placeholder="Confirm password" required>

            <button type="submit" class="btn-submit">Create Account</button>

        </form>

        <div class="login-bottom">
            Already have an account?
            <a href="login.php">Login</a>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
