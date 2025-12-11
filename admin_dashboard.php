<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// only admin an access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

// dashboard stats
$total_recipes = $conn->query("SELECT COUNT(*) AS c FROM recipes")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) AS c FROM recipes WHERE status='pending'")->fetch_assoc()['c'];
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];

// approve recipe
if (isset($_GET["approve"])) {
    $id = intval($_GET["approve"]);
    $conn->query("UPDATE recipes SET status='approved' WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}

// Reject / Delete Recipe
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    $conn->query("DELETE FROM recipes WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}

// Create Recipe
$message = "";
if (isset($_POST["create"])) {
    $title = $_POST["title"];
    $category = $_POST["category"];
    $description = $_POST["description"];
    $ingredients = $_POST["ingredients"];
    $steps = $_POST["steps"];
    $nutrition = $_POST["nutrition"];
    $video = $_POST["video_url"];

    // Image Upload
    $file = time() . "_" . $_FILES["image"]["name"];
    $path = "uploads/" . $file;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $path)) {

        $stmt = $conn->prepare("
            INSERT INTO recipes (title,category,description,ingredients,steps,nutrition,video_url,image,status,user_id)
            VALUES (?,?,?,?,?,?,?,?, 'approved',?)
        ");
        $admin_id = $_SESSION["user_id"];
        $stmt->bind_param("ssssssssi",
            $title, $category, $description, $ingredients, $steps, $nutrition, $video, $file, $admin_id
        );
        $stmt->execute();
        $message = "<div class='alert success'>✔ Recipe Created Successfully!</div>";
    } else {
        $message = "<div class='alert error'>Image Upload Failed</div>";
    }
}

// Fetch all recipes
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
<?php include "navbar.php";?>
<div class="admin-contain">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="admin_users.php">Users</a>
        <a href="recipes.php">Recipes</a> 
        <a href="create.php">Create</a>
        <!-- <a href="settings.php">Settings</a> -->
    </aside>
    <main class="content">
        <h1>📌 Dashboard Overview</h1>
        <div class="stats-box">
            <div class="stat"><h2><?= $total_recipes ?></h2><p>Total Recipes</p></div>
            <div class="stat"><h2><?= $pending ?></h2><p>Pending Approval</p></div>
            <div class="stat"><h2><?= $total_users ?></h2><p>Total Users</p></div>
        </div>
        <?= $message ?>
        <h2 id="recipes">All Recipes</h2>
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
                    <td><img src="uploads/<?= $row['image'] ?>" class="thumb"></td>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['creator'] ?: "Admin" ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <?php if ($row['status'] == "pending"): ?>
                            <a href="?approve=<?= $row['id'] ?>" class="approve">Approve</a> |
                        <?php endif; ?>

                        <a href="?delete=<?= $row['id'] ?>" class="delete">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>
</div>
<? include "footer.php";?>
</body>
</html>
