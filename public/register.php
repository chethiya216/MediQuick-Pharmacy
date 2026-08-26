<?php

require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/security.php';


/*
|--------------------------------------------------------------------------
| Already Logged In
|--------------------------------------------------------------------------
*/

if (isLoggedIn()) {

    redirectByRole();
}


$message = '';

$success = '';


/*
|--------------------------------------------------------------------------
| REGISTRATION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
    |--------------------------------------------------------------------------
    | GET FORM DATA
    |--------------------------------------------------------------------------
    */

    $accountType =
        $_POST['account_type'] ?? '';

    $firstName =
        trim($_POST['first_name'] ?? '');

    $lastName =
        trim($_POST['last_name'] ?? '');

    $email =
        strtolower(
            trim($_POST['email'] ?? '')
        );

    $phone =
        trim($_POST['phone'] ?? '');

    $address =
        trim($_POST['address'] ?? '');

    $dateOfBirth =
        trim($_POST['date_of_birth'] ?? '');

    $staffRole =
        $_POST['staff_role'] ?? '';

    $password =
        $_POST['password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        !in_array(
            $accountType,
            ['customer', 'staff'],
            true
        )
    ) {

        $message =
            "Please select an account type.";

    } elseif ($firstName === '') {

        $message =
            "First name is required.";

    } elseif ($lastName === '') {

        $message =
            "Last name is required.";

    } elseif (!validEmail($email)) {

        $message =
            "Please enter a valid email.";

    } elseif (strlen($password) < 8) {

        $message =
            "Password must be at least 8 characters.";

    } elseif (
        $password !== $confirmPassword
    ) {

        $message =
            "Passwords do not match.";

    } elseif (
        $accountType === 'staff'
        &&
        !in_array(
            $staffRole,
            ['admin', 'pharmacist'],
            true
        )
    ) {

        $message =
            "Please select Admin or Pharmacist.";
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK EMAIL
    |--------------------------------------------------------------------------
    */

    if ($message === '') {


        $customerCheck = sqlsrv_query(
            $conn,
            "
            SELECT TOP 1 customer_id
            FROM CUSTOMER
            WHERE email = ?
            ",
            [$email]
        );


        $staffCheck = sqlsrv_query(
            $conn,
            "
            SELECT TOP 1 staff_id
            FROM staff
            WHERE email = ?
            ",
            [$email]
        );


        if (
            $customerCheck === false
            ||
            $staffCheck === false
        ) {

            $message =
                "Database error.";

        } elseif (
            sqlsrv_has_rows($customerCheck)
            ||
            sqlsrv_has_rows($staffCheck)
        ) {

            $message =
                "This email is already registered.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | HASH PASSWORD
            |--------------------------------------------------------------------------
            */

            $passwordHash =
                password_hash(
                    $password,
                    PASSWORD_BCRYPT
                );


            /*
            |--------------------------------------------------------------------------
            | CUSTOMER
            |--------------------------------------------------------------------------
            */

            if (
                $accountType === 'customer'
            ) {


                $customerId =
                    generateId('CUS');


                $sql = "
                    INSERT INTO CUSTOMER
                    (
                        customer_id,
                        first_name,
                        last_name,
                        email,
                        phone,
                        address,
                        date_of_birth,
                        password_hash,
                        status
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'active'
                    )
                ";


                $params = [

                    $customerId,

                    $firstName,

                    $lastName,

                    $email,

                    $phone !== ''
                        ? $phone
                        : null,

                    $address !== ''
                        ? $address
                        : null,

                    $dateOfBirth !== ''
                        ? $dateOfBirth
                        : null,

                    $passwordHash
                ];


            /*
            |--------------------------------------------------------------------------
            | STAFF
            |--------------------------------------------------------------------------
            */

            } else {


                $staffId =
                    generateId('STF');


                $sql = "
                    INSERT INTO staff
                    (
                        staff_id,
                        first_name,
                        last_name,
                        email,
                        password_hash,
                        role,
                        status
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'active'
                    )
                ";


                $params = [

                    $staffId,

                    $firstName,

                    $lastName,

                    $email,

                    $passwordHash,

                    $staffRole
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

            $stmt = sqlsrv_query(
                $conn,
                $sql,
                $params
            );


            if ($stmt === false) {

                $message =
                    "Registration failed.";

            } else {

                $success =
                    "Registration successful. You can now login.";

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
        Register - MediQuick Pharmacy
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-7 mt-4 mb-4">

            <div class="card shadow">

                <div class="card-body p-4">


                    <h2 class="text-center">
                        MediQuick Pharmacy
                    </h2>

                    <h4 class="text-center mb-4">
                        Create Account
                    </h4>


                    <!-- Error -->

                    <?php if ($message !== ''): ?>

                        <div
                            class="alert alert-danger"
                        >
                            <?= e($message) ?>
                        </div>

                    <?php endif; ?>


                    <!-- Success -->

                    <?php if ($success !== ''): ?>

                        <div
                            class="alert alert-success"
                        >
                            <?= e($success) ?>

                            <br>

                            <a href="login.php">
                                Go to Login
                            </a>

                        </div>

                    <?php endif; ?>


                    <form
                        method="POST"
                        action=""
                    >


                        <!-- ACCOUNT TYPE -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Account Type
                            </label>

                            <select
                                name="account_type"
                                id="account_type"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Account Type
                                </option>

                                <option value="customer">
                                    Customer
                                </option>

                                <option value="staff">
                                    Staff
                                </option>

                            </select>

                        </div>


                        <!-- STAFF ROLE -->

                        <div
                            class="mb-3"
                            id="staffRoleContainer"
                            style="display:none;"
                        >

                            <label
                                class="form-label"
                            >
                                Staff Role
                            </label>

                            <select
                                name="staff_role"
                                id="staff_role"
                                class="form-select"
                            >

                                <option value="">
                                    Select Staff Role
                                </option>

                                <option value="admin">
                                    Admin
                                </option>

                                <option value="pharmacist">
                                    Pharmacist
                                </option>

                            </select>

                        </div>


                        <!-- FIRST NAME -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                First Name
                            </label>

                            <input
                                type="text"
                                name="first_name"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- LAST NAME -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Last Name
                            </label>

                            <input
                                type="text"
                                name="last_name"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- EMAIL -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- PHONE -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Phone
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                            >

                        </div>


                        <!-- ADDRESS -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Address
                            </label>

                            <input
                                type="text"
                                name="address"
                                class="form-control"
                            >

                        </div>


                        <!-- DATE OF BIRTH -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Date of Birth
                            </label>

                            <input
                                type="date"
                                name="date_of_birth"
                                class="form-control"
                            >

                        </div>


                        <!-- PASSWORD -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="8"
                                required
                            >

                            <small class="text-muted">
                                Minimum 8 characters.
                            </small>

                        </div>


                        <!-- CONFIRM PASSWORD -->

                        <div class="mb-3">

                            <label
                                class="form-label"
                            >
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                minlength="8"
                                required
                            >

                        </div>


                        <!-- SUBMIT -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Register
                        </button>


                    </form>


                    <div class="text-center mt-3">

                        Already have an account?

                        <a href="login.php">
                            Login
                        </a>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| Show Staff Role Only For Staff
|--------------------------------------------------------------------------
*/

const accountType =
    document.getElementById('account_type');

const staffRoleContainer =
    document.getElementById(
        'staffRoleContainer'
    );

const staffRole =
    document.getElementById('staff_role');


function updateStaffRole()
{
    if (
        accountType.value === 'staff'
    ) {

        staffRoleContainer.style.display =
            'block';

        staffRole.required = true;

    } else {

        staffRoleContainer.style.display =
            'none';

        staffRole.required = false;

        staffRole.value = '';
    }
}


accountType.addEventListener(
    'change',
    updateStaffRole
);


updateStaffRole();

</script>


</body>

</html>