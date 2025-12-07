<?php
session_start();

$conn = new mysqli("localhost", "root", "", "recipebook");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    // CHECK IF EMAIL EXISTS
    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // CHECK PASSWORD (plain text for now)
        if ($password == $user["password"]) {

            // Save session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            // Role based redirect
            if ($user["role"] == "admin") {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: home.php");
                exit();
            }

        } else {
            $error = "Invalid email or password!";
        }

    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | RecipeBook</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>

<?php include "navbar.php"; ?>

<div class="login-container">
    <div class="login-box">

        <h2>Login</h2>
        <p class="subtitle">Access your RecipeBook account</p>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($error)) { ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php } ?>

        <!-- SUCCESS MESSAGE AFTER REGISTER -->
        <?php if (isset($_GET['registered'])) { ?>
            <div class="message success">Account created successfully. Please log in.</div>
        <?php } ?>

        <form method="POST" action="">
            
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>

            <button type="submit" class="btn-submit">Login</button>
        </form>

        <div class="login-bottom">
            Don't have an account?
            <a href="register.php">Register</a>
        </div>

    </div>
</div>

<?php include "footer.php"; ?>

</body>
</html>
