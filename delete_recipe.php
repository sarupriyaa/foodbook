<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("Database Connection Error");

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];

// Ensure an ID is provided
if (!isset($_GET['id'])) {
    header("Location: profile.php");
    exit();
}
$recipe_id = $_GET['id'];

// Check ownership before deletion
$check_stmt = $conn->prepare("SELECT user_id FROM recipes WHERE id = ?");
$check_stmt->bind_param("i", $recipe_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$recipe = $check_result->fetch_assoc();

// If the recipe exists and the user is authorized (owner or admin)
if ($recipe && ($recipe['user_id'] == $user_id || $role === 'admin')) {
    $delete_stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
    $delete_stmt->bind_param("i", $recipe_id);
    $delete_stmt->execute();
    $delete_stmt->close();
}
$check_stmt->close();
$conn->close();

// Redirect back to the profile page
header("Location: profile.php");
exit();
?>
