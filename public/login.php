<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_functions.php';


/*
|--------------------------------------------------------------------------
| If Already Logged In
|--------------------------------------------------------------------------
*/

if (isLoggedIn()) {

    $role = $_SESSION['role'] ?? '';

    switch ($role) {

        case 'customer':
            header(
                "Location: customer/dashboard.php"
            );
            exit;

        case 'admin':
            header(
                "Location: admin/dashboard.php"
            );
            exit;

        case 'pharmacist':
            header(
                "Location: pharmacist/dashboard.php"
            );
            exit;

        case 'superadmin':
            header(
                "Location: superadmin/dashboard.php"
            );
            exit;
    }
}


$error = '';


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email =
        strtolower(
            trim(
                $_POST['email'] ?? ''
            )
        );

    $password =
        (string)(
            $_POST['password'] ?? ''
        );


    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    $validationError =
        validateLoginInput(
            $email,
            $password
        );


    if ($validationError !== null) {

        $error =
            $validationError;

    } else {


        /*
        |--------------------------------------------------------------------------
        | STEP 1
        | CUSTOMER
        |--------------------------------------------------------------------------
        */

        $customer =
            findCustomerByEmail(
                $conn,
                $email
            );


        /*
        |--------------------------------------------------------------------------
        | CUSTOMER FOUND
        |--------------------------------------------------------------------------
        */

        if ($customer) {


            /*
            |--------------------------------------------------------------------------
            | Check Status
            |--------------------------------------------------------------------------
            */

            if (
                $customer['status']
                !== 'active'
            ) {

                $error =
                    "This account is not active.";

            }


            /*
            |--------------------------------------------------------------------------
            | Verify Password
            |--------------------------------------------------------------------------
            */

            elseif (
                password_verify(
                    $password,
                    $customer['password_hash']
                )
            ) {


                /*
                |--------------------------------------------------------------------------
                | LOGIN CUSTOMER
                |--------------------------------------------------------------------------
                */

                loginCustomer(
                    $customer
                );


                /*
                |--------------------------------------------------------------------------
                | CUSTOMER DASHBOARD
                |--------------------------------------------------------------------------
                */

                header(
                    "Location: customer/dashboard.php"
                );

                exit;

            } else {

                $error =
                    "Invalid email or password.";
            }


        /*
        |--------------------------------------------------------------------------
        | NO CUSTOMER
        | CHECK STAFF
        |--------------------------------------------------------------------------
        */

        } else {


            /*
            |--------------------------------------------------------------------------
            | STEP 2
            | STAFF
            |--------------------------------------------------------------------------
            */

            $staff =
                findStaffByEmail(
                    $conn,
                    $email
                );


            /*
            |--------------------------------------------------------------------------
            | STAFF FOUND
            |--------------------------------------------------------------------------
            */

            if ($staff) {


                /*
                |--------------------------------------------------------------------------
                | Check Status
                |--------------------------------------------------------------------------
                */

                if (
                    $staff['status']
                    !== 'active'
                ) {

                    $error =
                        "This staff account is not active.";

                }


                /*
                |--------------------------------------------------------------------------
                | Verify Password
                |--------------------------------------------------------------------------
                */

                elseif (
                    password_verify(
                        $password,
                        $staff['password_hash']
                    )
                ) {


                    /*
                    |--------------------------------------------------------------------------
                    | LOGIN STAFF
                    |--------------------------------------------------------------------------
                    */

                    loginStaff(
                        $staff
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 3
                    | CHECK ROLE
                    |--------------------------------------------------------------------------
                    */

                    switch (
                        strtolower(
                            $staff['role']
                        )
                    ) {

                        case 'admin':

                            header(
                                "Location: admin/dashboard.php"
                            );

                            exit;


                        case 'pharmacist':

                            header(
                                "Location: pharmacist/dashboard.php"
                            );

                            exit;


                        case 'superadmin':

                            header(
                                "Location: superadmin/dashboard.php"
                            );

                            exit;


                        default:

                            $error =
                                "Invalid staff role.";

                            break;
                    }

                } else {

                    $error =
                        "Invalid email or password.";
                }

            } else {

                $error =
                    "Invalid email or password.";
            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Login | MediQuick Pharmacy
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-5 mt-5">

            <div class="card shadow">

                <div class="card-body p-4">


                    <h2 class="text-center">
                        MediQuick Pharmacy
                    </h2>

                    <p class="text-center text-muted">
                        Login to your account
                    </p>


                    <?php if ($error !== ''): ?>

                        <div
                            class="alert alert-danger"
                        >

                            <?= htmlspecialchars(
                                $error
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <form
                        method="POST"
                        action=""
                    >


                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Email
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Login
                        </button>


                    </form>


                    <div class="text-center mt-3">

                        Don't have an account?

                        <a href="register.php">
                            Register
                        </a>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


</body>

</html>