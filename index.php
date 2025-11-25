<?php 
// Session Start
session_start();

// Connection to Database
require 'config.php';

// Generate CSRF Token
if(!isset($_SESSION['__csrf'])){
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}   

// Store Errors in Session Variable
if(!isset($_SESSION['errors']) && isset($_SESSION['success'])){
    $_SESSION['errors'] = [];
    $_SESSION['success'] = [];
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmitted'])){
    // Verify CSRF Token
    if(!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])){
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Collect Data from Form
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = htmlspecialchars(trim($_POST['password']));

    // Validation
    if(empty($email) || empty($password)){
        $_SESSION['errors'][] = 'All Fields are required';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Fetch data from Database for Specific User
    $stmt = $conn->prepare("SELECT * FROM users_tbl WHERE user_email = :uemail");
    $stmt->bindParam(':uemail', $email);
    $stmt->execute();
    $user =  $stmt->fetchAll();

    // Verify user email
    if(!$user){
        $_SESSION['errors'][] = 'Invalid Email or Password';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Verify user password
    if(password_verify($password, $user['user_password'])){
        $_SESSION['errors'][] = 'Invalid Email or Password';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Store user data into the session variable
    $_SESSION['loggedIn']   = true;
    $_SESSION['userId']     = $user['id'];
    $_SESSION['userName']   = $user['user_fullname'];
    $_SESSION['userEmail']  = $user['user_email'];

    // Redirected to Home page 
    header('Location: gallery/index.php');
    exit;

}

// Store Success and Errors in variable
$message = $_SESSION['success'] ?? [] ; 
$_SESSION['success'] = [];

$errors = $_SESSION['errors'] ?? [] ; 
$_SESSION['errors'] = [];


require 'header.php';
?>

<body class=" bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <!-- Logo/Brand Section -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1 text-dark">Image Gallery</h2>
                    <p class="text-muted mb-0">Your Beautiful image Gallery is one step away</p>
                </div>

                <!-- Show Success Message -->
                <?php if(!empty($message)): ?>
                <?php foreach($message as $msg): ?>
                <div class="alert alert-success text-center" role="alert">
                    <?= $msg ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- Show Errors Message -->
                <?php if(!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?= $error ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- Login Card -->
                <div class="card shadow border-0">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="card-title text-center mb-2 fw-bold">Welcome Back</h3>
                        <p class="text-center text-muted mb-4">Please login to your account</p>
                        <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                            <!-- Email Input -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="your.email@example.com" autofocus>
                            </div>

                            <!-- Password Input -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter your password">
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>
                                <a href="#" class="text-decoration-none">Forgot password?</a>
                            </div>

                            <!-- Login Button -->
                            <div class="d-grid mb-3">
                                <button name="isSubmitted" type="submit" class="btn btn-dark btn-lg">
                                    Login
                                </button>
                            </div>
                        </form>

                        <!-- Registration Link -->
                        <div class="text-center">
                            <p class="mb-0 text-muted">Don't have an account?
                                <a href="register.php" class="text-decoration-none">
                                    Create Account
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require 'footer.php' ?>