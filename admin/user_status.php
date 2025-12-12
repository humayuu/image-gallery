<?php
// Session Start
session_start();

// Connection to Database
require '../config.php';


// Initialize Session for Store Error and Success Message
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = [];
}



$id = htmlspecialchars($_GET['id']);

$query = $conn->prepare('SELECT * FROM users_tbl WHERE id = :id');
$query->bindParam(':id', $id);
$query->execute();
$user = $query->fetch();
$newStatus = ($user['user_status'] == 'Active') ? 'Inactive' : 'Active';

try {
    $stmt = $conn->prepare('UPDATE users_tbl SET user_status = :newStatus WHERE id = :id');
    $stmt->bindParam(':newStatus', $newStatus);
    $stmt->bindParam(':id', $id);
    $result = $stmt->execute();

    if ($result) {
        header('Location: all_user.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['message'][] = 'Error in Update Status: ' . $e->getMessage();
    header('Location: all_user.php');
    exit;
}
