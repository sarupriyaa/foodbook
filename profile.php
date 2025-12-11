<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("Database Connection Error");

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];

// Use prepared statement to fetch logged-in user info
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_query = $user_stmt->get_result();
$user = $user_query->fetch_assoc();
$user_stmt->close();
$recipes = null;
$pending_count = 0;
$total_recipes = 0;
$bookmarks = null;
$bookmarks_stmt = null; // Initialize to null

// ADMIN → sees all recipes
if ($role === "admin") {
    // Fetch all recipes
    $recipes = $conn->query("SELECT * FROM recipes ORDER BY id DESC");

    // Fetch counts
    $pending_count = $conn->query("SELECT COUNT(*) AS c FROM recipes WHERE status='pending'")
        ->fetch_assoc()['c'];
    $total_recipes = $conn->query("SELECT COUNT(*) AS c FROM recipes")
        ->fetch_assoc()['c'];

// USER → sees only own recipes
} else {
    // Use prepared statement to fetch user's own recipes
    $recipes_stmt = $conn->prepare("SELECT * FROM recipes WHERE user_id = ? ORDER BY id DESC");
    $recipes_stmt->bind_param("i", $user_id);
    $recipes_stmt->execute();
    $recipes = $recipes_stmt->get_result();
    $recipes_stmt->close();

    // Use prepared statement for count
    $count_stmt = $conn->prepare("SELECT COUNT(*) AS c FROM recipes WHERE user_id = ?");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $total_recipes = $count_stmt->get_result()->fetch_assoc()['c'];
    $count_stmt->close();

    // Fetch user's bookmarks using a prepared statement (only for user role)
    $bookmarks_stmt = $conn->prepare("
        SELECT r.* FROM recipes r
        INNER JOIN bookmarks b ON r.id = b.recipe_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $bookmarks_stmt->bind_param("i", $user_id);
    $bookmarks_stmt->execute();
    $bookmarks = $bookmarks_stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="profile-container">
    <div class="profile-card">
        <h2>My Profile</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <!-- Corrected Address line syntax -->
        <p><strong>Address:</strong><?= htmlspecialchars($user['address'])?></p>
        <p><strong>Role:</strong> <?= strtoupper($role) ?></p>

        <?php if ($role === "admin"): ?>
            <p><strong>Total Recipes:</strong> <?= $total_recipes ?></p>
            <p><strong>Pending Approvals:</strong> <?= $pending_count ?></p>
            <a href="admin_approve_recipes.php" class="btn-manage">Manage Approvals</a>
        <?php else: ?>
            <p><strong>My Recipes:</strong> <?= $total_recipes ?></p>
            <a href="create.php" class="btn-create">➕ Create Recipe</a>
        <?php endif; ?>
        
        <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="user-recipes">
        <?php if ($role === "admin"): ?>
            <h2>All Recipes</h2>
        <?php else: ?>
            <h2>My Recipes</h2>
        <?php endif; ?>

        <?php if ($recipes->num_rows === 0): ?>
            <p>No recipes found.</p>
        <?php else: ?>
            <div class="recipes-grid">
                <?php while ($r = $recipes->fetch_assoc()): ?>
                    <?php
                    // Use a prepared statement for the in-loop bookmark check
                    $bookmark_check_stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND recipe_id = ?");
                    $bookmark_check_stmt->bind_param("ii", $user_id, $r['id']);
                    $bookmark_check_stmt->execute();
                    $bookmark_check = $bookmark_check_stmt->get_result();
                    $is_bookmarked = $bookmark_check->num_rows > 0;
                    $bookmark_check_stmt->close();
                    ?>
                    <div class="recipe-card">
                        <img src="http://localhost:8080/foodbook/<?= $r ?>" alt="Recipe Image">
                        <h3><?= htmlspecialchars($r['title']) ?></h3>
                        <p>Category: <?= htmlspecialchars($r['category']) ?></p>
                        <p>Status: <strong><?= $r['status'] ?></strong></p>
                        <a href="recipe.php?id=<?= $r['id'] ?>" class="view-btn">View</a>

                        <?php if ($role !== "admin"): ?>
                            <?php if ($is_bookmarked): ?>
                                <a href="remove_bookmark.php?id=<?= $r['id'] ?>" class="bookmark-btn bookmarked">★ Bookmarked</a>
                            <?php else: ?>
                                <a href="add_bookmark.php?id=<?= $r['id'] ?>" class="bookmark-btn">☆ Bookmark</a>
                            <?php endif; ?>
                            <a href="delete_recipe.php?id=<?= $r['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($role !== "admin"): ?>
    <div class="user-bookmarks">
        <h2>My Bookmarks</h2>
        <?php if ($bookmarks->num_rows === 0): ?>
            <p>No bookmarks yet.</p>
        <?php else: ?>
            <div class="recipes-grid">
                <?php while ($b = $bookmarks->fetch_assoc()): ?>
                    <div class="recipe-card">
                        <img src="http://localhost:8080/foodbook/<?= $b ?>" alt="Recipe Image">
                        <h3><?= htmlspecialchars($b['title']) ?></h3>
                        <p>Category: <?= htmlspecialchars($b['category']) ?></p>
                        <a href="recipe.php?id=<?= $b['id'] ?>" class="view-btn">View</a>
                        <a href="remove_bookmark.php?id=<?= $b['id'] ?>" class="bookmark-btn bookmarked">★ Remove Bookmark</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
<?php include "footer.php"; ?>
</body>
</html>
<?php
// Close remaining statements/connection if not already closed 
if ($bookmarks_stmt) {
    $bookmarks_stmt->close();
}
$conn->close();
?>
