<?php

include __DIR__ . '/../../includes/db.php';

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
JOIN orders o ON p.order_id = o.order_id
JOIN customers c ON o.customer_id = c.customer_id
ORDER BY p.payment_id DESC
";

$result = mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Management</title>
</head>
<body>

<h2>Payment Management</h2>

<table border="1" cellpadding="10">

<tr>
    <th>Payment ID</th>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Method</th>
    <th>Amount</th>
    <th>Status</th>
    <th>Paid Date</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>
    <td><?= $row['payment_id']; ?></td>
    <td><?= $row['order_id']; ?></td>
    <td><?= $row['first_name'].' '.$row['last_name']; ?></td>
    <td><?= $row['payment_method']; ?></td>
    <td><?= number_format($row['amount'],2); ?></td>
    <td><?= $row['payment_status']; ?></td>
    <td><?= $row['paid_at']; ?></td>
</tr>

<?php } ?>

</table>

</body>
</html>