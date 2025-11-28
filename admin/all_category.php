<?php
// Session Start
session_start();

// Connection to Database
require '../config.php';

// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Initialize Session for Store Error and Success Message
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = [];
}

// Handle POST request to add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['message'][] = 'Invalid CSRF Token';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $categoryName = filter_var(trim($_POST['name']), FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($categoryName)) {
        $_SESSION['message'][] = 'Category name is required';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Check if Category name already exists
    $sql = $conn->prepare('SELECT * FROM category_tbl WHERE category_name = :categoryName');
    $sql->bindParam(':categoryName', $categoryName);
    $sql->execute();

    if ($sql->rowCount() > 0) {
        $_SESSION['message'][] = 'Category name already exists';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Insert Data into the Database
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('INSERT INTO category_tbl (category_name) VALUES (:cname)');
        $stmt->bindParam(':cname', $categoryName);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();
            $_SESSION['message'][] = 'Category successfully added';
            header('Location: ' . $_SERVER['PHP_SELF']);
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

<!-- start content -->
<main class="page-content">
    <!-- breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Image Gallery</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- end breadcrumb -->

    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0">Add Category</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-4 d-flex">
                    <div class="card border shadow-none w-100">
                        <div class="card-body">
                            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="row g-3">
                                <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                <div class="col-12">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Category name">
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button name="issSubmitted" class="btn btn-primary">Add Category</button>
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

                <?php
                // Pagination logic
                $limit = 5;
                $pageNo = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offSet = ($pageNo - 1) * $limit;

                // Fetch Categories
                $query = $conn->prepare("SELECT * FROM category_tbl ORDER BY id DESC LIMIT $offSet, $limit");
                $query->execute();
                $categories = $query->fetchAll();

                // Get total rows and calculate total pages
                $qu = $conn->prepare('SELECT COUNT(*) AS total FROM category_tbl');
                $qu->execute();
                $totalRows = $qu->fetch()['total'];
                $totalPages = ceil($totalRows / $limit);
                ?>

                <div class="col-12 col-lg-8 d-flex">
                    <div class="card border shadow-none w-100">
                        <div class="card-body">
                            <div class="table-responsive">
                                <?php if ($categories): ?>
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Category Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['id']) ?></td>
                                            <td><?= htmlspecialchars($category['category_name']) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3 fs-6">
                                                    <a href="edit_category.php?id=<?= htmlspecialchars($category['id']) ?>"
                                                        class="text-warning" data-bs-toggle="tooltip"
                                                        data-bs-placement="bottom" title="Edit info"><i
                                                            class="bi bi-pencil-fill"></i></a>
                                                    <a href="delete_category.php?id=<?= htmlspecialchars($category['id']) ?>"
                                                        class="text-danger" onclick="return confirm('Are you sure?')"
                                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                        title="Delete"><i class="bi bi-trash-fill"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <div class="alert alert-danger fade show" role="alert">
                                    <span>No categories found!</span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Pagination -->
                            <nav aria-label="...">
                                <ul class="pagination">
                                    <li class="page-item <?= ($pageNo == 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $pageNo - 1 ?>" tabindex="-1">Previous</a>
                                    </li>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($pageNo == $i) ? 'active' : '' ?>">
                                        <a class="page-link"
                                            href="?page=<?= htmlspecialchars($i) ?>"><?= htmlspecialchars($i) ?></a>
                                    </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?= ($pageNo == $totalPages) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $pageNo + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->
        </div>
    </div>
</main>
<!-- end page main -->

<?php require 'footer.php' ?>