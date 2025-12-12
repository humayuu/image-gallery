<?php
// Session Start
session_start();

// Connection to Database
require '../config.php';


// Initialize Session for Store Error and Success Message
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = [];
}


$sl = 1;


// Fetch All users
try {

    $stmt = $conn->prepare('SELECT * FROM users_tbl ORDER BY id DESC');
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $_SESSION['message'][] = 'Error in Fetch: ' . $e->getMessage();
}

// Get messages and clear after displaying
$message = $_SESSION['message'] ?? [];
$_SESSION['message'] = [];

require './header.php';
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
                    <li class="breadcrumb-item active" aria-current="page">All Users</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0">All Users</h6>
        </div>
        <!-- Display Error Messages -->
        <div class="card-body">
            <div class="row">
                <!-- Display Errors -->
                <?php if (!empty($message)): ?>
                    <div class="row mt-3 justify-content-center">
                        <div class="col-md-12 col-lg-12">
                            <?php foreach ($message as $msg): ?>
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <span><?= htmlspecialchars($msg) ?></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="col-12 col-lg-12 d-flex">
                    <div class="card border shadow-none w-100">
                        <div class="card-body">
                            <div class="table-responsive">
                                <?php if ($users): ?>
                                    <table class="table align-middle">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th>#</th>
                                                <th>Fullname</th>
                                                <th>Email</th>
                                                <th>Total Uploads</th>
                                                <th>Status</th>
                                                <th>Admin Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr class="text-center fs-6">
                                                    <td><?= $sl++ ?></td>
                                                    <td><?= htmlspecialchars($user['user_fullname']) ?></td>
                                                    <td><?= htmlspecialchars($user['user_email']) ?></td>
                                                    <td>
                                                        <?php
                                                        $query = $conn->prepare('SELECT COUNT(*) as total FROM gallery_tbl WHERE user_id = :uid');
                                                        $query->bindParam(':uid', $user['id']);
                                                        $query->execute();
                                                        $result = $query->fetch();
                                                        echo $result['total'];
                                                        ?>

                                                    </td>
                                                    <td>
                                                        <?php if (htmlspecialchars($user['user_status']) == 'Active'): ?>
                                                            <span class="badge text-bg-success fs-5">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge text-bg-light fs-5">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (htmlspecialchars($user['user_admin_status']) == '1'): ?>
                                                            <span class="badge text-bg-success fs-5">Online</span>
                                                        <?php else: ?>
                                                            <span class="badge text-bg-light fs-5">Offline</span>
                                                        <?php endif; ?>
                                                    </td>


                                                    <td>
                                                        <div class="m-2 fs-5">
                                                            <?php
                                                            $icon = ($user['user_status'] == 'Active') ? 'thumbs-up' : 'thumbs-down';

                                                            ?>

                                                            <a href="user_status.php?id=<?= htmlspecialchars($user['id']) ?>"
                                                                class="text-primary" data-bs-toggle="tooltip"
                                                                data-bs-placement="bottom" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
                                                                    class="bi bi-hand-<?= $icon ?>-fill"></i></a>


                                                            <a href="user_delete.php?id=<?= htmlspecialchars($user['id']) ?>"
                                                                class="text-danger" onclick="return confirm('Are you Sure?')"
                                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
                                                                    class="bi bi-trash-fill"></i></a>


                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="alert alert-danger fade show" role="alert">
                                        <span>No Users found!</span>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>

    </div>

</main>
<!--end page main-->
<?php require './footer.php' ?>