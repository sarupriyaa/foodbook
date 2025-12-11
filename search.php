<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// DATABASE
$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Connection Failed");

// GET SEARCH INPUTS
// $q = isset($_GET['q']) ? trim($_GET['q']) : "";
// $category = isset($_GET['category']) ? $_GET['category'] : "all";
// $sort = isset($_GET['sort']) ? $_GET['sort'] : "newest";

// BASE QUERY → SHOW ONLY APPROVED RECIPES
$sql = "SELECT * FROM recipes WHERE status='approved'";

// TEXT SEARCH
if ($q !== "") {
    $safe = $conn->real_escape_string($q);
    $sql .= " AND (title LIKE '%$safe%' OR category LIKE '%$safe%' OR description LIKE '%$safe%')";
}

// CATEGORY FILTER
// if ($category !== "all") {
//     $cat = $conn->real_escape_string($category);
//     $sql .= " AND category LIKE '%$cat%'";
// }

// SORTING
// $sql .= ($sort == "oldest") ? " ORDER BY id ASC" : " ORDER BY id DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
    <link rel="stylesheet" href="style.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
body{
    background: #fafafa; padding-top: 90px; 
}
h1 {
    text-align: center;
    margin-top: 10px;
    font-size: 36px;
    font-weight: bold;
    color: #333;
}
.search-box {
    width: 90%;
    margin: 25px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.search-box form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.search-box input,
.search-box select {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
    flex: 1;
}

.btn-search {
    background: #007bff;
    color: #fff;
    padding: 12px 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
}
.btn-search:hover { background: #005fcc; }

.clear-btn {
    background: #777;
    color: white;
    padding: 12px 18px;
    border-radius: 8px;
    text-decoration: none;
}
.clear-btn:hover { background: #555; }

/* .results-count {
    width: 90%;
    margin: auto;
    font-size: 17px;
    margin-bottom: 10px;
    color: #666;
} */

.grid {
    width: 90%;
    margin: 20px auto 50px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.recipe-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #ddd;
    overflow: hidden;
    text-align: center;
    padding-bottom: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: 0.3s ease;
}
.recipe-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}

.recipe-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.recipe-card h3 {
    margin-top: 10px;
    font-size: 18px;
    color: #6a1b9a;
    text-decoration: underline;
}

.recipe-card p {
    color: #444;
    margin-top: 5px;
}

.no-recipes {
    text-align: center;
    font-size: 20px;
    margin-top: 40px;
    color: #777;
}

@media (max-width: 768px) {
    h1 { font-size: 28px; }
}
@media (max-width: 480px) {
    h1 { font-size: 24px; }
    .recipe-card img { height: 150px; }
}
</style>

</head>
<body>

<?php include "navbar.php"; ?>

<h1>Search Results for: "<?= htmlspecialchars($q) ?>"</h1>

<div class="search-box">
    <form method="GET">

        <input type="text" name="q" placeholder="Search recipes..." value="<?= htmlspecialchars($q) ?>">

        <!-- <select name="category">
            <option value="all">All Categories</option>
            <option value="Breakfast" <?= $category=="Breakfast"?"selected":"" ?>>Breakfast</option>
            <option value="Lunch" <?= $category=="Lunch"?"selected":"" ?>>Lunch</option>
            <option value="Snacks" <?= $category=="Snacks"?"selected":"" ?>>Snacks</option>
            <option value="Desserts" <?= $category=="Desserts"?"selected":"" ?>>Desserts</option>
        </select>

        <select name="sort">
            <option value="newest" <?= $sort=="newest"?"selected":"" ?>>Newest First</option>
            <option value="oldest" <?= $sort=="oldest"?"selected":"" ?>>Oldest First</option>
        </select> -->

        <button type="submit" class="btn-search">Search</button>

        <a href="search.php" class="clear-btn">Clear</a>

    </form>
</div>

<p class="results-count">Found <?= $result->num_rows ?> recipe(s)</p>

<!-- ================= SEARCH RESULTS ================= -->
<div class="grid">
<?php
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        echo "
        <div class='recipe-card'>
            <a href='../recipe.php?id={$row['id']}'>
                <img src='http://localhost:8080/foodbook/{$row['image']}'>
                <h3>{$row['title']}</h3>
                <p>{$row['category']}</p>
            </a>
        </div>";
    }

} else {
    echo "<p class='no-recipes'>No recipes matched your search.</p>";
}
?>
</div>

<?php include "footer.php"; ?>

</body>
</html>
