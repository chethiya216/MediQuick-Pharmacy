<?php



error_reporting(E_ALL);
ini_set('display_errors', 1);




require_once __DIR__ . '/../../includes/db.php';



if (!isset($conn)) {
    die("Database connection failed: \$conn is not defined.");
}




$sql = "
    SELECT
        p.payment_id,
        p.order_id,
        c.first_name,
        c.last_name,
        p.payment_method,
        p.amount,
        p.payment_status,
        p.paid_at
    FROM payments p
    JOIN orders o
        ON p.order_id = o.order_id
    JOIN customers c
        ON o.customer_id = c.customer_id
    ORDER BY p.payment_id DESC
";

$result = mysqli_query($conn, $sql);




if (!$result) {
    die(
        "Payment query failed:<br>" .
        htmlspecialchars(mysqli_error($conn))
    );
}

?>

<!DOCTYPE html>

<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../admin-assets/assets/"
    data-template="vertical-menu-template-free"
>



<?php require_once __DIR__ . '/includes/head.php'; ?>


<body>




<div class="layout-wrapper layout-content-navbar">

    <div class="layout-container">


     

        <?php require_once __DIR__ . '/includes/sidebar.php'; ?>


  

        <div class="layout-page">


            <!-- =================================================
                 HEADER
            ================================================== -->

            <?php require_once __DIR__ . '/includes/header.php'; ?>


            <!-- =================================================
                 CONTENT WRAPPER
            ================================================== -->

            <div class="content-wrapper">


                <!-- =================================================
                     CONTENT
                ================================================== -->

                <div class="container-xxl flex-grow-1 container-p-y">


                    <!-- =================================================
                         PAGE TITLE
                    ================================================== -->

                    <h4 class="fw-bold py-3 mb-4">

                        <span class="text-muted fw-light">
                            Payments /
                        </span>

                        Payment Management

                    </h4>


                    <!-- =================================================
                         PAYMENT CARD
                    ================================================== -->

                    <div class="card">


                        <!-- CARD HEADER -->

                        <div class="card-header">

                            <h5 class="mb-0">
                                Payment Management
                            </h5>

                        </div>


                        <!-- =================================================
                             TABLE
                        ================================================== -->

                        <div class="table-responsive text-nowrap">

                            <table class="table table-hover">


                                <!-- TABLE HEADER -->

                                <thead>

                                    <tr>

                                        <th>
                                            Payment ID
                                        </th>

                                        <th>
                                            Order ID
                                        </th>

                                        <th>
                                            Customer
                                        </th>

                                        <th>
                                            Method
                                        </th>

                                        <th>
                                            Amount
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Paid Date
                                        </th>

                                    </tr>

                                </thead>


                                <!-- =================================================
                                     TABLE BODY
                                ================================================== -->

                                <tbody class="table-border-bottom-0">


                                <?php if (mysqli_num_rows($result) > 0): ?>


                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>


                                        <tr>


                                            <!-- PAYMENT ID -->

                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $row['payment_id']
                                                    ); ?>

                                                </strong>

                                            </td>


                                            <!-- ORDER ID -->

                                            <td>

                                                <strong>

                                                    #<?= htmlspecialchars(
                                                        $row['order_id']
                                                    ); ?>

                                                </strong>

                                            </td>


                                            <!-- CUSTOMER -->

                                            <td>

                                                <?= htmlspecialchars(
                                                    $row['first_name'] .
                                                    ' ' .
                                                    $row['last_name']
                                                ); ?>

                                            </td>


                                            <!-- PAYMENT METHOD -->

                                            <td>

                                                <?= htmlspecialchars(
                                                    $row['payment_method']
                                                ); ?>

                                            </td>


                                            <!-- AMOUNT -->

                                            <td>

                                                Rs.
                                                <?= number_format(
                                                    (float) $row['amount'],
                                                    2
                                                ); ?>

                                            </td>


                                            <!-- STATUS -->

                                            <td>

                                                <?php

                                                $status = strtolower(
                                                    trim(
                                                        $row['payment_status']
                                                    )
                                                );


                                                if (
                                                    $status === 'paid' ||
                                                    $status === 'completed'
                                                ) {

                                                    $badge =
                                                        'bg-label-success';

                                                } elseif (
                                                    $status === 'pending'
                                                ) {

                                                    $badge =
                                                        'bg-label-warning';

                                                } elseif (
                                                    $status === 'failed' ||
                                                    $status === 'cancelled'
                                                ) {

                                                    $badge =
                                                        'bg-label-danger';

                                                } else {

                                                    $badge =
                                                        'bg-label-secondary';

                                                }

                                                ?>


                                                <span
                                                    class="badge <?= $badge; ?>"
                                                >

                                                    <?= htmlspecialchars(
                                                        $row['payment_status']
                                                    ); ?>

                                                </span>

                                            </td>


                                            <!-- PAID DATE -->

                                            <td>

                                                <?php

                                                if (!empty($row['paid_at'])) {

                                                    echo htmlspecialchars(
                                                        $row['paid_at']
                                                    );

                                                } else {

                                                    echo '-';

                                                }

                                                ?>

                                            </td>


                                        </tr>


                                    <?php endwhile; ?>


                                <?php else: ?>


                                    <!-- NO PAYMENTS -->

                                    <tr>

                                        <td
                                            colspan="7"
                                            class="text-center"
                                        >

                                            No payments found.

                                        </td>

                                    </tr>


                                <?php endif; ?>


                                </tbody>

                            </table>

                        </div>

                    </div>


                </div>


                <!-- =================================================
                     FOOTER
                ================================================== -->

                <?php require_once __DIR__ . '/includes/footer.php'; ?>


                <div class="content-backdrop fade"></div>


            </div>


        </div>


    </div>


    <!-- =================================================
         OVERLAY
    ================================================== -->

    <div class="layout-overlay layout-menu-toggle"></div>


</div>


</body>

</html>