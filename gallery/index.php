<?php
// Session Start
session_start();

// Check if User is login or not
if(!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true){
    $_SESSION['errors'][] = 'Please Login your Account First';
    header('Location: ../index.php');
    exit;
}


// Connection to Database
require '../config.php';


// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Store Errors & Success message in session variable
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = [];
}



// Insert data into the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Collect Data from form
    $name = filter_var(trim($_POST['imageName']), FILTER_SANITIZE_SPECIAL_CHARS);
    $category = filter_var(trim($_POST['category']), FILTER_SANITIZE_SPECIAL_CHARS);
    $image = null;

    $allowedExtension = ['jpg', 'jpeg', 'png'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $uploadDir = __DIR__ . '/uploads/';

    // Create Upload Directory
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }


    // Upload Image 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $tmpName = $_FILES['image']['tmp_name'];
        $size = $_FILES['image']['size'];

        if (!in_array($ext, $allowedExtension)) {
            $_SESSION['errors'][] = 'Extension not allowed, file must be in jpg, jpeg, png';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        if ($size > $maxFileSize) {
            $_SESSION['errors'][] = 'Max file size is 2 MB';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        $newName = uniqid('image_') . time() . '.' . $ext;

        if (!move_uploaded_file($tmpName, $uploadDir . $newName)) {
            $_SESSION['errors'][] = 'Error in image upload';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        $image = 'uploads/' . $newName;
    }

    // Validations
    if (empty($name) || empty($image)) {
        $_SESSION['errors'][] = 'All fields are required';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('INSERT INTO gallery_tbl (img_name, img, category_id, user_id) VALUES (:imageName, :imagePath, :categoryId, :userId)');
        $stmt->bindParam(':imageName', $name);
        $stmt->bindParam(':imagePath', $image);
        $stmt->bindParam(':categoryId', $category);
        $stmt->bindParam(':userId', $_SESSION['userId']);
        $result =  $stmt->execute();

        if ($result) {
            $conn->commit();
            $_SESSION['success'][] = 'Image Upload Successfully.';
            // Redirected to Same page
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Insert error ' . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}



// Store Success and Errors in variable
$message = $_SESSION['success'] ?? [];
$_SESSION['success'] = [];

$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-light">
        <div class="container">
            <a class="navbar-brand fs-3" href="#">Image <span class="text-primary">Gallery</span> </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link fs-5" aria-current="page" href="#">Category</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link fs-5 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            User
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Change Password</a></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="p-3 m-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="text-center bg-dark text-white p-2 rounded-bottom">Upload Your Image</h2>
                    <form class="mb-3" method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>"
                        enctype="multipart/form-data">
                        <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Image Name</label>
                            <input type="text" class="form-control" name="imageName">
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Image</label>
                            <input type="file" class="form-control" name="image">
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image Category</label>
                            <select class="form-select" name="category" aria-label="Default select example">
                                <option selected>Open this select menu</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                        <button type="submit" name="issSubmitted" class="btn btn-dark">Submit</button>
                    </form>
                    <!-- Show Success Message -->
                    <?php if (!empty($message)): ?>
                    <?php foreach ($message as $msg): ?>
                    <div class="alert alert-success text-center" role="alert">
                        <?= $msg ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Show Errors Message -->
                    <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger text-center" role="alert">
                        <?= $error ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php 
    // Fetch all Data from gallery
    $sql = $conn->prepare('SELECT * FROM gallery_tbl.*,  ');
    ?>

    <section class="p-3 m-3">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card" style="width: 18rem;">
                        <img src="#" class="card-img-top" alt="image">
                        <div class="card-body">
                            <p class="card-text">Some quick example text to build on the card title and make up the bulk
                                of the card’s content.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>

<?php $conn = null; ?>