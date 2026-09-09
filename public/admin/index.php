<?php

require_once '../../includes/auth.php';

requireAdmin();

?>

<!doctype html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<?php include 'includes/head.php'; ?>

<body>
    <!-- Standard Sneat Wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar Included Here -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Page Container (Sits to the right of Sidebar) -->
            <div class="layout-page">

                <!-- Header Included Inside layout-page (Spans top right area flush) -->
                <?php include 'includes/header.php'; ?>

                <!-- Content Wrapper -->
                <div class="content-wrapper">
                    
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <!-- Left Column: Banner + Transactions -->
                            <div class="col-lg-8 col-md-8 order-0 mb-4">
                                <div class="row">
                                    <!-- 1. Congratulations Card -->
                                    <div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="d-flex align-items-end row">
                                                <div class="col-sm-7">
                                                    <div class="card-body">
                                                        <h5 class="card-title text-primary">
                                                            Congratulations John! 🎉
                                                        </h5>
                                                        <p class="mb-4">
                                                            You have done <span class="fw-bold">72%</span> more
                                                            sales today. Check your new badge in your profile.
                                                        </p>

                                                        <a href="javascript:;" class="btn btn-sm btn-outline-primary">View Badges</a>
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 text-center text-sm-left">
                                                    <div class="card-body pb-0 px-0 px-md-4">
                                                        <img
                                                            src="../assets/img/illustrations/man-with-laptop-light.png"
                                                            height="140"
                                                            alt="View Badge User"
                                                            data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                                            data-app-light-img="illustrations/man-with-laptop-light.png"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Transactions Card (Same size/width directly below Banner) -->
                                    <div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="card-header d-flex align-items-center justify-content-between">
                                                <h5 class="card-title m-0 me-2">Transactions</h5>
                                                <div class="dropdown">
                                                    <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                                                        <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                                                        <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                                        <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <ul class="p-0 m-0">
                                                    <li class="d-flex mb-4 pb-1">
                                                        <div class="avatar flex-shrink-0 me-3">
                                                            <img src="../assets/img/icons/unicons/paypal.png" alt="User" class="rounded" />
                                                        </div>
                                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                            <div class="me-2">
                                                                <small class="text-muted d-block mb-1">Paypal</small>
                                                                <h6 class="mb-0">Send money</h6>
                                                            </div>
                                                            <div class="user-progress d-flex align-items-center gap-1">
                                                                <h6 class="mb-0">+82.6</h6>
                                                                <span class="text-muted">USD</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="d-flex mb-4 pb-1">
                                                        <div class="avatar flex-shrink-0 me-3">
                                                            <img src="../assets/img/icons/unicons/wallet.png" alt="User" class="rounded" />
                                                        </div>
                                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                            <div class="me-2">
                                                                <small class="text-muted d-block mb-1">Wallet</small>
                                                                <h6 class="mb-0">Mac'D</h6>
                                                            </div>
                                                            <div class="user-progress d-flex align-items-center gap-1">
                                                                <h6 class="mb-0">+270.69</h6>
                                                                <span class="text-muted">USD</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="d-flex mb-4 pb-1">
                                                        <div class="avatar flex-shrink-0 me-3">
                                                            <img src="../assets/img/icons/unicons/chart.png" alt="User" class="rounded" />
                                                        </div>
                                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                            <div class="me-2">
                                                                <small class="text-muted d-block mb-1">Transfer</small>
                                                                <h6 class="mb-0">Refund</h6>
                                                            </div>
                                                            <div class="user-progress d-flex align-items-center gap-1">
                                                                <h6 class="mb-0">+637.91</h6>
                                                                <span class="text-muted">USD</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="d-flex mb-4 pb-1">
                                                        <div class="avatar flex-shrink-0 me-3">
                                                            <img src="../assets/img/icons/unicons/cc-success.png" alt="User" class="rounded" />
                                                        </div>
                                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                            <div class="me-2">
                                                                <small class="text-muted d-block mb-1">Credit Card</small>
                                                                <h6 class="mb-0">Ordered Food</h6>
                                                            </div>
                                                            <div class="user-progress d-flex align-items-center gap-1">
                                                                <h6 class="mb-0">-838.71</h6>
                                                                <span class="text-muted">USD</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="d-flex mb-4 pb-1">
                                                        <div class="avatar flex-shrink-0 me-3">
                                                            <img src="../assets/img/icons/unicons/wallet.png" alt="User" class="rounded" />
                                                        </div>
                                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                            <div class="me-2">
                                                                <small class="text-muted d-block mb-1">Wallet</small>
                                                                <h6 class="mb-0">Starbucks</h6>
                                                            </div>
                                                            <div class="user-progress d-flex align-items-center gap-1">
                                                                <h6 class="mb-0">+203.33</h6>
                                                                <span class="text-muted">USD</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="d-flex">
                                                        <div class="avatar flex-shrink-0 me-3">
                                                            <img src="../assets/img/icons/unicons/cc-warning.png" alt="User" class="rounded" />
                                                        </div>
                                                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                            <div class="me-2">
                                                                <small class="text-muted d-block mb-1">Mastercard</small>
                                                                <h6 class="mb-0">Ordered Food</h6>
                                                            </div>
                                                            <div class="user-progress d-flex align-items-center gap-1">
                                                                <h6 class="mb-0">-92.45</h6>
                                                                <span class="text-muted">USD</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: 4 Small Cards Together in a 2x2 Grid + Profile Report -->
                            <div class="col-lg-4 col-md-4 order-1 mb-4">
                                <div class="row">
                                    <!-- Card 1: Profit -->
                                    <div class="col-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between">
                                                    <div class="avatar flex-shrink-0">
                                                        <img src="../assets/img/icons/unicons/chart-success.png" alt="chart success" class="rounded" />
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                                            <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1">Profit</span>
                                                <h3 class="card-title mb-2">$12,628</h3>
                                                <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +72.80%</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 2: Sales -->
                                    <div class="col-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between">
                                                    <div class="avatar flex-shrink-0">
                                                        <img src="../assets/img/icons/unicons/wallet-info.png" alt="Credit Card" class="rounded" />
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                                                            <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span>Sales</span>
                                                <h3 class="card-title text-nowrap mb-1">$4,679</h3>
                                                <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +28.42%</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 3: Payments -->
                                    <div class="col-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between">
                                                    <div class="avatar flex-shrink-0">
                                                        <img src="../assets/img/icons/unicons/paypal.png" alt="Credit Card" class="rounded" />
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                                                            <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="d-block mb-1">Payments</span>
                                                <h3 class="card-title text-nowrap mb-2">$2,456</h3>
                                                <small class="text-danger fw-semibold"><i class="bx bx-down-arrow-alt"></i> -14.82%</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 4: Transactions Metric -->
                                    <div class="col-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between">
                                                    <div class="avatar flex-shrink-0">
                                                        <img src="../assets/img/icons/unicons/cc-primary.png" alt="Credit Card" class="rounded" />
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="cardOpt1">
                                                            <a class="dropdown-item" href="javascript:void(0);">View More</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1">Transactions</span>
                                                <h3 class="card-title mb-2">$14,857</h3>
                                                <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +28.14%</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Profile Report Card -->
                                    <div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
                                                    <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                                        <div class="card-title">
                                                            <h5 class="text-nowrap mb-2">Profile Report</h5>
                                                            <span class="badge bg-label-warning rounded-pill">Year 2021</span>
                                                        </div>
                                                        <div class="mt-sm-auto">
                                                            <small class="text-success text-nowrap fw-semibold"><i class="bx bx-chevron-up"></i> 68.2%</small>
                                                            <h3 class="mb-0">$84,686k</h3>
                                                        </div>
                                                    </div>
                                                    <div id="profileReportChart"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Included Here -->
                    <?php include 'includes/footer.php'; ?>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>

        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

</body>

</html>