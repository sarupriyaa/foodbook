<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="style.css">
<script src="navbar.js" defer></script>
<nav class="navbar" id="navbar">
    <div class="logo">
        <a href="home.php">RecipeBook</a>
    </div>
    <div class="hamburger" id="hamburger">☰</div>
    <ul class="nav-links" id="nav-links">
        <li><a href="home.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li class="dropdown">
                    <a href="javascript:void(0)" class="dropdown-btn">Recipes ▼</a>
                    <ul class="dropdown-menu">
                        <li><a href="recipes.php">All Recipes</a></li>
                        <li><a href="breakfast.php">Breakfast</a></li>
                        <li><a href="lunch.php">Lunch</a></li>
                        <li><a href="snacks.php">Snacks</a></li>
                        <li><a href="desserts.php">Desserts</a></li>
                    </ul>
                </li>
        <?php if (!isset($_SESSION["role"])): ?>
            <li><a href="login.php" class="btn">Sign In</a></li>
            <li><a href="register.php" class="btn primary">Sign Up</a></li>
        <?php else: ?>
            <?php if ($_SESSION["role"] == "user"): ?>
                <li><a href="create.php">Create</a></li>
            <?php endif; ?>

            <?php if ($_SESSION["role"] == "admin"): ?>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
            <?php endif; ?>

            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php" class="btn logout">Logout</a></li>

        <?php endif; ?>

    </ul>

</nav>
