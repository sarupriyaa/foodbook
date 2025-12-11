<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// ONLY ADMIN
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "recipebook");
if ($conn->connect_error) die("DB Error");

// DELETE USER
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: admin_users.php");
    exit();
}

// FETCH USERS
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="footer.css">
</head>

<body>

<div class="admin-container">

    <?php include "admin_sidebar.php"; ?>

    <main class="content">
        <h1>👥 Manage Users</h1>

        <table class="table">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Address</th>
                <th>Role</th>
                <th>Action</th>
            </tr>

            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $u['name'] ?></td>
                <td><?= $u['email'] ?></td>
                <td><?= $u['address'] ?></td>
                <td><?= ucfirst($u['role']) ?></td>

                <td>
                    <?php if ($u['role'] !== 'admin'): ?>
                        <a href="?delete=<?= $u['id'] ?>" class="delete">Delete</a>
                    <?php else: ?>
                        <span style="color:gray;">Admin</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>

    </main>
</div>

</body>
</html>
