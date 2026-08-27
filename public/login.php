<?php

session_start();


require_once '../includes/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(
        trim($_POST['email'] ?? '')
    );

    $password = $_POST['password'] ?? '';


    if (
        $email === '' ||
        $password === ''
    ) {

        $message = "Please enter email and password.";
        $messageType = "error";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Try Customer
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT
                customer_id,
                first_name,
                last_name,
                email,
                password_hash,
                status
            FROM customers
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Customer Login
        |--------------------------------------------------------------------------
        */

        if ($user) {

            if ($user['status'] !== 'active') {

                $message = "Your account is not active.";
                $messageType = "error";

            } elseif (
                password_verify(
                    $password,
                    $user['password_hash']
                )
            ) {

                session_regenerate_id(true);

                $_SESSION['user_id'] =
                    $user['customer_id'];

                $_SESSION['first_name'] =
                    $user['first_name'];

                $_SESSION['last_name'] =
                    $user['last_name'];

                $_SESSION['email'] =
                    $user['email'];

                $_SESSION['role'] =
                    'customer';

                header("Location: ../public/index.html");

                exit;

            } else {

                $message = "Invalid email or password.";
                $messageType = "error";
            }


        } else {

            /*
            |--------------------------------------------------------------------------
            | Try Staff
            |--------------------------------------------------------------------------
            */

            $sql = "
                SELECT
                    staff_id,
                    first_name,
                    last_name,
                    email,
                    password_hash,
                    role,
                    status
                FROM staff
                WHERE email = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();

            $staff = $result->fetch_assoc();

            $stmt->close();


            if (!$staff) {

                $message =
                    "Invalid email or password.";

                $messageType = "error";

            } elseif ($staff['status'] !== 'active') {

                $message =
                    "Your account is not active.";

                $messageType = "error";

            } elseif (
                password_verify(
                    $password,
                    $staff['password_hash']
                )
            ) {

                session_regenerate_id(true);

                $_SESSION['user_id'] =
                    $staff['staff_id'];

                $_SESSION['first_name'] =
                    $staff['first_name'];

                $_SESSION['last_name'] =
                    $staff['last_name'];

                $_SESSION['email'] =
                    $staff['email'];

                $_SESSION['role'] =
                    $staff['role'];


                /*
                | Staff goes to admin dashboard
                */

                header(
                    "Location: ../public/admin/index.php"
                );

                exit;

            } else {

                $message =
                    "Invalid email or password.";

                $messageType = "error";
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

    <title>Login</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            padding: 50px 20px;
        }

        .container {
            max-width: 400px;
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

        input {
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

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            margin-bottom: 15px;
        }

        .register {
            text-align: center;
            margin-top: 20px;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Login</h2>


    <?php if ($message !== ''): ?>

        <div class="<?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>Email</label>

        <input
            type="email"
            name="email"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            required
        >


        <button type="submit">
            Login
        </button>

    </form>


    <div class="register">

        Don't have an account?

        <a href="register.php">
            Register
        </a>

    </div>

</div>

</body>

</html>