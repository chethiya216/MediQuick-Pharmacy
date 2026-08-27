<?php

require_once '../../includes/auth.php';

requireAdmin();

require_once '../../includes/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim(
        $_POST['first_name'] ?? ''
    );

    $lastName = trim(
        $_POST['last_name'] ?? ''
    );

    $email = strtolower(
        trim($_POST['email'] ?? '')
    );

    $password = $_POST['password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';

    $role = strtolower(
        trim($_POST['role'] ?? '')
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $firstName === '' ||
        $lastName === '' ||
        $email === '' ||
        $password === '' ||
        $confirmPassword === '' ||
        $role === ''
    ) {

        $message =
            "Please fill in all required fields.";

        $messageType = "error";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

        $messageType = "error";

    } elseif (
        $password !== $confirmPassword
    ) {

        $message =
            "Passwords do not match.";

        $messageType = "error";

    } elseif (
        strlen($password) < 8
    ) {

        $message =
            "Password must be at least 8 characters.";

        $messageType = "error";

    } elseif (
        !in_array(
            $role,
            [
                'admin',
                'pharmacist',
                'superadmin'
            ],
            true
        )
    ) {

        $message =
            "Invalid staff role.";

        $messageType = "error";

    } elseif (
        $role === 'superadmin' &&
        getUserRole() !== 'superadmin'
    ) {

        $message =
            "Only a superadmin can create another superadmin.";

        $messageType = "error";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Customer Email
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT customer_id
            FROM customers
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->store_result();

        $customerExists =
            $stmt->num_rows > 0;

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Check Staff Email
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT staff_id
            FROM staff
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->store_result();

        $staffExists =
            $stmt->num_rows > 0;

        $stmt->close();


        if (
            $customerExists ||
            $staffExists
        ) {

            $message =
                "This email is already registered.";

            $messageType = "error";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Create Staff
            |--------------------------------------------------------------------------
            */

            $staffId =
                'STF-' .
                strtoupper(
                    bin2hex(
                        random_bytes(4)
                    )
                );

            $passwordHash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


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
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ";

            $stmt =
                $conn->prepare($sql);


            if (!$stmt) {

                $message =
                    "Database error: " .
                    $conn->error;

                $messageType = "error";

            } else {

                $stmt->bind_param(
                    "ssssss",
                    $staffId,
                    $firstName,
                    $lastName,
                    $email,
                    $passwordHash,
                    $role
                );


                if ($stmt->execute()) {

                    $message =
                        "Staff account created successfully.";

                    $messageType =
                        "success";

                } else {

                    $message =
                        "Failed to create staff account: " .
                        $stmt->error;

                    $messageType =
                        "error";
                }


                $stmt->close();
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

    <title>Create Staff Account</title>


    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            padding: 40px 20px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,.1);
        }

        h2 {
            text-align: center;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            background: #007bff;
            color: white;
            cursor: pointer;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
        }

        .back {
            margin-top: 20px;
            text-align: center;
        }

    </style>

</head>


<body>


<div class="container">


    <h2>
        Create Staff Account
    </h2>


    <?php if ($message !== ''): ?>

        <div class="<?= htmlspecialchars($messageType) ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <label>
            First Name
        </label>

        <input
            type="text"
            name="first_name"
            required
        >


        <label>
            Last Name
        </label>

        <input
            type="text"
            name="last_name"
            required
        >


        <label>
            Email
        </label>

        <input
            type="email"
            name="email"
            required
        >


        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            minlength="8"
            required
        >


        <label>
            Confirm Password
        </label>

        <input
            type="password"
            name="confirm_password"
            minlength="8"
            required
        >


        <label>
            Staff Role
        </label>

        <select
            name="role"
            required
        >

            <option value="">
                Select role
            </option>

            <option value="admin">
                Admin
            </option>

            <option value="pharmacist">
                Pharmacist
            </option>

            <?php if (getUserRole() === 'superadmin'): ?>

                <option value="superadmin">
                    Superadmin
                </option>

            <?php endif; ?>

        </select>


        <button type="submit">
            Create Staff Account
        </button>


    </form>


    <div class="back">

        <a href="../admin/index.php">
            Back to Dashboard
        </a>

        &nbsp; | &nbsp;

        <a href="../logout.php">
            Logout
        </a>

    </div>


</div>


</body>

</html>