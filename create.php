<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title       = $_POST["title"];
    $category    = $_POST["category"];
    $description = $_POST["description"];
    $ingredients = $_POST["ingredients"];
    $steps       = $_POST["steps"];
    $nutrition   = $_POST["nutrition"];
    $video_url   = $_POST["video_url"];
    $user_id     = $_SESSION["user_id"];
    $role        = $_SESSION["role"];

    // ✅ STATUS LOGIC
    $status = ($role === "admin") ? "approved" : "pending";

    // image upload
    $image_path = "";
    if (!empty($_FILES["image"]["name"])) {
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_dir = "uploads/";
        $image_path = $target_dir . $image_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    // insert query
    $stmt = $conn->prepare("
        INSERT INTO recipes 
        (title, category, image, description, ingredients, steps, video_url, nutrition, status, user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssssssi",
        $title,
        $category,
        $image_path,
        $description,
        $ingredients,
        $steps,
        $video_url,
        $nutrition,
        $status,
        $user_id
    );

    if ($stmt->execute()) {
        if ($role === "admin") {
            $message = "✔ Recipe published successfully!";
        } else {
            $message = "✔ Recipe submitted! Waiting for admin approval.";
        }
    } else {
        $message = "❌ Error saving recipe.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Recipe</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-container">
    <h1>Create New Recipe</h1>

    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="create-form">

        <label>Recipe Title</label>
        <input type="text" name="title" required>

        <label>Category</label>
        <select name="category" required>
            <option value="">Select category</option>
            <option>Breakfast</option>
            <option>Lunch</option>
            <option>Snacks</option>
            <option>Desserts</option>
        </select>

        <label>Recipe Image</label>
        <input type="file" name="image" required>

        <label>Description</label>
        <textarea name="description" required></textarea>

        <label>Ingredients</label>
        <textarea name="ingredients" required></textarea>

        <label>Steps</label>
        <textarea name="steps" required></textarea>

        <label>Nutrition</label>
        <textarea name="nutrition"></textarea>

        <label>Video URL (optional)</label>
        <input type="text" name="video_url">

        <button type="submit" class="btn-create">
            <?= ($_SESSION["role"] === "admin") ? "Publish Recipe" : "Submit for Approval" ?>
        </button>

    </form>
</div>

<?php include "footer.php"; ?>
</body>
</html>
