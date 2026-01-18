<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['id'])) die("No recipe ID received");

$user_id = intval($_SESSION['user_id']);
$recipe_id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND recipe_id = ?");
$stmt->bind_param("ii", $user_id, $recipe_id);

$stmt->execute();

if ($stmt->affected_rows > 0) {
    // success
} else {
    echo "Bookmark not found.";
}
$stmt->close();
$conn->close();
header("Location: recipes.php");
exit();
?>
