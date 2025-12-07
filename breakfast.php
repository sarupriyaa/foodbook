<?php
session_start();

// SHOW ERRORS
error_reporting(E_ALL);
ini_set("display_errors", 1);

// DB connection
$conn = new mysqli("localhost", "root", "", "recipebook");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch only breakfast recipes
$sql = "SELECT id, title, category, image FROM recipes WHERE category = 'Breakfast'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Breakfast Recipes</title>
    <style>
        h1 {
            margin-top: 20px;
            font-size: 40px;
        }

        .recipe-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: center;
            padding: 30px;
        }

        .recipe-card {
            width: 260px;
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: 0.2s;
        }

        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
        }

        .recipe-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 10px;
        }

        .recipe-card h3 {
            font-size: 20px;
            margin-top: 12px;
        }

        .recipe-card p {
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <?php include "navbar.php"?>
    <h1>Breakfast Recipes</h1>
    <div class="recipe-grid">
    <?php
    if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "
        <div class='recipe-card'>
            <a href='recipe.php?id={$row['id']}' style='text-decoration:none; color:black;'>
                <img src='http://localhost:8080/foodbook/{$row['image']}'>
                <h3>{$row['title']}</h3>
                <p>{$row['category']}</p>
            </a>
        </div>";
    }
    } else {
    echo "<p>No breakfast recipes found.</p>";
    }
    ?>
    </div>
<?php include 'footer.php'; ?>

</body>
</html>
