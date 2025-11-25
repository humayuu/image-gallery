<?php 

try{
    $dsn = 'mysql:host=localhost;dbname=image_gallery_db;';
    $user = 'root';
    $password = '';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    $conn = new PDO($dsn, $user, $password, $options);

}catch(PDOException $e){
    throw new PDOException('Database connection failed ' . $e->getMessage());
}