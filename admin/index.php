<?php
// Session Start
session_start();

// Check if User is already Exists
if (isset($_SESSION['AdminLoggedIn']) == true) {
    // Redirected to Home page 
    header('Location: all_user.php');
    exit;
}

// Connection to Database
require '../config.php';

// Generate CSRF Token
if (!isset($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Store Errors in Session Variable
if (!isset($_SESSION['errors']) && isset($_SESSION['success'])) {
    $_SESSION['errors'] = [];
    $_SESSION['success'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmitted'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: ' . basename(__FILE__));
        exit;
    }
    try {

        // Collect Data from Form
        $userName = htmlspecialchars(trim($_POST['username']));
        $password = htmlspecialchars(trim($_POST['password']));

        // Validation
        if (empty($userName) || empty($password)) {
            $_SESSION['errors'][] = 'All Fields are required';
            header('Location: ' . basename(__FILE__));
            exit;
        }

        // Fetch data from Database for Specific User
        $stmt = $conn->prepare("SELECT * FROM admin_tbl WHERE user_name = :uname");
        $stmt->bindParam(':uname', $userName);
        $stmt->execute();
        $user =  $stmt->fetch();

        // Verify username
        if (!$user) {
            $_SESSION['errors'][] = 'Invalid Username or Password';
            header('Location: ' . basename(__FILE__));
            exit;
        }

        // Verify user password
        if (!password_verify($password, $user['user_password'])) {
            $_SESSION['errors'][] = 'Invalid Username or Password';
            header('Location: ' . basename(__FILE__));
            exit;
        }

        // Store user data into the session variable
        $_SESSION['AdminLoggedIn']   = true;
        $_SESSION['AdminUserId']     = $user['id'];
        $_SESSION['AdminUserName']   = $user['user_name'];

        // Redirected to Home page 
        header('Location: all_user.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['errors'][] = 'Error in login ' . $e->getMessage();
        header('Location: ' . basename(__FILE__));
        exit;
    }
}

// Store Success and Errors in variable
$message = $_SESSION['success'] ?? [];
$_SESSION['success'] = [];

$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ratnews Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4 p-sm-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-gradient text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="bi bi-newspaper fs-1"></i>
                            </div>
                            <h4 class="fw-semibold text-dark">Image-Gallery</h4>
                            <p class="text-muted mb-0">Sign In to Dashboard</p>
                        </div>

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

                        <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-medium">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-fill text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="username"
                                        name="username" placeholder="Enter your username" autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password"
                                        name="password" placeholder="Enter your password">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                    <label class="form-check-label" for="rememberMe">
                                        Remember Me
                                    </label>
                                </div>
                                <a href="#" class="text-primary text-decoration-none small">Forgot
                                    Password?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="isSubmitted" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<?php $conn = null; ?>