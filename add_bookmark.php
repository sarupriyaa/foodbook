<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("Connection failed");

$user_id = $_SESSION['user_id'];
$recipe_id = intval($_GET['id']);

// Insert bookmark
$conn->query("INSERT IGNORE INTO bookmarks (user_id, recipe_id) VALUES ($user_id, $recipe_id)");

header("Location: recipes.php");
?>
