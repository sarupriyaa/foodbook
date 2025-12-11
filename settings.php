<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    file_put_contents("site_title.txt", $_POST["title"]);
    file_put_contents("site_description.txt", $_POST["description"]);

    $message = "<div class='alert success'>Settings updated!</div>";
}

$title = file_exists("site_title.txt") ? file_get_contents("site_title.txt") : "RecipeBook";
$desc  = file_exists("site_description.txt") ? file_get_contents("site_description.txt") : "Best recipes website.";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="footer.css">
</head>
<body>

<div class="admin-container">

    <main class="content">
        <h1>Website Settings</h1>

        <?= $message ?>

        <form method="POST" class="card">

            <label>Website Title</label>
            <input type="text" name="title" value="<?= $title ?>">

            <label>Description</label>
            <textarea name="description"><?= $desc ?></textarea>

            <button type="submit">Save</button>
        </form>

    </main>
</div>

</body>
</html>
