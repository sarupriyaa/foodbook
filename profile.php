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

// Fetch logged-in user info
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();


// ADMIN → sees all recipes
if ($role === "admin") {
    $recipes = $conn->query("SELECT * FROM recipes ORDER BY id DESC");
    $pending_count = $conn->query("SELECT COUNT(*) AS c FROM recipes WHERE status='pending'")
    ->fetch_assoc()['c'];
    $total_recipes = $conn->query("SELECT COUNT(*) AS c FROM recipes")
    ->fetch_assoc()['c'];

// USER → sees only own recipes
} else {
    $recipes = $conn->query("SELECT * FROM recipes WHERE user_id = $user_id ORDER BY id DESC");
    $total_recipes = $conn->query("SELECT COUNT(*) AS c FROM recipes WHERE user_id = $user_id")
    ->fetch_assoc()['c'];
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
        <a href="delete_recipe.php?id=<?= $r['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
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
                    <div class="recipe-card">
                        <img src="http://localhost:8080/foodbook/<?= $r['image'] ?>" alt="Recipe Image">
                        <h3><?= htmlspecialchars($r['title']) ?></h3>
                        <p>Category: <?= htmlspecialchars($r['category']) ?></p>
                        <p>Status: <strong><?= $r['status'] ?></strong></p>
                        <a href="recipe.php?id=<?= $r['id'] ?>" class="view-btn">View</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include "footer.php"; ?>
</body>
</html>
