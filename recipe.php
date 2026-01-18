<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// database
$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

// Validate recipe ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) die("Invalid recipe ID");
$id = intval($_GET["id"]);

// where user came from
$from = isset($_GET['from']) ? $_GET['from'] : "recipes";

$back_link = "recipes.php";

if ($from === "profile") $back_link = "profile.php";
if ($from === "admin")   $back_link = "admin_dashboard.php";

// Fetch recipe
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();

if (!$recipe) die("Recipe not found");

// Save review
$review_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["user_id"])) {
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);
    $user_id = $_SESSION["user_id"];

    if ($rating >= 1 && $rating <= 5 && !empty($review_text)) {
        $insert = $conn->prepare("
            INSERT INTO recipe_reviews (recipe_id, user_id, rating, review_text)
            VALUES (?, ?, ?, ?)
        ");
        $insert->bind_param("iiis", $id, $user_id, $rating, $review_text);
        $insert->execute();
        $review_message = "✔ Review submitted successfully!";
    } else {
        $review_message = "❌ Please provide a rating and review text.";
    }
}

// fetch reviews
$reviews = $conn->query("
    SELECT r.*, u.name 
    FROM recipe_reviews r
    JOIN users u ON r.user_id = u.id
    WHERE recipe_id = $id
    ORDER BY created_at DESC
");

// avg rating
$avg_query = $conn->query("
    SELECT AVG(rating) AS avg_rating 
    FROM recipe_reviews 
    WHERE recipe_id = $id
");
$avg_row = $avg_query->fetch_assoc();
$avg_rating = ($avg_row['avg_rating'] !== null) ? round($avg_row['avg_rating'], 1) : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($recipe['title']) ?></title>
    <link rel="stylesheet" href="recipes.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="recipe-container">
    <a href="<?= $back_link ?>" class="back-link">← Back to Recipes</a>

    <h1 class="recipe-title"><?= htmlspecialchars($recipe['title']) ?></h1>
    <p class="avg-rating">
        Average Rating: <?= $avg_rating ?? "No ratings yet" ?>
    </p>
    <img src="http://localhost:8080/foodbook/<?= $recipe['image'] ?>"
         class="recipe-image"
         alt="<?= htmlspecialchars($recipe['title']) ?>">
    <p class="description">
        <?= nl2br(htmlspecialchars($recipe['description'])) ?>
    </p>

    <h2 class="section-title">Ingredients</h2>
    <ul class="ingredients-list">
        <?php foreach (explode("\n", $recipe['ingredients']) as $i): ?>
            <li><?= htmlspecialchars($i) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2 class="section-title">Steps</h2>
    <ol class="steps-list">
        <?php foreach (explode("\n", $recipe['steps']) as $s): ?>
            <li><?= htmlspecialchars($s) ?></li>
        <?php endforeach; ?>
    </ol>

    <h2 class="section-title">Nutrition</h2>
    <ul class="ingredients-list">
        <?php foreach (explode("\n", $recipe['nutrition']) as $n): ?>
            <li><?= htmlspecialchars($n) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2 class="section-title">Customer Reviews</h2>
    <?php if ($reviews->num_rows == 0): ?>
        <p>No reviews yet.</p>
    <?php else: ?>
        <ul class="review-list">
            <?php while ($rev = $reviews->fetch_assoc()): ?>
                <li>
                    <strong><?= htmlspecialchars($rev['name']) ?></strong>
                    <span>⭐ <?= $rev['rating'] ?>/5</span>
                    <p><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>
                    <small><?= $rev['created_at'] ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>

    <?php if (isset($_SESSION["user_id"])): ?>
        <h2 class="section-title">Write a Review</h2>

        <?php if ($review_message): ?>
            <p class="review-msg"><?= $review_message ?></p>
        <?php endif; ?>

        <form method="POST" class="review-form">
            <label>Rating:</label>
            <select name="rating" required>
                <option value="">Select rating</option>
                <option value="5">⭐ 5</option>
                <option value="4">⭐ 4</option>
                <option value="3">⭐ 3</option>
                <option value="2">⭐ 2</option>
                <option value="1">⭐ 1</option>
            </select>

            <label>Your Review:</label>
            <textarea name="review_text" required></textarea>

            <button type="submit" class="btn-submit">Submit</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Log in</a> to write a review.</p>
    <?php endif; ?>

    <h2 class="section-title">Video</h2>
    <div class="video-wrapper">
        <iframe src="<?= htmlspecialchars($recipe['video_url']) ?>" allowfullscreen></iframe>
    </div>
</div>
<?php include "footer.php"; ?>
</body>
</html>
