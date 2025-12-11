<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

$conn = new mysqli("localhost", "root", "", "recipebook");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// fetch only approved recipes
$sql = "
    SELECT id, title, category, image 
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
<div style="text-align:center; margin-top:20px;">
    <form action="search/search.php" method="GET">
        <input type="text" name="q" placeholder="Search recipes..." 
               style="padding:10px; width:260px; border-radius:6px; border:2px solid #ccc; font-size:16px;">
        <button type="submit" 
                style="padding:10px 20px; background:#6a1b9a; color:white; border:none; border-radius:6px; cursor:pointer;">
            Search
        </button>
    </form>
</div>
<div class="grid">
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "
        <div class='recipe-card'>
            <a href='recipe.php?id={$row['id']}' style='text-decoration:none; color:inherit;'>

                <img src='http://localhost:8080/foodbook/{$row['image']}'
                     alt='{$row['title']}'
                     onerror=\"this.src='http://localhost:8080/foodbook/images/placeholder.jpg'\"> 
                <h3>{$row['title']}</h3>
                <p>{$row['category']}</p>
            </a>
        </div>
        ";}
} else {
    echo "<p class='no-recipes'>No approved recipes available yet.</p>";
}
?>
</div>
<?php include "footer.php"; ?>
</body>
</html>
