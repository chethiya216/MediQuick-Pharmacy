<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : "MediQuick Pharmacy Portal"; ?></title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Theme Override Style -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Global Navigation Bar -->
    <header class="sticky-top shadow-sm">
        <nav class="navbar navbar-expand-lg navbar-light bg-white py-3">
            <div class="container">
                <!-- Brand Logo -->
                <a class="navbar-brand fw-bold text-primary fs-4" href="index.php">
                    <i class="fas fa-clinic-medical me-2"></i>MediQuick
                </a>

                <!-- Responsive Hamburger Menu Toggle Button -->
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarNav" aria-controls="mainNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation Links & Dropdowns -->
                <div class="collapse navbar-collapse" id="mainNavbarNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="shop.php">Store</a>
                        </li>
                        
                        <!-- Dropdown Menu Example -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle fw-medium" href="#" id="medicalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Services
                            </a>
                            <ul class="dropdown-menu border-0 shadow-sm rounded-3 py-2">
                                <li><a class="dropdown-item py-2" href="upload-prescription.php"><i class="fas fa-file-prescription me-2 text-primary"></i>Upload Prescription</a></li>
                                <li><a class="dropdown-item py-2" href="cart.php"><i class="fas fa-shopping-cart me-2 text-primary"></i>Shopping Cart</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2" href="tracking.php"><i class="fas fa-truck-medical me-2 text-primary"></i>Order Tracking</a></li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="contact.php">Contact Us</a>
                        </li>

                        <!-- Authentication Actions (Login/Register Links) -->
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <a class="btn btn-outline-primary rounded-pill px-4 fw-semibold w-100" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>