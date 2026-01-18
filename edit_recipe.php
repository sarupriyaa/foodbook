<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

$user_id = $_SESSION["user_id"];
$role    = $_SESSION["role"] ?? "user";

$success = "";
$error   = "";

// recipe id required
if (!isset($_GET['id'])) die("No recipe selected.");
$id = (int) $_GET['id'];

// back link
$from = $_GET['from'] ?? "profile";
$back_link = ($from === "admin") ? "admin_dashboard.php" : "profile.php";

// fetch recipe
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$recipe) die("Recipe not found");

// permission
if ($role !== "admin" && $recipe["user_id"] != $user_id) {
    die("Not allowed");
}

// UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title       = $_POST['title'];
    $category    = $_POST['category'];
    $description = $_POST['description'];
    $video_url   = trim($_POST['video_url']);

    $status = ($role === "admin") ? $_POST["status"] : $recipe["status"];
    $image  = $recipe["image"];

    // image upload
    if (!empty($_FILES['image']['name'])) {
        $file = time() . "_" . basename($_FILES['image']['name']);
        $path = "uploads/" . $file;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
            $image = $path;
        }
    }

    if ($role === "admin") {
        $stmt = $conn->prepare("
            UPDATE recipes 
            SET title=?, category=?, description=?, video_url=?, status=?, image=?
            WHERE id=?
        ");
        $stmt->bind_param("ssssssi",
            $title, $category, $description, $video_url, $status, $image, $id
        );
    } else {
        $stmt = $conn->prepare("
            UPDATE recipes 
            SET title=?, category=?, description=?, video_url=?, image=?
            WHERE id=? AND user_id=?
        ");
        $stmt->bind_param("ssssiii",
            $title, $category, $description, $video_url, $image, $id, $user_id
        );
    }

    if ($stmt->execute()) {
        $success = "✔ Recipe updated successfully!";
    } else {
        $error = "❌ Update failed";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="footer.css">
</head>

<body>

<?php include "navbar.php"; ?>

<div class="edit-recipe-page">
    <main class="edit-recipe-content">

        <h1>Edit Recipe</h1>

        <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required>

            <label>Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($recipe['category']) ?>" required>

            <label>Description</label>
            <textarea name="description" rows="4" required><?= htmlspecialchars($recipe['description']) ?></textarea>

            <label>Current Image</label>
            <?php if ($recipe['image']): ?>
                <img src="<?= $recipe['image'] ?>">
            <?php endif; ?>

            <label>Upload New Image</label>
            <input type="file" name="image">

            <label>Video URL (YouTube embed)</label>
            <input type="text" name="video_url"
                   value="<?= htmlspecialchars($recipe['video_url'] ?? '') ?>"
                   placeholder="https://www.youtube.com/embed/XXXX">

            <?php if (!empty($recipe['video_url'])): ?>
                <iframe src="<?= htmlspecialchars($recipe['video_url']) ?>"
                        width="100%" height="250" allowfullscreen></iframe>
            <?php endif; ?>

            <?php if ($role === "admin"): ?>
                <label>Status</label>
                <select name="status">
                    <option value="approved" <?= $recipe['status']=="approved"?"selected":"" ?>>Approved</option>
                    <option value="pending" <?= $recipe['status']=="pending"?"selected":"" ?>>Pending</option>
                </select>
            <?php endif; ?>

            <button type="submit">Save</button>
            <button id = "back"><a href="<?= $back_link ?>">Back</a></button>

        </form>

    </main>
</div>

<?php include "footer.php"; ?>
</body>
</html>
