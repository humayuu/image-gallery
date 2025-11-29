<?php
// Session Start
session_start();

// Connection to database
require '../config.php';
// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Initialize Session for Store Error and Success Message
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = [];
}



// fetch data with specific id
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = $conn->prepare('SELECT * FROM category_tbl WHERE id = :id');
    $sql->bindParam(':id', $id);
    $sql->execute();
    $category = $sql->fetch();
}

// Handle POST request to add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['message'][] = 'Invalid CSRF Token';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $id = htmlspecialchars($_POST['id']);
    $categoryName = filter_var(trim($_POST['name']), FILTER_SANITIZE_SPECIAL_CHARS);
    $oldCategory = htmlspecialchars($_POST['oldCategory']);
    $newCategoryName;

    $newCategoryName = ($categoryName && $categoryName !== $oldCategory) ? $categoryName : $oldCategory;

    // Insert Data into the Database
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('UPDATE category_tbl SET category_name = :cname WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':cname', $newCategoryName);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();
            $_SESSION['message'][] = 'Category successfully Update';
            header('Location: all_category.php');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['message'][] = 'Insert error: ' . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}






// Get messages and clear after displaying
$message = $_SESSION['message'] ?? [];
$_SESSION['message'] = [];

require 'header.php';

?>
<!--start content-->
<main class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Image Gallery</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0">Edit Category</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-4 d-flex">
                    <div class="card border shadow-none w-100">
                        <div class="card-body">
                            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="row g-3">
                                <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
                                <input type="hidden" name="oldCategory"
                                    value="<?= htmlspecialchars($category['category_name']) ?>">
                                <div class="col-12">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Category name"
                                        value="<?= htmlspecialchars($category['category_name'])  ?>">
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button name="issSubmitted" class="btn btn-primary">Update Category</button>
                                    </div>
                                </div>
                            </form>
                            <!-- Display Errors -->
                            <?php if (!empty($message)): ?>
                                <div class="row mt-3 justify-content-center">
                                    <div class="col-md-12 col-lg-12">
                                        <?php foreach ($message as $msg): ?>
                                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                                <span><?= htmlspecialchars($msg) ?></span>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
    </div>

</main>
<!--end page main-->
<?php require 'footer.php' ?>