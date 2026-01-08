<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

$sql = "
    SELECT id, title, category, image, status
    FROM recipes
    WHERE status = 'approved'
    ORDER BY id DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Recipes</title>
    <link rel="stylesheet" href="recipes.css">
</head>
<body>

<?php include "navbar.php"; ?>

<h1>All Recipes</h1>

<div class="grid">
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "
        <div class='recipe-card'>
            <a href='recipe.php?id={$row['id']}&from=recipes'>
                <img src=\"http://localhost:8080/foodbook/{$row['image']}\" 
                     alt='{$row['title']}'
                     onerror=\"this.src='images/placeholder.jpg'\"> 
                <h3>{$row['title']}</h3>
                <p>{$row['category']}</p>
            </a>
        </div>";
    }
} else {
    echo "<p>No recipes yet.</p>";
}
?>
</div>

<?php include "footer.php"; ?>
</body>
</html>
