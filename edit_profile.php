<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("Database Connection Error");

$user_id = $_SESSION["user_id"];
$message = "";

// Fetch logged-in user info
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    
    // Check if email already exists for another user (optional but good practice)
    $check_email_query = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_email_query->bind_param("si", $email, $user_id);
    $check_email_query->execute();
    $result = $check_email_query->get_result();

    if ($result->num_rows > 0) {
        $message = "Error: This email is already in use by another account.";
    } else {
        // Update user data in the database
        $update_query = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $update_query->bind_param("ssi", $name, $email, $user_id);

        if ($update_query->execute()) {
            $message = "Profile updated successfully!";
            // Update session variables if needed (optional)
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            // Re-fetch user data to reflect changes immediately
            $user['name'] = $name;
            $user['email'] = $email;
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
        $update_query->close();
    }
    $check_email_query->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
        </head>
<body>
<?php include "navbar.php"; ?>
<div class="edit-container">
    <div class="edit-card">
        <h2>Edit Profile</h2>
        <?php if ($message): ?>
            <p class="message <?= (strpos($message, 'Error') !== false) ? 'error' : 'success' ?>">
                <?= $message ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <div class="edit-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="edit-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" class="btn-update">Update Profile</button>
            <a href="profile.php">Back to Profile</a>
        </form>
    </div>
</div>
<?php include "footer.php"; ?>
</body>
</html>
