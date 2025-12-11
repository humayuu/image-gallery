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

// Store Errors & Success message in session variable
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])) {
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: edit.php');
        exit;
    }

    $id = htmlspecialchars($_POST['id']);
    $imageName = filter_var(trim($_POST['imageName']), FILTER_SANITIZE_SPECIAL_CHARS);
    $categoryName = filter_var(trim($_POST['category']), FILTER_SANITIZE_SPECIAL_CHARS);
    $oldImage = filter_var(trim($_POST['oldImage']), FILTER_SANITIZE_SPECIAL_CHARS);
    $image = null;

    $allowedExtension = ['jpg', 'jpeg', 'png'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $uploadDir = __DIR__ . '/uploads/';

    // Create a Upload Directory
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $size = $_FILES['image']['size'];
        $tmpName = $_FILES['image']['tmp_name'];

        if (!in_array($ext, $allowedExtension)) {
            $_SESSION['errors'][] = 'Extension not Allowed';
            header('Location: edit.php');
            exit;
        }

        if ($size > $maxFileSize) {
            $_SESSION['errors'][] = 'Max file size is 2MB';
            header('Location: edit.php');
            exit;
        }

        $imgName = uniqid('image_') . time() . '.' . $ext;

        if (!move_uploaded_file($tmpName, $uploadDir . $imgName)) {
            $_SESSION['errors'][] = 'Image Upload failed';
            header('Location: edit.php');
            exit;
        }

        $image = 'uploads/' . $imgName;
    } else {
        $image = $oldImage;
    }

    // Validation

    if (empty($imageName) || empty($categoryName) || empty($image)) {
        $_SESSION['errors'][] = 'All Fields are required';
        header('Location: edit.php');
        exit;
    }


    try {

        $conn->beginTransaction();

        $stmt = $conn->prepare('UPDATE gallery_tbl
                                 SET  image_name  = :iname, 
                                      img         = :img,
                                      category_id = :cid    
                              WHERE   id          = :id');
        $stmt->bindParam(':iname', $imageName);
        $stmt->bindParam(':img', $image);
        $stmt->bindParam(':cid', $categoryName);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();

            if (!empty($image) && $oldImage !== $image) {
                unlink($oldImage);
            }

            $_SESSION['success'][] = 'Successfully Update';
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Error in Update Data ' . $e->getMessage();
        header('Location: edit.php');
        exit;
    }
}

// Fetch data for Specific ID
try {

    $id = htmlspecialchars($_GET['id']);

    $sql = $conn->prepare('SELECT * FROM gallery_tbl WHERE id = :id');
    $sql->bindParam(':id', $id);
    $sql->execute();
    $row = $sql->fetch();
} catch (Exception $e) {
    $_SESSION['errors'][] = 'Error in Fetch Data ' . $e->getMessage();
    header('Location: edit.php');
    exit;
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
                            User
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
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
                    <h2 class="text-center bg-dark text-white p-2 rounded-bottom">Edit Your Image</h2>
                    <form class="mb-3" method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>"
                        enctype="multipart/form-data">
                        <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                        <input type="hidden" name="oldImage" value="<?= htmlspecialchars($row['img']) ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Image Name</label>
                            <input type="text" class="form-control" name="imageName"
                                value="<?= htmlspecialchars($row['image_name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Image</label>
                            <input type="file" class="form-control" name="image">
                            <img width="200" src="<?= htmlspecialchars($row['img']) ?>" alt="image">
                        </div>

                        <div class="mb-3">

                            <label for="image" class="form-label">Image Category</label>
                            <select class="form-select" name="category" aria-label="Default select example">
                                <option disabled selected>Open this select menu</option>
                                <?php foreach ($categories as $category): ?>
                                    <option <?= ($row['category_id'] == $category['id']) ? 'selected' : null  ?>
                                        value="<?= htmlspecialchars($category['id']) ?>">
                                        <?= htmlspecialchars($category['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="issSubmitted" class="btn btn-dark">Save Changes</button>
                        <a href="index.php" class="btn btn-outline-danger">Cancel</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>

<?php $conn = null; ?>