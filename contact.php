<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

$success = "";
$error = "";

// When form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name    = trim($_POST["name"]);
    $email   = trim($_POST["email"]);
    $message = trim($_POST["message"]);

    if ($name == "" || $email == "" || $message == "") {
        $error = "All fields are required.";
    } else {

        $conn = new mysqli("localhost", "root", "", "recipebook");

        if ($conn->connect_error) {
            die("DB connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("
            INSERT INTO contact_messages (name, email, message)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("sss", $name, $email, $message);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        $success = "Message saved successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>

<?php include "navbar.php"; ?>

<div class="contact-container">

    <h1>Contact Us</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="contact-form">

        <label>Your Name</label>
        <input type="text" name="name" required>

        <label>Your Email</label>
        <input type="email" name="email" required>

        <label>Your Message</label>
        <textarea name="message" required></textarea>

        <br><br>
        <button type="submit" class="btn-submit">Send Message</button>

    </form>
</div>
<div class="contact-info">
    <p><strong>Email:</strong> support@recipebook.com</p>
    <p><strong>Phone:</strong> 9858302475</p>
    <p><strong>Address:</strong> Tilottama-4, Rupandehi, Nepal</p>
</div>


<?php include "footer.php"; ?>

</body>
</html>
