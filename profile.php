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
$role    = $_SESSION["role"];

// Fetch user
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Recipes
if ($role === "admin") {

    $recipes = $conn->query("SELECT * FROM recipes ORDER BY id DESC");

    $pending_count = $conn->query("
        SELECT COUNT(*) AS c FROM recipes WHERE status='pending'
    ")->fetch_assoc()['c'];

    $total_recipes = $conn->query("
        SELECT COUNT(*) AS c FROM recipes
    ")->fetch_assoc()['c'];

} else {

    $recipes_stmt = $conn->prepare("
        SELECT * FROM recipes WHERE user_id=? ORDER BY id DESC
    ");
    $recipes_stmt->bind_param("i", $user_id);
    $recipes_stmt->execute();
    $recipes = $recipes_stmt->get_result();
    $recipes_stmt->close();

    $count_stmt = $conn->prepare("
        SELECT COUNT(*) AS c FROM recipes WHERE user_id=?
    ");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $total_recipes = $count_stmt->get_result()->fetch_assoc()['c'];
    $count_stmt->close();
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

        <?php if ($role !== "admin"): ?>
            <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
        <?php endif; ?>

        <p><strong>Role:</strong> <?= strtoupper($role) ?></p>

        <?php if ($role === "admin"): ?>
            <p><strong>Total Recipes:</strong> <?= $total_recipes ?></p>
            <p><strong>Pending Approvals:</strong> <?= $pending_count ?></p>
            <a href="admin_approve_recipes.php" class="btn-manage">Manage Approvals</a>
            <a href="approved.php" class='btn-manage'>Users Recipes</a>
        <?php else: ?>
            <p><strong>My Recipes:</strong> <?= $total_recipes ?></p>
            <a href="create.php" class="btn-create">Create Recipe</a>
        <?php endif; ?>

        <a href="save.php" class="btn-edit">Saved Recipes</a>
        <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
        <!-- <a href="logout.php" class="logout-btn">Logout</a> -->
    </div>

    <div class="user-recipes">
        <h2><?= $role === "admin" ? "All Recipes" : "My Recipes" ?></h2>

        <?php if ($recipes->num_rows === 0): ?>
            <p>No recipes found.</p>
        <?php else: ?>
        <div class="recipes-grid">

            <?php while ($r = $recipes->fetch_assoc()): ?>
                <?php
                $img = $r['image'] ?: "images/placeholder.jpg";
                $img = str_replace(
                    ["http://localhost:8080/foodbook/", "foodbook/"],
                    "",
                    $img
                );
                ?>
                <div class="recipe-card">
                    <img src="<?= $img ?>">
                    <h3><?= htmlspecialchars($r['title']) ?></h3>
                    <p><?= htmlspecialchars($r['category']) ?></p>
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
<?php $conn->close(); ?>
