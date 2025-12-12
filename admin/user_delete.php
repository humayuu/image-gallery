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

try {
    $stmt = $conn->prepare('DELETE FROM users_tbl WHERE id = :id');
    $stmt->bindParam(':id', $id);
    $result = $stmt->execute();

    if ($result) {
        $_SESSION['message'][] = 'User Successfully Deleted';
        header('Location: all_user.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['message'][] = 'Error in Delete: ' . $e->getMessage();
    header('Location: all_user.php');
    exit;
}
