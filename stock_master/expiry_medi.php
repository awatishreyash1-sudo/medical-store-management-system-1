<?php
session_start(); 

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Today's date
$today = date('Y-m-d');

// 5 days later
$five_days_later = date('Y-m-d', strtotime('+5 days'));

$medicines = [];

// Show medicines expiring in next 5 days AND stock > 0
$sql = "
    SELECT sm.*, sb.salesman_name, sb.s_name
    FROM ss_medicine sm
    LEFT JOIN ss_bills sb ON sm.bill_id = sb.id
    WHERE DATE(sm.ex_date) BETWEEN ? AND ?
    AND sm.new_qty > 0
    ORDER BY sm.ex_date ASC
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("ss", $today, $five_days_later);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $medicines[] = $row;
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Expiry Alert (5 Days Before)</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
.custom-card {
    width: 80%;
    max-width: 1100px;
    margin: auto;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    left: 100px;
}

.card-header {
    background: linear-gradient(135deg, #ff9800, #dc3545);
    color: white;
    padding: 20px;
    border-radius: 20px 20px 0 0;
}

.table th {
    background-color: #343a40;
    color: white;
}

.badge-warning {
    background-color: orange;
}

.badge-danger {
    background-color: red;
}
</style>
</head>

<body>

<div class="container-fluid">
<div class="row">
<?php include 'navbar4.php'; ?>

<div class="container my-4 d-flex justify-content-center">
<div class="card custom-card">

<div class="card-header text-center">
    <h2><i class="bi bi-exclamation-triangle-fill"></i> Expiry Alert (Next 5 Days)</h2>
    <small>Showing medicines expiring between <?= $today ?> and <?= $five_days_later ?></small>
</div>

<div class="card-body">

<?php if (!empty($medicines)): ?>

<div class="table-responsive">
<table class="table table-striped table-hover">
<thead>
<tr>
    <th>Product</th>
    <th>MRP</th>
    <th>Amount</th>
    <th>Supplier</th>
    <th>Salesman</th>
    <th>Batch</th>
    <th>Expiry Date</th>
    <th>Stock</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php foreach ($medicines as $medicine): 

    $expiry = $medicine['ex_date'];
    $days_left = floor((strtotime($expiry) - strtotime($today)) / (60*60*24));
?>

<tr>
    <td><?= htmlspecialchars($medicine['product']); ?></td>
    <td><?= htmlspecialchars($medicine['mrp']); ?></td>
    <td><?= htmlspecialchars($medicine['amount']); ?></td>
    <td><?= htmlspecialchars($medicine['s_name'] ?? ''); ?></td>
    <td><?= htmlspecialchars($medicine['salesman_name'] ?? ''); ?></td>
    <td><?= htmlspecialchars($medicine['batch']); ?></td>
    <td>
        <?php if ($days_left < 0): ?>
            <span class="badge badge-danger">Expired (<?= $expiry ?>)</span>
        <?php else: ?>
            <span class="badge badge-warning">
                <?= $expiry ?> (<?= $days_left ?> days left)
            </span>
        <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($medicine['new_qty']); ?></td>
    <td>
        <a href="expiry_add.php?id=<?= urlencode($medicine['id']); ?>" 
           class="btn btn-sm btn-success">
           <i class="bi bi-plus-circle"></i> Add
        </a>
    </td>
</tr>

<?php endforeach; ?>
</tbody>
</table>
</div>

<?php else: ?>

<div class="alert alert-success text-center">
    No available stock expiring in next 5 days 🎉
</div>

<?php endif; ?>

</div>
</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>