<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Connection Failed");

// CHECK QUERY
if (!isset($_GET['q']) || trim($_GET['q']) == "") {
    $q = "";
} else {
    $q = trim($_GET['q']);
}

$qEscaped = $conn->real_escape_string($q);

// SEARCH ONLY APPROVED RECIPES
$sql = "
    SELECT * FROM recipes 
    WHERE status = 'approved' 
    AND (title LIKE '%$qEscaped%' 
        OR category LIKE '%$qEscaped%' 
        OR description LIKE '%$qEscaped%')
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
    <link rel="stylesheet" href="../search.css">
</head>
<body>

<?php include "../navbar.php"; ?>

<div class="search-page">

    <h1>Search Results for: <span>"<?= htmlspecialchars($q) ?>"</span></h1>

    <div class="recipe-grid">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { ?>

                <div class="recipe-card">
                    <a href="../recipe.php?id=<?= $row['id'] ?>">
                        <img src="http://localhost:8080/foodbook/<?= $row['image'] ?>"
                             onerror="this.src='http://localhost:8080/foodbook/images/placeholder.jpg'">

                        <h3><?= htmlspecialchars($row['title']) ?></h3>
                        <p><?= htmlspecialchars($row['category']) ?></p>
                    </a>
                </div>

        <?php }
        } else {
            echo "<p class='no-results'>No recipes matched your search.</p>";
        }
        ?>
    </div>

</div>

<?php include "../footer.php"; ?>
</body>
</html>
