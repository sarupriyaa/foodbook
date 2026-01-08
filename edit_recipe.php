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
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

$user_id = $_SESSION["user_id"];
$role    = $_SESSION["role"] ?? "user";

$success = "";
$error = "";

// recipe id required
if (!isset($_GET['id'])) die("No recipe selected.");

$id = (int) $_GET['id'];

// where user came from
$from = $_GET['from'] ?? "profile";
$back_link = ($from === "admin") ? "admin_dashboard.php" : "profile.php";

// fetch recipe
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$recipe) die("Recipe not found.");

// ONLY ADMIN OR OWNER CAN EDIT
if ($role !== "admin" && $recipe["user_id"] != $user_id) {
    die("You are not allowed to edit this recipe.");
}

// UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title       = $_POST['title'];
    $category    = $_POST['category'];
    $description = $_POST['description'];

    // user cannot change status
    $status = ($role === "admin") ? $_POST["status"] : $recipe["status"];

    // start with old image
    $image = $recipe["image"];

    // image upload
    if (!empty($_FILES['image']['name'])) {

        // keep only filename
        $file = time() . "_" . basename($_FILES['image']['name']);
        $path = "uploads/" . $file;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
            $image = "uploads/" . $file; // store path
        }
    }

    // PREPARED UPDATE
    if ($role === "admin") {
        $sql = "
            UPDATE recipes 
            SET title=?, category=?, description=?, status=?, image=?
            WHERE id=?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $title, $category, $description, $status, $image, $id);
    } else {
        $sql = "
            UPDATE recipes 
            SET title=?, category=?, description=?, image=?
            WHERE id=? AND user_id=?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $title, $category, $description, $image, $id, $user_id);
    }

    if ($stmt->execute()) {
        $success = "Recipe updated successfully!";
    } else {
        $error = "Failed to update recipe.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="edit_recipe.css">
</head>

<body>

<?php include "navbar.php"; ?>

<div class="admin-contain">
    <main class="content">

        <h1>Edit Recipe</h1>

        <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
        <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required>

            <label>Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($recipe['category']) ?>" required>

            <label>Description</label>
            <textarea name="description" rows="4" required><?= htmlspecialchars($recipe['description']) ?></textarea>

            <?php if ($role === "admin"): ?>
                <label>Status</label>
                <select name="status">
                    <option value="approved" <?= $recipe['status']=="approved"?"selected":"" ?>>Approved</option>
                    <option value="pending" <?= $recipe['status']=="pending"?"selected":"" ?>>Pending</option>
                </select>
            <?php endif; ?>

            <label>Current Image</label><br>

            <?php if ($recipe['image']): ?>
                <img src="<?= $recipe['image'] ?>" width="180"><br><br>
            <?php endif; ?>

            <label>Upload New Image (optional)</label>
            <input type="file" name="image">

            <br><br>

            <button type="submit">Save</button>
            <a href="<?= $back_link ?>">Back</a>

        </form>

    </main>
</div>

<?php include "footer.php"; ?>
</body>
</html>
