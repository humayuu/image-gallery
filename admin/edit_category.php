<?php
require 'header.php'

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
                            <form" class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Category name">
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button name="issSubmitted" class="btn btn-primary">Update Category</button>
                                    </div>
                                </div>
                                </form>
                                <!-- Display Errors -->
                                <?php if (!empty($errors)): ?>
                                <div class="row mt-3 justify-content-center">
                                    <div class="col-md-12 col-lg-12">
                                        <?php foreach ($errors as $error): ?>
                                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                                            <span><?= htmlspecialchars($error) ?></span>
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