<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// ONLY ADMIN ACCESS
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// DB
$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");


// ========================
// STATS
// ========================
$total_recipes = $conn->query("SELECT COUNT(*) AS c FROM recipes")->fetch_assoc()['c'];
$pending_recipes = $conn->query("SELECT COUNT(*) AS c FROM recipes WHERE status='pending'")->fetch_assoc()['c'];
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];


// ========================
// HANDLE RECIPE APPROVAL
// ========================
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE recipes SET status='approved' WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $conn->query("DELETE FROM recipes WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}


// ========================
// HANDLE NEW RECIPE CREATION
// ========================
$create_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_recipe"])) {

    $title = $_POST["title"];
    $category = $_POST["category"];
    $description = $_POST["description"];
    $ingredients = $_POST["ingredients"];
    $steps = $_POST["steps"];
    $nutrition = $_POST["nutrition"];
    $video_url = $_POST["video_url"];

    // IMAGE upload
    $filename = time() . "_" . $_FILES["image"]["name"];
    $target = "uploads/" . $filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {

        $stmt = $conn->prepare("
            INSERT INTO recipes 
            (title, category, description, ingredients, steps, nutrition, video_url, image, status, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved', ?)
        ");

        $admin_id = $_SESSION["user_id"];
        $stmt->bind_param("ssssssssi", 
            $title, $category, $description, $ingredients, $steps, $nutrition, $video_url, $filename, $admin_id
        );

        if ($stmt->execute()) {
            $create_msg = "<p class='success'>✔ Recipe created successfully!</p>";
        } else {
            $create_msg = "<p class='error'>❌ Something went wrong.</p>";
        }

    } else {
        $create_msg = "<p class='error'>❌ Image upload failed.</p>";
    }
}


// ========================
// FETCH ALL RECIPES
// ========================
$recipes = $conn->query("SELECT r.*, u.name AS creator 
                         FROM recipes r 
                         LEFT JOIN users u ON u.id = r.user_id
                         ORDER BY r.id DESC");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; margin: 0; }
        .dashboard {
            width: 90%;
            margin: 20px auto;
        }

        h1 { text-align: center; }

        .stats-box {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat {
            flex: 1;
            background: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* CREATE FORM */
        .create-box {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            padding: 12px 16px;
            background: #ff7a00;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover { background: #e06800; }

        .success { color: green; }
        .error { color: red; }

        /* RECIPES TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        img.thumb {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
        .approve { color: green; font-weight: bold; }
        .reject { color: red; font-weight: bold; }

        @media(max-width: 768px) {
            .stats-box { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="dashboard">

    <h1>Admin Dashboard</h1>

    <!-- Stats -->
    <div class="stats-box">
        <div class="stat"><h2><?= $total_recipes ?></h2><p>Total Recipes</p></div>
        <div class="stat"><h2><?= $pending_recipes ?></h2><p>Pending Approval</p></div>
        <div class="stat"><h2><?= $total_users ?></h2><p>Total Users</p></div>
    </div>

    <!-- CREATE RECIPE -->
    <div class="create-box">
        <h2>Create New Recipe</h2>
        <?= $create_msg ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="create_recipe" value="1">

            <label>Title:</label>
            <input type="text" name="title" required>

            <label>Category:</label>
            <select name="category" required>
                <option>Breakfast</option>
                <option>Lunch</option>
                <option>Snacks</option>
                <option>Desserts</option>
            </select>

            <label>Description:</label>
            <textarea name="description" required></textarea>

            <label>Ingredients:</label>
            <textarea name="ingredients" required></textarea>

            <label>Steps:</label>
            <textarea name="steps" required></textarea>

            <label>Nutrition Info:</label>
            <textarea name="nutrition"></textarea>

            <label>Video URL (optional):</label>
            <input type="text" name="video_url">

            <label>Image:</label>
            <input type="file" name="image" required>

            <button type="submit">Create Recipe</button>
        </form>
    </div>


    <!-- RECIPE MANAGEMENT -->
    <h2>All Recipes</h2>

    <table>
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Category</th>
            <th>Creator</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($r = $recipes->fetch_assoc()): ?>
        <tr>
            <td><img class="thumb" src="uploads/<?= $r['image'] ?>"></td>
            <td><?= $r['title'] ?></td>
            <td><?= $r['category'] ?></td>
            <td><?= $r['creator'] ?: "Admin" ?></td>
            <td><?= ucfirst($r['status']) ?></td>
<td>
                <a href="edit_recipe.php?id=<?= $r['id'] ?>" class="edit-link">Edit</a>

</td>
            <td>
                <?php if ($r['status'] == "pending"): ?>
                    <a class="approve" href="?approve=<?= $r['id'] ?>">Approve</a> |
                    <a class="reject" href="?reject=<?= $r['id'] ?>">Reject</a>
                <?php else: ?>
                    <a class="reject" href="?reject=<?= $r['id'] ?>">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include "footer.php"; ?>

</body>
</html>
