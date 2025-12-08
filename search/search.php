<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Connection Failed");

// GET SEARCH PARAMETERS
$q = isset($_GET['q']) ? trim($_GET['q']) : "";
$category = isset($_GET['category']) ? trim($_GET['category']) : "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : "newest"; // newest, oldest, rating, title

// ESCAPE INPUT
$qEscaped = $conn->real_escape_string($q);
$categoryEscaped = $conn->real_escape_string($category);

// BUILD SQL QUERY
$sql = "
    SELECT 
        r.*,
        COALESCE(AVG(rev.rating), 0) as avg_rating,
        COUNT(rev.id) as review_count
    FROM recipes r
    LEFT JOIN recipe_reviews rev ON r.id = rev.recipe_id
    WHERE r.status = 'approved'
";

// ADD SEARCH CONDITIONS
if (!empty($qEscaped)) {
    $sql .= " AND (
        r.title LIKE '%$qEscaped%' 
        OR r.category LIKE '%$qEscaped%' 
        OR r.description LIKE '%$qEscaped%'
        OR r.ingredients LIKE '%$qEscaped%'
    )";
}

// ADD CATEGORY FILTER
if (!empty($categoryEscaped)) {
    $sql .= " AND r.category = '$categoryEscaped'";
}

// GROUP BY recipe
$sql .= " GROUP BY r.id";

// ADD SORTING
switch($sort) {
    case 'oldest':
        $sql .= " ORDER BY r.created_at ASC";
        break;
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC, review_count DESC";
        break;
    case 'title':
        $sql .= " ORDER BY r.title ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY r.created_at DESC";
        break;
}

$result = $conn->query($sql);

// GET AVAILABLE CATEGORIES
$categories_query = "SELECT DISTINCT category FROM recipes WHERE status = 'approved' ORDER BY category";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Recipe Book</title>
    <link rel="stylesheet" href="../search.css">
    <style>
        /* Enhanced Search Page Styles */
        .search-filters {
            background: white;
            padding: 25px;
            margin: 20px auto;
            max-width: 1200px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input[type="text"] {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            min-width: 200px;
        }
        
        .filter-group button {
            padding: 10px 25px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .filter-group button:hover {
            background-color: #0056b3;
        }
        
        .search-info {
            margin: 20px auto;
            max-width: 1200px;
            padding: 0 20px;
            color: #666;
        }
        
        .search-info strong {
            color: #007bff;
        }
        
        .recipe-card .rating {
            color: #f39c12;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .clear-filters {
            background-color: #6c757d;
            margin-left: 10px;
        }
        
        .clear-filters:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<?php include "../navbar.php"; ?>

<div class="search-page">

    <h1>
        <?php if (!empty($q)): ?>
            Search Results for: <span>"<?= htmlspecialchars($q) ?>"</span>
        <?php elseif (!empty($category)): ?>
            <?= htmlspecialchars(ucfirst($category)) ?> Recipes
        <?php else: ?>
            All Recipes
        <?php endif; ?>
    </h1>

    <!-- SEARCH FILTERS -->
    <div class="search-filters">
        <div class="filter-group">
            <input 
                type="text" 
                id="searchInput" 
                placeholder="Search for recipes..." 
                value="<?= htmlspecialchars($q) ?>"
            >
            
            <select id="categoryFilter">
                <option value="">All Categories</option>
                <?php while($cat = $categories_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($cat['category']) ?>"
                            <?= $category == $cat['category'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($cat['category'])) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select id="sortFilter">
                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                <option value="rating" <?= $sort == 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                <option value="title" <?= $sort == 'title' ? 'selected' : '' ?>>Alphabetical</option>
            </select>
            
            <button onclick="applyFilters()">Search</button>
            
            <?php if (!empty($q) || !empty($category) || $sort != 'newest'): ?>
                <button class="clear-filters" onclick="clearFilters()">Clear Filters</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- SEARCH INFO -->
    <div class="search-info">
        <p>Found <strong><?= $result->num_rows ?></strong> recipe(s)</p>
    </div>

    <!-- RECIPE GRID -->
    <div class="recipe-grid">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { 
                $avg_rating = round($row['avg_rating'], 1);
                $review_count = $row['review_count'];
                ?>

                <div class="recipe-card">
                    <a href="../recipe.php?id=<?= $row['id'] ?>">
                        <img src="http://localhost:8080/foodbook/<?= $row['image'] ?>"
                             alt="<?= htmlspecialchars($row['title']) ?>"
                             onerror="this.src='http://localhost:8080/foodbook/images/placeholder.jpg'">

                        <h3><?= htmlspecialchars($row['title']) ?></h3>
                        <p><?= htmlspecialchars(ucfirst($row['category'])) ?></p>
                        
                        <?php if ($review_count > 0): ?>
                            <div class="rating">
                                ⭐ <?= $avg_rating ?>/5 
                                (<?= $review_count ?> review<?= $review_count != 1 ? 's' : '' ?>)
                            </div>
                        <?php else: ?>
                            <div class="rating">No reviews yet</div>
                        <?php endif; ?>
                    </a>
                </div>

        <?php }
        } else {
            echo "<p class='no-results'>
                    No recipes matched your search. 
                    <a href='search.php'>View all recipes</a>
                  </p>";
        }
        ?>
    </div>

</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const sort = document.getElementById('sortFilter').value;
    
    let url = 'search.php?';
    if (search) url += 'q=' + encodeURIComponent(search) + '&';
    if (category) url += 'category=' + encodeURIComponent(category) + '&';
    if (sort && sort !== 'newest') url += 'sort=' + sort;
    
    window.location.href = url;
}

function clearFilters() {
    window.location.href = 'search.php';
}

// Allow Enter key to search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

document.getElementById('categoryFilter').addEventListener('change', applyFilters);
document.getElementById('sortFilter').addEventListener('change', applyFilters);
</script>

<?php include "../footer.php"; ?>
</body>
</html>