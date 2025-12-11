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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $currentPassword = htmlspecialchars($_POST['current_password']);
    $newPassword = htmlspecialchars($_POST['new_password']);
    $confirmPassword = htmlspecialchars($_POST['confirm_password']);

    // Validations
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['errors'][] = 'All fields are required';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (strlen($newPassword) < 8) {
        $_SESSION['errors'][] = 'Password must be in 8 character';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['errors'][] = 'Password and Confirm password Must be matched';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Fetch Old Password
    $sql = $conn->prepare('SELECT * FROM users_tbl WHERE id = :userId');
    $sql->bindParam(':userId', $_SESSION['userId']);
    $sql->execute();
    $row = $sql->fetch();

    if (!password_verify($currentPassword, $row['user_password'])) {
        $_SESSION['errors'][] = 'Current Password is not matched with our Data';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // HashPassword
    $hashPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update Password

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('UPDATE users_tbl 
                                   SET user_password = :upassword
                                WHERE             id = :id');
        $stmt->bindParam(':upassword', $hashPassword);
        $stmt->bindParam(':id', $_SESSION['userId']);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();
            $_SESSION['success'][] = 'Password Update Successfully';
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Error in Update Data ' . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}








// Store Success and Errors in variable
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

    <section class="p-3 m-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="text-center bg-secondary text-white p-2 rounded-bottom">Change Password</h2>

                    <form class="mb-3" method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                        <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>

                        <button type="submit" name="issSubmitted" class="btn btn-dark">Save Changes</button>
                        <a href="index.php" class="btn btn-outline-danger">Cancel</a>
                    </form>

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