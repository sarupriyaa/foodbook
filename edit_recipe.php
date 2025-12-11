<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

$id = intval($_GET['id']);

// Fetch recipe
$recipe = $conn->query("SELECT * FROM recipes WHERE id=$id")->fetch_assoc();
if (!$recipe) die("Recipe not found");

$message = "";

// Update recipe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $category = $_POST["category"];
    $description = $_POST["description"];
    $ingredients = $_POST["ingredients"];
    $steps = $_POST["steps"];
    $nutrition = $_POST["nutrition"];
    $video = $_POST["video_url"];
    $status = $_POST["status"];

    // Image upload
    if (!empty($_FILES["image"]["name"])) {
        $file = time() . "_" . $_FILES["image"]["name"];
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $file);
    } else {
        $file = $recipe['image'];
    }

    $stmt = $conn->prepare("
        UPDATE recipes SET 
            title=?, category=?, description=?, ingredients=?, steps=?, nutrition=?, video_url=?, image=?, status=?
        WHERE id=?
    ");

    $stmt->bind_param("sssssssssi",
        $title, $category, $description, $ingredients, $steps, $nutrition, $video, $file, $status, $id
    );

    if ($stmt->execute()) {
        $message = "<div class='alert success'>Updated Successfully!</div>";
        $recipe = $conn->query("SELECT * FROM recipes WHERE id=$id")->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-container">
    <?php include "admin_sidebar.php"; ?>
    <main class="content">
        <h1>Edit Recipe</h1>
        <?= $message ?>
        <form method="POST" enctype="multipart/form-data" class="card">
            <label>Title</label>
            <input type="text" name="title" value="<?= $recipe['title'] ?>" required>

            <label>Category</label>
            <select name="category">
                <option <?= $recipe['category']=="Breakfast"?"selected":"" ?>>Breakfast</option>
                <option <?= $recipe['category']=="Lunch"?"selected":"" ?>>Lunch</option>
                <option <?= $recipe['category']=="Snacks"?"selected":"" ?>>Snacks</option>
                <option <?= $recipe['category']=="Desserts"?"selected":"" ?>>Desserts</option>
            </select>

            <label>Description</label>
            <textarea name="description" required><?= $recipe['description'] ?></textarea>

            <label>Ingredients</label>
            <textarea name="ingredients"><?= $recipe['ingredients'] ?></textarea>

            <label>Steps</label>
            <textarea name="steps"><?= $recipe['steps'] ?></textarea>

            <label>Nutrition</label>
            <textarea name="nutrition"><?= $recipe['nutrition'] ?></textarea>

            <label>Video URL</label>
            <input type="text" name="video_url" value="<?= $recipe['video_url'] ?>">

            <label>Current Image:</label><br>
            <img src="uploads/<?= $recipe['image'] ?>" style="width:120px;border-radius:6px;"><br><br>

            <label>Change Image:</label>
            <input type="file" name="image">

            <label>Status</label>
            <select name="status">
                <option value="approved" <?= $recipe['status']=="approved"?"selected":"" ?>>Approved</option>
                <option value="pending" <?= $recipe['status']=="pending"?"selected":"" ?>>Pending</option>
            </select>
            <button type="submit">Update</button>
        </form>
    </main>
</div>

</body>
</html>
