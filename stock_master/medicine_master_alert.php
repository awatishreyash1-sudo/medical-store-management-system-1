<?php
session_start();
if(!isset($_SESSION['name'])){
    header("Location:login.php");
    exit();
}

$conn=new mysqli("localhost","root","","medical_shop");
if($conn->connect_error) die("DB Error");

/* LOW STOCK */
$low_stock=$conn->query("
    SELECT * FROM medicine_master
    WHERE stock BETWEEN 0 AND 5
    ORDER BY stock ASC
");

/* EXPIRY 5 DAYS BEFORE */
$expiry=$conn->query("
    SELECT s.* 
    FROM ss_medicine s
    WHERE CURDATE() BETWEEN DATE_SUB(s.ex_date, INTERVAL 5 DAY) AND s.ex_date
    AND s.new_qty>0
    ORDER BY s.ex_date ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Stock & Expiry Alerts</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
body{
    background: linear-gradient(to right,#eef2f3,#dfe9f3);
    font-family: 'Segoe UI', sans-serif;
}

/* Card Design */
.alert-card{
    width:85%;
    max-width:950px;
    margin:30px auto;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
    overflow:hidden;
    background:#fff;
    margin-left:300px;
}

/* Header Styles */
.low-header{
    background:linear-gradient(135deg,#dc3545,#b02a37);
    color:#fff;
    padding:18px;
    font-size:20px;
}

.exp-header{
    background:linear-gradient(135deg,#ff9800,#ff5722);
    color:#fff;
    padding:18px;
    font-size:20px;
}

/* Table */
.table th{
    background:#1e293b;
    color:#fff;
    font-size:14px;
}
.table td{
    font-size:14px;
}

.table-hover tbody tr:hover{
    background:#f1f5f9;
    transition:0.3s;
}

/* Stock Highlight */
.low-stock{
    color:#dc3545;
    font-weight:bold;
    font-size:16px;
}

/* Expiry Badge */
.badge-days{
    background:#dc2626;
    color:#fff;
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
}

/* Amount Styling */
.amount{
    font-weight:600;
    color:#0d6efd;
}
</style>
</head>
<body>

<div class="container-fluid">
<div class="row">
<?php include 'navbar4.php'; ?>

<h3 class="text-center mt-4 fw-bold">
<i class="bi bi-exclamation-triangle-fill text-danger"></i>
Medicine Stock & Expiry Alerts
</h3>

<!-- ================= LOW STOCK ================= -->
<div class="alert-card">
<div class="low-header">
<i class="bi bi-box-seam"></i> Low Stock Alert (0 – 5 Qty)
</div>

<div class="p-3">
<div class="table-responsive">
<table class="table table-hover table-bordered align-middle text-center">
<tr>
<th>Medicine</th>
<th>Company</th>
<th>Stock</th>
</tr>

<?php
if($low_stock->num_rows>0){
while($row=$low_stock->fetch_assoc()){
echo "<tr>
<td>{$row['medicine_name']}</td>
<td>{$row['company_name']}</td>
<td class='low-stock'>{$row['stock']}</td>
</tr>";
}}
else{
echo "<tr><td colspan='3' class='text-center text-success fw-bold'>No Low Stock 🎉</td></tr>";
}
?>
</table>
</div>
</div>
</div>


<!-- ================= EXPIRY ================= -->
<div class="alert-card">
<div class="exp-header">
<i class="bi bi-calendar-x"></i> Expiry Alert (5 Days Before Expiry)
</div>

<div class="p-3">
<div class="table-responsive">
<table class="table table-hover table-bordered align-middle text-center">
<tr>
<th>Medicine</th>
<th>Batch</th>
<th>Expiry</th>
<th>Qty</th>
<th>MRP</th>
<th>Basic</th>
<th>GST%</th>
<th>GST Amt</th>
<th>Total</th>
<th>Days Left</th>
</tr>

<?php
if($expiry->num_rows>0){
while($row=$expiry->fetch_assoc()){

$days=ceil((strtotime($row['ex_date'])-time())/86400);

$basic = $row['new_qty'] * $row['mrp'];
$gst_amt = ($basic * $row['gst'])/100;
$total = $basic + $gst_amt;

echo "<tr>
<td>{$row['product']}</td>
<td>{$row['batch']}</td>
<td class='fw-bold text-danger'>{$row['ex_date']}</td>
<td>{$row['new_qty']}</td>
<td>{$row['mrp']}</td>
<td class='amount'>".number_format($basic,2)."</td>
<td>{$row['gst']}%</td>
<td>".number_format($gst_amt,2)."</td>
<td class='amount'>".number_format($total,2)."</td>
<td><span class='badge-days'>{$days} Days</span></td>
</tr>";
}
}
else{
echo "<tr><td colspan='10' class='text-center text-success fw-bold'>No Expiry Medicine 🎉</td></tr>";
}
?>
</table>
</div>
</div>
</div>

</div>
</div>

</body>
</html>