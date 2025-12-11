<?php
// Session Start
session_start();

// Check if User is login or not
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
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

        $stmt = $conn->prepare('INSERT INTO gallery_tbl (image_name, img, category_id, user_id) VALUES (:imageName, :imagePath, :categoryId, :userId)');
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
                    <?php
                    // Ensure $conn is available and valid before running this code
                    $sq = $conn->prepare('SELECT * FROM category_tbl ORDER BY id DESC');
                    $sq->execute();
                    $categories =  $sq->fetchAll();
                    ?>

                    <?php foreach ($categories as $category): ?>
                        <li class="nav-item">
                            <a class="nav-link fs-5" aria-current="page"
                                href="category_wise.php?category_id=<?= htmlspecialchars($category['id']) ?>">
                                <?= htmlspecialchars($category['category_name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link fs-5 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <?= htmlspecialchars($_SESSION['userName']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
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
                                <option disabled selected>Open this select menu</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id']) ?>">
                                        <?= htmlspecialchars($category['category_name']) ?></option>
                                <?php endforeach; ?>
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
    $sql = $conn->prepare('SELECT * FROM gallery_tbl WHERE user_id = :userId  ORDER BY id DESC');
    $sql->bindParam(':userId', $_SESSION['userId']);
    $sql->execute();
    $images = $sql->fetchAll();
    ?>
    <section class="p-4">
        <div class="container">
            <?php if ($images): ?>
                <div class="row g-4">
                    <?php foreach ($images as $img): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100 gallery-card">
                                <div class="img-wrapper">
                                    <img src="<?= htmlspecialchars($img['img']) ?>" class="card-img-top" alt="image">
                                </div>

                                <div class="card-body text-center">
                                    <p class="card-text fw-semibold mb-2">
                                        <?= htmlspecialchars($img['image_name']) ?>
                                    </p>

                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="delete.php?id=<?= htmlspecialchars($img['id']) ?>"
                                            onclick="return confirm('Are you sure')" class="text-danger fs-5 icon-hover"
                                            title="Delete">
                                            <i class="bi bi-trash3-fill"></i>
                                        </a>
                                        <a href="edit.php?id=<?= htmlspecialchars($img['id']) ?>"
                                            class="text-primary fs-5 icon-hover" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    No Image Found
                </div>
            <?php endif; ?>
        </div>
    </section>

    <style>
        .gallery-card img {
            height: 200px;
            object-fit: cover;
            transition: 0.3s ease;
        }

        .gallery-card:hover img {
            transform: scale(1.05);
        }

        .icon-hover:hover {
            opacity: 0.7;
            transition: 0.2s ease;
        }
    </style>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>

<?php $conn = null; ?>