<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

/* ONLY ADMIN */
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

$success = "";
$error   = "";

/* DELETE RECIPE */
if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];

    if ($conn->query("DELETE FROM recipes WHERE id = $id")) {
        $success = "Recipe deleted successfully!";
    } else {
        $error = "Failed to delete recipe.";
    }
}

/* FETCH ONLY APPROVED RECIPES CREATED BY USERS (NOT ADMIN) */
$recipes = $conn->query("
    SELECT r.*, u.name AS creator
    FROM recipes r
    INNER JOIN users u ON u.id = r.user_id
    WHERE r.status = 'approved'
      AND u.role = 'user'
      AND r.user_id IS NOT NULL
    ORDER BY r.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Approved Recipes</title>
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="login.css">

</head>

<body>

<?php include "navbar.php"; ?>

<div class="admin-user-approved">

    <main class="content">

        <h1>Approved User Recipes</h1>

        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <table class="table">
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php if ($recipes->num_rows === 0): ?>
                <tr>
                    <td colspan="5" class="empty">
                        No approved user recipes found.
                    </td>
                </tr>
            <?php endif; ?>

            <?php while ($row = $recipes->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img
                            src="http://localhost:8080/foodbook/<?= htmlspecialchars($row['image']) ?>"
                            class="thumb"
                            onerror="this.src='http://localhost:8080/foodbook/images/placeholder.jpg'"
                        >
                    </td>

                    <td><?= htmlspecialchars($row['title']) ?></td>

                    <td><?= htmlspecialchars($row['creator']) ?></td>

                    <td>
                        <span class="approved">Approved</span>
                    </td>

                    <td>
                        <a href="edit_recipe.php?id=<?= $row['id'] ?>&from=admin">
                            Edit
                        </a>
                        |
                        <a
                            href="?delete=<?= $row['id'] ?>"
                            onclick="return confirm('Delete this recipe?');"
                            class="delete"
                        >
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        </table>

    </main>
</div>

<?php include "footer.php"; ?>

</body>
</html>

<?php
$conn->close();
?>
