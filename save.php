<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// DB connection
$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("Database Connection Error");

$user_id = $_SESSION["user_id"];

// Fetch bookmarked recipes
$stmt = $conn->prepare("
    SELECT r.id, r.title, r.category, r.image
    FROM recipes r
    INNER JOIN bookmarks b ON r.id = b.recipe_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookmarks = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Saved Recipes</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="profile-container">
    <div class="user-recipes">
        <h2>Saved Recipes</h2><br>
        <a href="recipes.php" class="to">← Back to recipes</a>
        <?php if ($bookmarks->num_rows === 0): ?>
            <p>You haven't bookmarked any recipes yet.</p>
        <?php else: ?>
            <div class="recipes-grid">
                <?php while ($b = $bookmarks->fetch_assoc()): ?>
                    <?php
                    $img = $b['image'] ?: "images/placeholder.jpg";
                    $img = str_replace(
                        ["http://localhost:8080/foodbook/", "foodbook/"],
                        "",
                        $img
                    );
                    ?>
                    <div class="recipe-card">
                        <img src="<?= $img ?>" onerror="this.src='images/placeholder.jpg'">
                        <h3><?= htmlspecialchars($b['title']) ?></h3>
                        <p><?= htmlspecialchars($b['category']) ?></p>
                        <a href="recipe.php?id=<?= $b['id'] ?>" class="view-btn">View</a>
                        <a href="remove_bookmark.php?id=<?= $b['id'] ?>"
                           class="bookmark-btn bookmarked">★ Remove</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include "footer.php"; ?>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
