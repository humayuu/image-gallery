<?php
// Session Start
session_start();

// Check if User is already Exists
if (isset($_SESSION['loggedIn']) == true) {
    // Redirected to Home page 
    header('Location: gallery/index.php');
    exit;
}
// Connection to Database
require 'config.php';

// Generate CSRF Token
if (!isset($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}


// Store Errors in Session Variable
if (!isset($_SESSION['errors']) && isset($_SESSION['success'])) {
    $_SESSION['errors'] = [];
    $_SESSION['success'] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmit'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Collect Data from Form
    $userFullname = filter_var(trim($_POST['fullname']), FILTER_SANITIZE_SPECIAL_CHARS);
    $userEmail = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = htmlspecialchars(trim($_POST['password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirmPassword']));
    $userStatus = 'Active';
    $userAdminStatus = '1';

    // Validations
    if (empty($userFullname) || empty($userEmail) || empty($password)) {
        $_SESSION['errors'][] = 'All Fields are required';
        header('Location: ' . basename(__FILE__));
        exit;
    } elseif (strlen($password) < 8) {
        $_SESSION['errors'][] = 'Password Must be in 8 Character';
        header('Location: ' . basename(__FILE__));
        exit;
    } elseif ($password !== $confirmPassword) {
        $_SESSION['errors'][] = 'Password and Confirm Password must be Match';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Fetch data from Database for Specific User
    $stmt = $conn->prepare("SELECT * FROM users_tbl WHERE user_email = :uemail");
    $stmt->bindParam(':uemail', $email);
    $stmt->execute();
    $user =  $stmt->fetchAll();

    // Verify user email
    if ($user) {
        $_SESSION['errors'][] = 'User already Exists';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Hash User Password
    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert Register User Data into the Database
    try {

        $conn->beginTransaction();

        $stmt = $conn->prepare('INSERT INTO users_tbl (user_fullname, user_email, user_password, user_status, user_admin_status) VALUES (:uname, :uemail, :upassword, :ustatus, :uadminstatus)');
        $stmt->bindParam(':uname', $userFullname);
        $stmt->bindParam(':uemail', $userEmail);
        $stmt->bindParam(':upassword', $hashPassword);
        $stmt->bindParam(':ustatus', $userStatus);
        $stmt->bindParam(':uadminstatus', $userAdminStatus);
        $result =  $stmt->execute();

        // Redirected to Login Page
        if ($result) {
            $conn->commit();
            $_SESSION['success'][] = '<strong>Successfully Register</strong> Please login Your Account';
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Error in insert Data ' . $e->getMessage();
        header('Location: ' . basename(__FILE__));
        exit;
    }
}


// Store Errors in variable
$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

require 'header.php';

?>

<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <!-- Logo/Brand Section -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1 text-dark">Image Gallery</h2>
                    <p class="text-muted mb-0">Your Beautiful image Gallery is one step away</p>
                </div>

                <!-- Show Errors -->
                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Registration Card -->
                <div class="card shadow border-0">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="card-title text-center mb-2 fw-bold fs-3">Create Account</h1>
                        <p class="text-center text-muted mb-4">Sign up to get started</p>
                        <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">

                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname"
                                    placeholder="John Doe" autofocus>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="your.email@example.com">
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Create a strong password">
                                <div class="form-text">Must be at least 8 characters long</div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                    placeholder="Re-enter your password">
                            </div>

                            <!-- Register Button -->
                            <div class="d-grid mb-3">
                                <button name="isSubmit" type="submit" class="btn btn-dark btn-lg">Create
                                    Account</button>
                            </div>
                        </form>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="mb-0 text-muted">Already have an account?
                                <a href="index.php" class="text-decoration-none">Login here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require 'footer.php' ?>
    <?php $conn = null; ?>