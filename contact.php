<?php
session_start();

// SHOW ERRORS
error_reporting(E_ALL);
ini_set("display_errors", 1);

// SUCCESS & ERROR MESSAGES
$success = "";
$error = "";

// PHPMailer Imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// When form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form values
    $name    = htmlspecialchars($_POST["name"]);
    $email   = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);

    // 1️⃣ SAVE MESSAGE TO DATABASE
    $conn = new mysqli("localhost", "root", "", "recipebook");

    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    $stmt->execute();
    $stmt->close();

    // 2️⃣ SEND EMAIL VIA PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // YOUR EMAIL + APP PASSWORD
        $mail->Username   = 'yourgmail@gmail.com';     // <-- CHANGE THIS
        $mail->Password   = 'YOUR_APP_PASSWORD';       // <-- CHANGE THIS

        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Email addresses
        $mail->setFrom($email, $name);
        $mail->addAddress('yourgmail@gmail.com');      // <-- Admin receives this

        // Email content
        $mail->Subject = "New Contact Message from RecipeBook";
        $mail->Body =
"Name: $name
Email: $email

Message:
$message";

        $mail->send();
        $success = "Your message has been sent successfully!";

    } catch (Exception $e) {
        $error = "Message could not be sent. Error: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us • RecipeBook</title>
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

    <form class="contact-form" method="POST">

        <label>Your Name</label>
        <input type="text" name="name" required>

        <label>Your Email</label>
        <input type="email" name="email" required>

        <label>Your Message</label>
        <textarea name="message" required></textarea><br><br>

        <button type="submit" class="btn-submit">Send Message</button>
    </form>
</div>

<div class="contact-info">
    <p><strong>Email:</strong> support@recipebook.com</p>
    <p><strong>Phone:</strong> +1 555 123 4567</p>
    <p><strong>Address:</strong> Tilottama-4, Rupandehi, Nepal</p>
</div>

<div class="map-box">
    <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.019505034529!2d-122.41941528468138!3d37.774929779759744!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8085808c1e32c7bd%3A0xced3ba0b04b2d30!2sFood%20City!5e0!3m2!1sen!2sus!4v1700000000000"
        width="100%" height="350" style="border:0;" allowfullscreen loading="lazy">
    </iframe>
</div>

<?php include "footer.php"; ?>

</body>
</html>
