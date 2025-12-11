<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recipebook";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Log the error securely instead of exposing internal details to the user
    error_log("Database Connection Error: " . $conn->connect_error);
    die("A database connection error occurred. Please try again later.");
}

$user_id = $_SESSION["user_id"];
$message = "";
$message_class = "";

// Fetch logged-in user info (needed for initial form values and after updates)
$user = null; // Initialize user variable

// Function to fetch user data safely
function fetch_user_data($conn, $user_id) {
    $stmt = $conn->prepare("SELECT name, address, email FROM users WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return null;
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();
    return $user_data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = htmlspecialchars(trim($_POST['name']));
    $address = htmlspecialchars(trim($_POST['address']));

    if (empty($name) || empty($address)) {
        $message = "Name and address fields cannot be empty.";
        $message_class = "error";
    } else {
        // Update user data in the database
        $update_query = $conn->prepare("UPDATE users SET name = ?, address = ? WHERE id = ?");

        if ($update_query === false) {
             error_log("Prepare failed: " . $conn->error);
             $message = "An error occurred during preparation.";
             $message_class = "error";
        } else {
            $update_query->bind_param("ssi", $name, $address, $user_id);

            if ($update_query->execute()) {
                $message = "Profile updated successfully!";
                $message_class = "success";
                
                // Update session variables (optional but useful)
                $_SESSION['name'] = $name;
                $_SESSION['address'] = $address;

                // Re-fetch user data to reflect changes immediately in the form's value attributes
                $user = fetch_user_data($conn, $user_id);

            } else {
                // Log the exact database error for debugging purposes
                error_log("Error updating profile: " . $update_query->error);
                $message = "Error updating profile. Please try again.";
                $message_class = "error";
            }
            $update_query->close();
        }
    }
}

// Fetch user data if it hasn't been fetched/refetched already
if ($user === null) {
    $user = fetch_user_data($conn, $user_id);
}

// Ensure $user is not null before closing the connection
if ($user === null) {
    // Handle a scenario where the user ID in the session is invalid
    session_unset();
    session_destroy();
    header("Location: login.php?error=invalid_user");
    $conn->close();
    exit();
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
        <a href="profile.php">Back to Profile</a>
        <?php if ($message): ?>
            <p class="message <?= $message_class ?>">
                <?= $message ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <div class="edit-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>
            <div class="edit-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn-update">Update Profile</button>
        </form>
    </div>
</div>
<?php include "footer.php"; ?>
</body>
</html>
