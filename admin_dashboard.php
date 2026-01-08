<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// only admin can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

$success = "";
$error = "";

/* APPROVE RECIPE */
if (isset($_GET["approve"])) {
    $id = (int) $_GET["approve"];

    if ($conn->query("UPDATE recipes SET status='approved' WHERE id=$id")) {
        $success = "Recipe approved successfully!";
    } else {
        $error = "Failed to approve recipe.";
    }
}

/* DELETE RECIPE */
if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];

    if ($conn->query("DELETE FROM recipes WHERE id=$id")) {
        $success = "Recipe deleted successfully!";
    } else {
        $error = "Failed to delete recipe.";
    }
}

// dashboard stats
$total_recipes = $conn->query("SELECT COUNT(*) AS c FROM recipes")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) AS c FROM recipes WHERE status='pending'")->fetch_assoc()['c'];
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];

// fetch recipes
$recipes = $conn->query("
    SELECT r.*, u.name AS creator
    FROM recipes r
    LEFT JOIN users u ON u.id = r.user_id
    ORDER BY r.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="footer.css">
</head>

<body>

<?php include "navbar.php"; ?>

<div class="admin-contain">

    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="admin_users.php">Users</a>
        <a href="recipes.php">Recipes</a>
        <a href="create.php">Create</a>
    </aside>

    <main class="content">

        <h1>📌 Dashboard Overview</h1>

        <?php if ($success): ?>
            <p style="color:green;"><?= $success ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>

        <div class="stats-box">
            <div class="stat"><h2><?= $total_recipes ?></h2><p>Total Recipes</p></div>
            <div class="stat"><h2><?= $pending ?></h2><p>Pending Approval</p></div>
            <div class="stat"><h2><?= $total_users ?></h2><p>Total Users</p></div>
        </div>

        <h2>All Recipes</h2>

        <table class="table">
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Creator</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php while ($row = $recipes->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img
                            src="http://localhost:8080/foodbook/<?= htmlspecialchars($row['image']) ?>"
                            class="thumb"
                            width="120"
                            height="120"
                            style="object-fit:cover;"
                            onerror="this.src='http://localhost:8080/foodbook/images/placeholder.jpg'"
                        >
                    </td>

                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $row['creator'] ?: "Admin" ?></td>
                    <td><?= ucfirst($row['status']) ?></td>

                    <td>
                        <?php if ($row['status'] == "pending"): ?>
                            <a href="admin_dashboard.php?approve=<?= $row['id'] ?>" class="approve">Approve</a> |
                        <?php endif; ?>

                        <a href="edit_recipe.php?id=<?= $row['id'] ?>">Edit</a> |
                        <a
                            href="admin_dashboard.php?delete=<?= $row['id'] ?>"
                            onclick="return confirm('Delete this recipe?');"
                            class="delete"
                        >Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

    </main>

</div>

<?php include "footer.php"; ?>

</body>
</html>
