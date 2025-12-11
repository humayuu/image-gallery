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
            <a class="navbar-brand fs-3" href="index.php">Image <span class="text-primary">Gallery</span> </a>
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
    <?php
    // Fetch all Data from gallery
    $category_id = htmlspecialchars($_GET['category_id']);
    $sql = $conn->prepare('SELECT * FROM gallery_tbl WHERE category_id = :catId AND user_id = :userId  ORDER BY id DESC');
    $sql->bindParam(':catId', $category_id);
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