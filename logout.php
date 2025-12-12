<?php
session_start();

// Connection to Database
require 'config.php';

// Update Admin user Status
$query = $conn->prepare('UPDATE users_tbl SET user_admin_status = "0" WHERE id = :uid');
$query->bindParam(':uid', $_SESSION['userId']);
$query->execute();

session_unset();

if (session_destroy()) {
    header('Location: ./index.php');
    exit;
}
