<?php
// Session start 
session_start();

// Connection to database
require '../config.php';

// Initialize Session for Store Error and Success Message
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = [];
}

$id = htmlspecialchars($_GET['id']);


try {

    $stmt = $conn->prepare('DELETE FROM category_tbl WHERE id = :id');
    $stmt->bindParam(':id', $id);
    $result = $stmt->execute();


    if ($result) {
        $_SESSION['message'][] = 'Category Delete Successfully';
        header('Location: all_category.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['message'][] = 'Error in delete ' . $e->getMessage();
    header('Location: all_category.php');
    exit;
}
