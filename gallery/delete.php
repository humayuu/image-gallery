<?php
// Session Start 
session_start();

// Connection to Database
require '../config.php';

// Store Errors & Success message in session variable
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = [];
}

$id = htmlspecialchars($_GET['id']);

// Fetch image data for delete from folder
$sql = $conn->prepare('SELECT * FROM gallery_tbl WHERE id = :id');
$sql->bindParam(':id', $id);
$sql->execute();
$img = $sql->fetch();
$image = $img['img'];

try {

    $conn->beginTransaction();

    $stmt = $conn->prepare('DELETE FROM gallery_tbl WHERE id = :id');
    $stmt->bindParam(':id', $id);
    $result = $stmt->execute();

    if ($result) {
        $conn->commit();
        if (!empty($image) || file_exists($image)) {
            unlink($image);
        }
        $_SESSION['success'][] = 'Successfully Deleted';
        header("Location: index.php");
        exit;
    }
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['errors'][] = 'Delete error ' . $e->getMessage();
    header("Location: index.php");
    exit;
}