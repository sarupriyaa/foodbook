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
$role = $_SESSION["role"];
$message = "";

// Ensure an ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php"); // Redirect to appropriate main page
    exit();
}

$recipe_id = $_GET['id'];

// Fetch the recipe details
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe_result = $stmt->get_result();
$recipe = $recipe_result->fetch_assoc();

// Check authorization (owner or admin)
if (!$recipe || ($recipe['user_id'] != $user_id && $role !== 'admin')) {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars($_POST['title']);
    $category = htmlspecialchars($_POST['category']);
    $description = htmlspecialchars($_POST['description']);
    $ingredients = htmlspecialchars($_POST['ingredients']);
    $steps = htmlspecialchars($_POST['steps']);
    $nutrition = htmlspecialchars($_POST['nutrition']);
    $video_url = $recipe;
    $image_path = $recipe; 

    // Handle image upload if a new file is provided
    if (isset($_FILES) && $_FILES['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Make sure this directory exists and is writable
        $image_name = basename($_FILES["name"]);
        $target_file = $target_dir . uniqid() . "_" . $image_name; // Use unique ID to prevent overwrites
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Basic image validation (check file type, size, etc. for better security)
        if (move_uploaded_file($_FILES["tmp_name"], $target_file)) {
            // Success: update the image path, optionally delete old file
            $image_path = $target_file;
            // unlink($recipe); // Uncomment to delete the old file
        } else {
            $message .= "Error uploading new image. ";
        }
    }

    // Update data in the database
    $update_stmt = $conn->prepare("UPDATE recipes SET title = ?, category = ?, description = ?, ingredients =? steps = ?, nutrition = ?, video = ? image = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $title, $category, $description, $ingredients, $steps, $nutrition, $video_url, $image_path, $recipe_id);

    if ($update_stmt->execute()) {
        $message = "Recipe updated successfully!";
        // Re-fetch data to show current state after update
        $recipe['title'] = $title;
        $recipe['category'] = $category;
        $recipe['description'] = $description;
        $recipe['ingredients'] = $ingredients;
        $recipe['steps'] = $steps;
        $recipe['nutrition'] = $nutrition;
        $recipe = $video_url;
        $recipe = $image_path;
    } else {
        $message .= "Error updating recipe: " . $conn->error;
    }
    $update_stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="profile.css"> <!-- Reuse the CSS file -->
</head>
<body>
<?php include "navbar.php"; ?>

<div class="edit-container">
    <div class="edit-card">
        <a href="admin_dashboard.php">Back to Dashboard</a>
        <h2>Edit Recipe: <?= htmlspecialchars($recipe['title']) ?></h2>

        <?php if ($message): ?>
            <p class="message <?= (strpos($message, 'Error') !== false) ? 'error' : 'success' ?>">
                <?= $message ?>
            </p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="edit-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required>
            </div>
            <div class="edit-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($recipe['category']) ?>" required>
            </div>
            <div class="edit-group">
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" value="<?= htmlspecialchars($recipe['description']) ?>" required>
            </div>
            <div class="edit-group">
                <label for="ingredients">Ingredients:</label>
                <textarea type="text" id="ingredients" name="ingredients" value="<?= htmlspecialchars($recipe['ingredients']) ?>" required></textarea>
            </div>
            <div class="edit-group">
                <label for="steps">Steps:</label>
                <textarea type="text" id="steps" name="steps" value="<?= htmlspecialchars($recipe['steps']) ?>" required></textarea>
            </div>
            <div class="edit-group">
                <label for="nutrition">Nutrition:</label>
                <textarea type="text" id="nutrition" name="nutrition" value="<?= htmlspecialchars($recipe['nutrition']) ?>" required></textarea>
            </div>
            <div class="edit-group">
                <label for="image">Upload New Image (optional):</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            <div class="edit-group">
                <label for="video">Upload New Video (optional):</label>
                <input type="url" id="video" name="video" accept="video_url">
            </div>
            <button type="submit" class="btn-update">Update Recipe</button>
            <!-- <div class="edit-group">
                <label>Current Image:</label>
                <?php if (!empty($recipe)): ?>
                    <img src="http://localhost:8080/foodbook/<?= htmlspecialchars($recipe) ?>" alt="Current Image" style="max-width: 150px; height: auto;">
                <?php else: ?>
                    <p>No image uploaded.</p>
                <?php endif; ?>
            </div>
             -->
           
        </form>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>
