<?php
require 'header.php';
?>
<!--start content-->
<main class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Ratnews</div>
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
                <div class="col-12 col-lg-12 d-flex">
                    <div class="card border shadow-none w-100">
                        <div class="card-body">
                            <div class="table-responsive">
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
                                        <tr class="text-center fs-6">
                                            <td>1</td>
                                            <td>dsassd</td>
                                            <td>dsassd</td>
                                            <td>dsassd</td>
                                            <td>dsassd</td>

                                            <td class="text-primary fs-5">Online</td>

                                            <td>
                                                <div class="m-2 fs-5">
                                                    <a href="#" class="text-<?= $class ?> me-3" data-bs-toggle="tooltip"
                                                        data-bs-placement="bottom" title=""
                                                        data-bs-original-title="Status" aria-label="Status"><i
                                                            class="bi bi-hand-<?= $icon ?>-fill"></i></a>
                                                    <a href="#" class="text-danger"
                                                        onclick="return confirm('Are you Sure?')"
                                                        data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                                        data-bs-original-title="Delete" aria-label="Delete"><i
                                                            class="bi bi-trash-fill"></i></a>
                                                </div>
                                            </td>


                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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