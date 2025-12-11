<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

// handle approve
if (isset($_GET["approve"])) {
    $id = intval($_GET["approve"]);
    $conn->query("UPDATE recipes SET status='approved' WHERE id=$id");
    header("Location: admin_approve_recipes.php");
    exit();
}

// handle delete
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    $conn->query("DELETE FROM recipes WHERE id=$id");
    header("Location: admin_approve_recipes.php");
    exit();
}

$pending = $conn->query("SELECT r.*, u.name AS creator 
    FROM recipes r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.status='pending'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Recipes</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="admin-container">
    <h1>Pending Recipe Approvals</h1>
    <?php if ($pending->num_rows == 0): ?>
        <p>No pending recipes.</p>
    <?php else: ?>
        <div class="recipes-grid">
        <?php while ($r = $pending->fetch_assoc()): ?>
            <div class="recipe-card">
                <img src="<?= htmlspecialchars($r['image']) ?>">

                <h3><?= htmlspecialchars($r['title']) ?></h3>
                <p>Category: <?= htmlspecialchars($r['category']) ?></p>
                <p>Submitted by: <strong><?= htmlspecialchars($r['creator']) ?></strong></p>

                <a class="btn-approve" href="admin_approve_recipes.php?approve=<?= $r['id'] ?>">Approve</a>
                <a class="btn-delete" href="admin_approve_recipes.php?delete=<?= $r['id'] ?>">Delete</a>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
<?php include "footer.php"; ?>
</body>
</html>
