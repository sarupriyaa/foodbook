<?php
session_start();
require "db.php";

if ($_SESSION['user']['role'] !== 'admin') {
    die("Access denied");
}
$id = $_GET['id'];
$conn->query("DELETE FROM users WHERE id = $id");
header("Location: admin.php");
exit;
