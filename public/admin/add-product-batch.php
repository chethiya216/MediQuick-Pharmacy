<?php

session_start();

require_once '../../includes/db.php';
require_once '../../includes/upload-helper.php';

$message = '';
$message_type = '';

$product_id = '';
$supplier_id = '';
$batch_number = '';
$quantity = '';
$purchase_price = '';
$selling_price = '';
$manufacture_date = '';
$expiry_date = '';
$received_date = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = (int)($_POST['product_id'] ?? 0);
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);

    $batch_number = trim($_POST['batch_number'] ?? '');

    $quantity = (int)($_POST['quantity'] ?? 0);

    $purchase_price = (float)($_POST['purchase_price'] ?? 0);
    $selling_price = (float)($_POST['selling_price'] ?? 0);

    $manufacture_date = $_POST['manufacture_date'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $received_date = $_POST['received_date'] ?? '';

    if ($product_id <= 0) {

        $message = 'Please select a product.';
        $message_type = 'danger';

    } elseif ($supplier_id <= 0) {

        $message = 'Please select a supplier.';
        $message_type = 'danger';

    } elseif ($batch_number === '') {

        $message = 'Please enter the batch number.';
        $message_type = 'danger';

    } elseif ($quantity <= 0) {

        $message = 'Quantity must be greater than 0.';
        $message_type = 'danger';

    } elseif ($purchase_price < 0) {

        $message = 'Purchase price cannot be negative.';
        $message_type = 'danger';

    } elseif ($selling_price < 0) {

        $message = 'Selling price cannot be negative.';
        $message_type = 'danger';

    } elseif ($expiry_date === '') {

        $message = 'Please select the expiry date.';
        $message_type = 'danger';

    } elseif ($received_date === '') {

        $message = 'Please select the received date.';
        $message_type = 'danger';

    } elseif (
        $manufacture_date !== '' &&
        $expiry_date <= $manufacture_date
    ) {

        $message = 'Expiry date must be after manufacture date.';
        $message_type = 'danger';

    } else {

        $invoice_file = null;

        if (
            isset($_FILES['invoice_file']) &&
            $_FILES['invoice_file']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            $invoiceUpload = uploadInvoiceImage(
                $_FILES['invoice_file']
            );

            if (!$invoiceUpload['success']) {

                $message = $invoiceUpload['error'];
                $message_type = 'danger';

            } else {

                $invoice_file = $invoiceUpload['filepath'];
            }
        }

        if ($message === '') {

            $sql = "
                INSERT INTO product_batches
                (
                    product_id,
                    supplier_id,
                    batch_number,
                    initial_quantity,
                    quantity_on_hand,
                    purchase_price,
                    selling_price,
                    manufacture_date,
                    expiry_date,
                    received_date,
                    invoice_file,
                    status
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                $message =
                    'Database error: ' .
                    $conn->error;

                $message_type = 'danger';

            } else {

                $stmt->bind_param(
                    "iisiiddssss",
                    $product_id,
                    $supplier_id,
                    $batch_number,
                    $quantity,
                    $quantity,
                    $purchase_price,
                    $selling_price,
                    $manufacture_date,
                    $expiry_date,
                    $received_date,
                    $invoice_file
                );

                if ($stmt->execute()) {

                    $stmt->close();

                    header(
                        'Location: manage-batch.php?success=added'
                    );

                    exit;

                } else {

                    $message =
                        'Failed to add batch: ' .
                        $stmt->error;

                    $message_type = 'danger';

                    $stmt->close();
                }
            }
        }
    }
}


