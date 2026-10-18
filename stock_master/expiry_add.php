<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get medicine details by ID
$id = $_GET['id'] ?? 0;
$medicine = [];

if ($id) {
    // Join ss_medicine with ss_bills
    $sql = "
        SELECT m.*, b.s_name, b.pay_mode, 
               b.salesman_name AS sales_pass, 
               b.address AS company
        FROM ss_medicine m
        JOIN ss_bills b ON m.bill_id = b.id
        WHERE m.id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $medicine = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Expired Medicine</title>

    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="../nav.css">

    <style>
        .card{
            left:220px;
        }
    </style>
</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include 'navbar4.php'; ?>

<div class="col-md-10 d-flex justify-content-center align-items-center mt-4 mb-4" style="min-height: 85vh;">

<div class="card shadow-lg rounded-4" style="width: 500px;">

<div class="card-header bg-info text-white text-center rounded-top-4 py-3">
<h3 class="mb-0 fw-bold">
<i class="bi bi-pencil-square"></i> Add Expired Medicine
</h3>
</div>

<div class="card-body bg-white">

<?php if (!empty($medicine)): ?>

<form method="POST">

<input type="hidden" name="id"
value="<?php echo $medicine['id']; ?>">

<div class="mb-3">
<label class="fw-semibold">Product</label>

<input type="text"
name="product"
class="form-control form-control-sm"
value="<?php echo $medicine['product']; ?>"
readonly>
</div>

<div class="mb-3">
<label class="fw-semibold">Supplier Name</label>

<input type="text"
name="s_name"
class="form-control form-control-sm"
value="<?php echo $medicine['s_name']; ?>"
readonly>
</div>

<div class="mb-3">
<label class="fw-semibold">Address</label>

<input type="text"
name="company"
class="form-control form-control-sm"
value="<?php echo $medicine['company']; ?>"
readonly>
</div>

<div class="mb-3">
<label class="fw-semibold">Sales Name</label>

<input type="text"
name="sales_pass"
class="form-control form-control-sm"
value="<?php echo $medicine['sales_pass']; ?>"
readonly>
</div>

<div class="row g-2">

<div class="col-md-6">
<label class="fw-semibold">MRP</label>

<input type="text"
name="mrp"
class="form-control form-control-sm"
value="<?php echo $medicine['mrp']; ?>"
readonly>
</div>

<div class="col-md-6">
<label class="fw-semibold">Amount</label>

<input type="text"
name="amount"
class="form-control form-control-sm"
value="<?php echo $medicine['amount']; ?>"
readonly>
</div>

<div class="col-md-6">
<label class="fw-semibold">Pay Mode</label>

<input type="text"
name="pay_mode"
class="form-control form-control-sm"
value="<?php echo $medicine['pay_mode']; ?>"
readonly>
</div>

<div class="col-md-6">
<label class="fw-semibold">Batch No</label>

<input type="text"
name="batch"
class="form-control form-control-sm"
value="<?php echo $medicine['batch']; ?>"
readonly>
</div>

<div class="col-md-6">
<label class="fw-semibold">Expiry Date</label>

<input type="date"
name="ex_date"
class="form-control form-control-sm"
value="<?php echo $medicine['ex_date']; ?>"
readonly>
</div>

<div class="col-md-6">
<label class="fw-semibold">GST</label>

<input type="text"
name="gst"
class="form-control form-control-sm"
value="<?php echo $medicine['gst']; ?>"
readonly>
</div>

<div class="col-md-6">
<label class="fw-semibold">Quantity</label>

<input type="number"
name="new_qty"
class="form-control form-control-sm"
value="<?php echo $medicine['new_qty']; ?>"
max="<?php echo $medicine['new_qty']; ?>"
required>
</div>

</div>

<div class="d-grid gap-2 mt-3">

<button type="submit"
class="btn btn-success">

<i class="bi bi-plus-circle"></i>
Add Expiry Medicine

</button>

<a href="expiry_medi.php"
class="btn btn-secondary">

<i class="bi bi-arrow-left-circle"></i>
Back

</a>

</div>

</form>

<?php else: ?>

<div class="alert alert-danger">
No medicine found!
</div>

<?php endif; ?>

</div>
</div>
</div>
</div>
</div>

</body>
</html>

<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$product_id = $_POST['id'];
$product_name = $_POST['product'];
$mrp = $_POST['mrp'];
$amount = $_POST['amount'];
$s_name = $_POST['s_name'];
$pay_mode = $_POST['pay_mode'];
$sales_pass = $_POST['sales_pass'];
$batch = $_POST['batch'];
$ex_date = $_POST['ex_date'];
$company = $_POST['company'];
$gst = $_POST['gst'];
$qty = $_POST['new_qty'];

$datetime = date('Y-m-d H:i:s');

$insertQuery = "
INSERT INTO expiry_medicin
(
product,
mrp,
amount,
s_name,
pay_mode,
sales_pass,
batch,
ex_date,
datetime,
company,
gst,
new_qty
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($insertQuery);

$stmt->bind_param(
"sdssssssssdi",
$product_name,
$mrp,
$amount,
$s_name,
$pay_mode,
$sales_pass,
$batch,
$ex_date,
$datetime,
$company,
$gst,
$qty
);

$stmt->execute();
$stmt->close();

/* Reduce stock in medicine_master */

$stock_stmt = $conn->prepare(
"UPDATE medicine_master
SET stock = stock - ?
WHERE medicine_name = ?"
);

$stock_stmt->bind_param(
"is",
$qty,
$product_name
);

$stock_stmt->execute();
$stock_stmt->close();

/* Reduce stock in ss_medicine */

$stmt = $conn->prepare(
"UPDATE ss_medicine
SET new_qty = new_qty - ?
WHERE id = ?"
);

$stmt->bind_param(
"ii",
$qty,
$product_id
);

$stmt->execute();
$stmt->close();

echo "<script>

alert('Expiry medicine added and stock updated successfully!');

window.location.href =
'expiry_medi.php';

</script>";

exit;

}
?>