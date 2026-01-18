<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="recipes.css">
</head>
<body>
<?php include "navbar.php"; ?>
<h1>All Recipes</h1>
<?php include "search.php"; ?>
<div class="grid">
<?php while ($row = $result->fetch_assoc()): ?>
<?php
$is_bookmarked = false;

if (isset($_SESSION["user_id"])) {
    $uid = $_SESSION["user_id"];
    $bm = $conn->prepare("
        SELECT 1 FROM bookmarks
        WHERE user_id=? AND recipe_id=?
    ");
    $bm->bind_param("ii", $uid, $row['id']);
    $bm->execute();
    $is_bookmarked = $bm->get_result()->num_rows > 0;
    $bm->close();
}
?>

<div class="recipe-card">
    <a href="recipe.php?id=<?= $row['id'] ?>">
        <img src="http://localhost:8080/foodbook/<?= htmlspecialchars($row['image']) ?>"
             onerror="this.src='images/placeholder.jpg'">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><?= htmlspecialchars($row['category']) ?></p>
    </a>

    <?php if (isset($_SESSION["user_id"])): ?>
        <?php if ($is_bookmarked): ?>
            <a href="remove_bookmark.php?id=<?= $row['id'] ?>"
               class="save-icon saved"
               title="Remove Bookmark">
               <i class="fas fa-bookmark"></i>
            </a>
        <?php else: ?>
            <a href="add_bookmark.php?id=<?= $row['id'] ?>"
               class="save-icon"
               title="Save Recipe">
               <i class="far fa-bookmark"></i>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php endwhile; ?>
</div>
<?php include "footer.php"; ?>
</body>
</html>