$products = $conn->query("
    SELECT
        product_id,
        product_name,
        sku
    FROM products
    WHERE status = 'active'
    ORDER BY product_name ASC
");

if (!$products) {

    die(
        'Product query error: ' .
        $conn->error
    );
}


$suppliers = $conn->query("
    SELECT
        supplier_id,
        name
    FROM suppliers
    ORDER BY name ASC
");

if (!$suppliers) {

    die(
        'Supplier query error: ' .
        $conn->error
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

<head>

    <?php require_once 'includes/head.php'; ?>

    <title>Add Product Batch - MediQuick</title>

    <style>

        .batch-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .batch-page-header h4 {
            margin: 0;
        }

        .batch-actions {
            display: flex;
            gap: 8px;
        }

        .batch-card {
            margin-bottom: 18px;
        }

        .batch-card .card-header {
            padding: 16px 20px;
            font-weight: 600;
        }

        .batch-card .card-body {
            padding: 20px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #566a7f;
        }

        .required {
            color: #ff3e1d;
        }

        .invoice-box {
            border: 1px dashed #d9dee3;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
        }

        .invoice-box i {
            font-size: 38px;
            margin-bottom: 8px;
        }

        .invoice-box small {
            display: block;
            margin-top: 8px;
        }

        .price-input {
            position: relative;
        }

        .price-input .currency {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #8592a3;
            z-index: 2;
        }

        .price-input input {
            padding-left: 42px;
        }

        @media (max-width: 768px) {

            .batch-page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .batch-actions {
                width: 100%;
            }

            .batch-actions .btn {
                flex: 1;
            }

        }

    </style>

</head>

<body>

<div class="layout-wrapper layout-content-navbar">

    <div class="layout-container">

        <?php require_once 'includes/sidebar.php'; ?>

        <div class="layout-page">

            <?php require_once 'includes/header.php'; ?>

            <div class="content-wrapper">

                <div class="container-xxl flex-grow-1 container-p-y">


                    <?php if ($message !== ''): ?>

                        <div
                            class="alert alert-<?=
                                htmlspecialchars($message_type)
                            ?> alert-dismissible fade show"
                            role="alert"
                        >

                            <?= htmlspecialchars($message) ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>

                        </div>

                    <?php endif; ?>


                    <form
                        id="addBatchForm"
                        method="POST"
                        enctype="multipart/form-data"
                    >


                        <div class="batch-page-header">

                            <h4 class="fw-bold">
                                Add Product Batch
                            </h4>


                            <div class="batch-actions">

                                <a
                                    href="manage-batch.php"
                                    class="btn btn-outline-secondary"
                                >

                                    <i class="bx bx-arrow-back me-1"></i>

                                    Cancel

                                </a>


                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >

                                    <i class="bx bx-save me-1"></i>

                                    Save Batch

                                </button>

                            </div>

                        </div>


                        <div class="row">


                            <div class="col-lg-7">


                                <div class="card batch-card">

                                    <div class="card-header">
                                        Product & Supplier
                                    </div>


                                    <div class="card-body">


                                        <div class="mb-3">

                                            <label class="form-label">

                                                Product
                                                <span class="required">*</span>

                                            </label>


                                            <select
                                                name="product_id"
                                                class="form-select"
                                                required
                                            >

                                                <option value="">
                                                    Select product
                                                </option>


                                                <?php while (
                                                    $product =
                                                    $products->fetch_assoc()
                                                ): ?>

                                                    <option
                                                        value="<?=
                                                            (int)$product[
                                                                'product_id'
                                                            ]
                                                        ?>"
                                                        <?=
                                                            $product_id ==
                                                            $product[
                                                                'product_id'
                                                            ]
                                                            ? 'selected'
                                                            : ''
                                                        ?>
                                                    >

                                                        <?=
                                                            htmlspecialchars(
                                                                $product[
                                                                    'product_name'
                                                                ]
                                                            )
                                                        ?>


                                                        <?php if (
                                                            !empty(
                                                                $product['sku']
                                                            )
                                                        ): ?>

                                                            -
                                                            <?=
                                                                htmlspecialchars(
                                                                    $product[
                                                                        'sku'
                                                                    ]
                                                                )
                                                            ?>

                                                        <?php endif; ?>

                                                    </option>

                                                <?php endwhile; ?>

                                            </select>

                                        </div>


                                        <div>

                                            <label class="form-label">

                                                Supplier
                                                <span class="required">*</span>

                                            </label>


                                            <select
                                                name="supplier_id"
                                                class="form-select"
                                                required
                                            >

                                                <option value="">
                                                    Select supplier
                                                </option>


                                                <?php while (
                                                    $supplier =
                                                    $suppliers->fetch_assoc()
                                                ): ?>

                                                    <option
                                                        value="<?=
                                                            (int)$supplier[
                                                                'supplier_id'
                                                            ]
                                                        ?>"
                                                        <?=
                                                            $supplier_id ==
                                                            $supplier[
                                                                'supplier_id'
                                                            ]
                                                            ? 'selected'
                                                            : ''
                                                        ?>
                                                    >

                                                        <?=
                                                            htmlspecialchars(
                                                                $supplier['name']
                                                            )
                                                        ?>

                                                    </option>

                                                <?php endwhile; ?>

                                            </select>

                                        </div>

                                    </div>

                                </div>


                                <div class="card batch-card">

                                    <div class="card-header">
                                        Batch Details
                                    </div>


                                    <div class="card-body">

                                        <div class="row">


                                            <div class="col-md-6 mb-3">

                                                <label class="form-label">

                                                    Batch / Lot Number
                                                    <span class="required">*</span>

                                                </label>


                                                <input
                                                    type="text"
                                                    name="batch_number"
                                                    class="form-control"
                                                    value="<?=
                                                        htmlspecialchars(
                                                            $batch_number
                                                        )
                                                    ?>"
                                                    placeholder="Enter batch number"
                                                    required
                                                >

                                            </div>


                                            <div class="col-md-6 mb-3">

                                                <label class="form-label">

                                                    Quantity Received
                                                    <span class="required">*</span>

                                                </label>


                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    class="form-control"
                                                    value="<?=
                                                        htmlspecialchars(
                                                            $quantity
                                                        )
                                                    ?>"
                                                    min="1"
                                                    placeholder="Enter quantity"
                                                    required
                                                >

                                            </div>


                                            <div class="col-md-6 mb-3">

                                                <label class="form-label">

                                                    Manufacture Date

                                                </label>


                                                <input
                                                    type="date"
                                                    name="manufacture_date"
                                                    class="form-control"
                                                    value="<?=
                                                        htmlspecialchars(
                                                            $manufacture_date
                                                        )
                                                    ?>"
                                                >

                                            </div>


                                            <div class="col-md-6 mb-3">

                                                <label class="form-label">

                                                    Expiry Date
                                                    <span class="required">*</span>

                                                </label>


                                                <input
                                                    type="date"
                                                    name="expiry_date"
                                                    class="form-control"
                                                    value="<?=
                                                        htmlspecialchars(
                                                            $expiry_date
                                                        )
                                                    ?>"
                                                    required
                                                >

                                            </div>


                                            <div class="col-md-6 mb-3">

                                                <label class="form-label">

                                                    Received Date
                                                    <span class="required">*</span>

                                                </label>


                                                <input
                                                    type="date"
                                                    name="received_date"
                                                    class="form-control"
                                                    value="<?=
                                                        htmlspecialchars(
                                                            $received_date
                                                        )
                                                    ?>"
                                                    required
                                                >

                                            </div>


                                            <div class="col-md-6 mb-3">

                                                <label class="form-label">

                                                    Purchase Price

                                                </label>


                                                <div class="price-input">

                                                    <span class="currency">
                                                        Rs.
                                                    </span>


                                                    <input
                                                        type="number"
                                                        name="purchase_price"
                                                        class="form-control"
                                                        value="<?=
                                                            htmlspecialchars(
                                                                $purchase_price
                                                            )
                                                        ?>"
                                                        min="0"
                                                        step="0.01"
                                                        placeholder="0.00"
                                                    >

                                                </div>

                                            </div>


                                            <div class="col-md-6">

                                                <label class="form-label">

                                                    Selling Price

                                                </label>


                                                <div class="price-input">

                                                    <span class="currency">
                                                        Rs.
                                                    </span>


                                                    <input
                                                        type="number"
                                                        name="selling_price"
                                                        class="form-control"
                                                        value="<?=
                                                            htmlspecialchars(
                                                                $selling_price
                                                            )
                                                        ?>"
                                                        min="0"
                                                        step="0.01"
                                                        placeholder="0.00"
                                                    >

                                                </div>

                                            </div>


                                        </div>

                                    </div>

                                </div>

                            </div>


                            <div class="col-lg-5">


                                <div class="card batch-card">

                                    <div class="card-header">

                                        Invoice

                                    </div>


                                    <div class="card-body">


                                        <div class="mb-3">

                                            <label class="form-label">

                                                Invoice Number

                                            </label>


                                            <input
                                                type="text"
                                                name="purchase_reference"
                                                class="form-control"
                                                placeholder="Enter invoice number"
                                            >

                                        </div>


                                        <div class="invoice-box">

                                            <i class="bx bx-receipt"></i>


                                            <div class="fw-semibold mb-2">

                                                Upload Invoice Image

                                            </div>


                                            <input
                                                type="file"
                                                name="invoice_file"
                                                class="form-control"
                                                accept="image/jpeg,image/png,image/webp"
                                            >


                                            <small class="text-muted">

                                                JPG, PNG or WEBP
                                                <br>
                                                Maximum size: 2MB

                                            </small>

                                        </div>

                                    </div>

                                </div>


                                <div class="card batch-card">

                                    <div class="card-header">

                                        Notes

                                    </div>


                                    <div class="card-body">

                                        <textarea
                                            name="notes"
                                            class="form-control"
                                            rows="4"
                                            placeholder="Storage conditions or other remarks"
                                        ></textarea>

                                    </div>

                                </div>


                            </div>


                        </div>


                    </form>

                </div>


                <?php require_once 'includes/footer.php'; ?>


                <div class="content-backdrop fade"></div>

            </div>

        </div>

    </div>


    <div class="layout-overlay layout-menu-toggle"></div>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const manufactureDate =
            document.querySelector(
                'input[name="manufacture_date"]'
            );

        const expiryDate =
            document.querySelector(
                'input[name="expiry_date"]'
            );


        if (
            manufactureDate &&
            expiryDate
        ) {

            manufactureDate.addEventListener(
                'change',
                function () {

                    if (this.value) {

                        expiryDate.min =
                            this.value;

                    }

                }
            );

        }


        const form =
            document.getElementById(
                'addBatchForm'
            );


        if (form) {

            form.addEventListener(
                'submit',
                function () {

                    const button =
                        form.querySelector(
                            'button[type="submit"]'
                        );

                    if (button) {

                        button.disabled = true;

                        button.innerHTML =
                            '<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...';

                    }

                }
            );

        }

    }
);

</script>

</body>

</html>