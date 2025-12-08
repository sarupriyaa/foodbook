<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);
$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT id, title, category, image FROM recipes";
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
<div class="search-container">
    <form action="search/search.php" method="GET" class="search-form">
        <input type="text" name="q" placeholder="Search for recipes..." required>
        <button type="submit">Search</button>
    </form>
</div>

<?php
if ($result->num_rows > 0) {
    echo "<div class='recipe-grid'>";
    while ($row = $result->fetch_assoc()) {
        echo "
        <div class='recipe-card'>
            <a href='recipe.php?id={$row['id']}'>
                <img src='http://localhost:8080/foodbook/{$row['image']}'
                     onerror=\"this.src='http://localhost:8080/foodbook/images/placeholder.jpg'\"> 
                <h3>{$row['title']}</h3>
                <p>{$row['category']}</p>
            </a>
        </div>
        ";
    }
    echo "</div>";
} else {
    echo "<p class='no-recipes'>No recipes found.</p>";
}
?>
<?php include "footer.php"; ?>
</body>
</html>
