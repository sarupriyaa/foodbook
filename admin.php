<?php
session_start();
require "db.php"; // <-- Your database connection file

if ($_SESSION['user']['role'] !== 'admin') {
    echo "Access Denied — Admins Only!";
    exit;
}

// Redirect if NOT logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Redirect if NOT admin
if ($_SESSION['user']['role'] !== 'admin') {
    echo "<h2 style='color:red; text-align:center; margin-top:40px;'>Access Denied! Admins only.</h2>";
    exit;
}
?>

<?php include "navbar.php"; ?>  <!-- Include your navbar -->

<link rel="stylesheet" href="admin.css">
<?php include "navbar.php"; ?>

<div class="admin-container">

    <h1 class="admin-title">Admin Dashboard</h1>

    <div class="admin-cards">

        <div class="card">
            <h2>Total Users</h2>
            <p>
                <?php
                $result = $conn->query("SELECT COUNT(*) AS total FROM users");
                echo $result->fetch_assoc()['total'];
                ?>
            </p>
        </div>

        <div class="card">
            <h2>Total Recipes</h2>
            <p>
                <?php
                $result = $conn->query("SELECT COUNT(*) AS total FROM recipes");
                echo $result->fetch_assoc()['total'];
                ?>
            </p>
        </div>

        <div class="card">
            <h2>Categories</h2>
            <p>5</p>
        </div>

    </div>

    <h2 class="section-title">Manage Users</h2>

    <table class="admin-table">
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>

        <?php
        $result = $conn->query("SELECT * FROM users ORDER BY id DESC");

        while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= $row['email']; ?></td>
                <td><?= $row['role']; ?></td>
                <td>
                    <?php if ($row['role'] !== 'admin') { ?>
                        <a class='delete-btn' href="delete_user.php?id=<?= $row['id']; ?>">Delete</a>
                    <?php } else {
                        echo "—";
                    } ?>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>
