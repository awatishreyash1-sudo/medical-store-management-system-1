<?php
$conn = mysqli_connect("localhost","root","","medical_shop");
if(!$conn){ die("Database Error"); }

/* ================= TOTAL STOCK ================= */
$q1 = mysqli_query($conn,"SELECT SUM(stock) AS total_stock FROM medicine_master");
$totalStock = mysqli_fetch_assoc($q1)['total_stock'] ?? 0;

/* ================= OUT OF STOCK ================= */
$outQ = mysqli_query($conn,"SELECT COUNT(*) AS out_stock FROM medicine_master WHERE stock = 0");
$outStock = mysqli_fetch_assoc($outQ)['out_stock'] ?? 0;

/* ================= ALL MEDICINES ================= */
$data = mysqli_query($conn,"
    SELECT medicine_name, company_name, stock 
    FROM medicine_master 
    ORDER BY medicine_name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Stock Status</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{background:#eef2f7;font-family:Segoe UI;}
.card{border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,.1);}
th{background:#0d6efd;color:white;}
.badge{font-size:14px;}
</style>
</head>

<body>
<div class="container-fluid p-4">

<!-- DASHBOARD -->
<div class="row g-4 mb-4">

<div class="col-md-6">
<div class="card text-center p-4">
<i class="bi bi-box-seam fs-1 text-primary"></i>
<h6>Total Remaining Stock</h6>
<h2 class="fw-bold"><?= $totalStock ?></h2>
</div>
</div>

<div class="col-md-6">
<div class="card text-center p-4">
<i class="bi bi-x-circle-fill fs-1 text-danger"></i>
<h6>Out of Stock Medicines</h6>
<h2 class="fw-bold"><?= $outStock ?></h2>
</div>
</div>

</div>

<!-- STOCK TABLE -->
<div class="card p-4">
<h4><i class="bi bi-capsule"></i> Medicine Stock List</h4>

<table class="table table-bordered table-hover mt-3">
<thead>
<tr>
<th>Medicine Name</th>
<th>Company</th>
<th>Remaining Stock</th>
<th>Status</th>
</tr>
</thead>

<tbody>
<?php while($r=mysqli_fetch_assoc($data)): ?>
<tr>
<td><?= $r['medicine_name'] ?></td>
<td><?= $r['company_name'] ?></td>
<td class="fw-bold"><?= $r['stock'] ?></td>
<td>
<?php if($r['stock'] > 0): ?>
<span class="badge bg-success">Available</span>
<?php else: ?>
<span class="badge bg-danger">Out of Stock</span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</div>
</body>
</html>
