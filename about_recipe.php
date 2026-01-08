<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recipes • RecipeBook</title>
    <link rel="stylesheet" href="recipe.css">
</head>
<body>
<?php include "navbar.php"; ?>

<div class="hero-section">
    <h1>Welcome to RecipeBook</h1>
    <p>Your destination for discovering delicious recipes from around the world.</p>
    <p>Explore rich flavors, healthy meals, and fun cooking ideas — all in one place!</p>
</div>

<h2 class="section-title">Popular Recipes</h2>

<div class="sample-grid">
    <div class="sample-card">
        <img src="uploads/biryani.jpg" alt="Biryani">
        <h3>Biryani</h3>
        <p>Fragrant basmati rice cooked with tender meat and spices.</p>
    </div>

    <div class="sample-card">
        <img src="uploads/pancakes.jpg" alt="Pancakes">
        <h3>Pancakes</h3>
        <p>Soft, fluffy, and perfect for a sweet breakfast.</p>
    </div>

    <div class="sample-card">
        <img src="uploads/momo.jpg" alt="Momo">
        <h3>Momo</h3>
        <p>Steamed dumplings filled with veggies or meat.</p>
    </div>
</div>

<div class="center-box">
    <?php if (!isset($_SESSION["user_id"])): ?>
        <a href="login.php" class="btn-full">Create Recipes</a>
    <?php else: ?>
        <a href="create.php" class="btn-full">Create Recipes</a>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
</body>
</html>
