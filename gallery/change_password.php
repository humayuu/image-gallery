<?php
// Session Start
session_start();

// Check if User is login or not
if(!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true){
    $_SESSION['errors'][] = 'Please Login your Account First';
    header('Location: ../index.php');
    exit;
}

?>