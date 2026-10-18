<?php
session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost","root","","medical_shop");

$supplier = $_GET['supplier'] ?? '';
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
<title>Expiry Medicine Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
body{background:#f5f7fa;font-family:'Segoe UI',sans-serif;}
.card{
    width:1000px;
    margin-top:30px;
    border:none;
    border-radius:15px;
    box-shadow:0 8px 30px rgba(0,0,0,0.08);
    left:130px;
}
.card-header{
    background:linear-gradient(135deg,#1d3557,#457b9d);
    color:#fff;
    text-align:center;
}
.bill-header{
    margin-bottom:10px;
    padding:10px;
    border:1px solid #ddd;
    border-radius:8px;
    background:#f8f9fa;
}
table th{
    background:#1d3557;
    color:#fff;
}
.total-box{
    font-size:18px;
    font-weight:bold;
    text-align:right;
    padding:10px;
    background:#e9f5ff;
    border-radius:8px;
}
@media print{
    .no-print,.navbar,.btn{display:none!important;}
}
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">
<?php include 'navbar7.php'; ?>

<div class="col-lg-10 offset-lg-1">
<div class="card" id="printArea">

<div class="card-header">
<h4>Expiry Return Medicine Report</h4>
<div class="no-print mt-2">
<button onclick="printReport()" class="btn btn-light btn-sm">
Print
</button>
</div>
</div>

<div class="card-body">

<?php
// ========================
// SUMMARY VIEW
// ========================
if($supplier == ""):
?>

<form class="row g-3 justify-content-center no-print">
    <div class="col-md-4">
        <label>From Date</label>
        <input type="date" name="from" value="<?= $from ?>" class="form-control">
    </div>
    <div class="col-md-4">
        <label>To Date</label>
        <input type="date" name="to" value="<?= $to ?>" class="form-control">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Search</button>
    </div>
</form>

<hr>

<?php
$sql = "SELECT s_name,
        DATE(datetime) as return_date,
        product,
        batch,
        SUM(new_qty) as total_qty,
        SUM(amount) as total_amount
        FROM expiry_medicin WHERE 1 ";

if($from && $to){
    $sql .= " AND DATE(datetime) BETWEEN '$from' AND '$to'";
}

$sql .= " GROUP BY s_name, DATE(datetime), product, batch
          ORDER BY return_date DESC";

$result = $conn->query($sql);

/* 🔥 FINAL TOTAL CALCULATION */
$total_sql = "SELECT SUM(amount) as final_total FROM expiry_medicin WHERE 1 ";
if($from && $to){
    $total_sql .= " AND DATE(datetime) BETWEEN '$from' AND '$to'";
}
$total_res = $conn->query($total_sql);
$total_row = $total_res->fetch_assoc();
$final_total = $total_row['final_total'] ?? 0;
?>

<table class="table table-bordered table-hover text-center">
<thead>
<tr>
    <th>#</th>
    <th>Supplier</th>
    <th>Date</th>
    <th>Medicine</th>
    <th>Batch</th>
    <th>Qty</th>
    <th>Total</th>
    <th class="no-print">Action</th>
</tr>
</thead>

<tbody>
<?php
$i=1;
while($r=$result->fetch_assoc()):
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= $r['s_name'] ?></td>
    <td><?= date('d-M-Y',strtotime($r['return_date'])) ?></td>
    <td><?= $r['product'] ?></td>
    <td><?= $r['batch'] ?></td>
    <td><?= $r['total_qty'] ?></td>
    <td><strong>₹<?= number_format($r['total_amount'],2) ?></strong></td>
    <td class="no-print">
        <a href="?supplier=<?= urlencode($r['s_name']) ?>&from=<?= $r['return_date'] ?>&to=<?= $r['return_date'] ?>" 
           class="btn btn-success btn-sm">
           View Bills
        </a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<!-- 🔥 FINAL TOTAL DISPLAY -->
<div class="total-box mt-3">
Final Total (<?= $from ?> to <?= $to ?>) :
₹<?= number_format($final_total,2) ?>
</div>

<?php
// ========================
// DETAIL VIEW
// ========================
else:

$sql2 = "SELECT * FROM expiry_medicin 
         WHERE s_name='$supplier' ";

if($from && $to){
    $sql2 .= " AND DATE(datetime) BETWEEN '$from' AND '$to'";
}

$sql2 .= " ORDER BY ex_date ASC";

$res2 = $conn->query($sql2);
$total=0;
?>

<div class="bill-header d-flex justify-content-between align-items-center">
<div>
<strong>Supplier:</strong> <?= $supplier ?><br>
<strong>Date:</strong> <?= date('d-M-Y',strtotime($from)) ?>
</div>

<div class="no-print">
<a href="expiry_bill.php" class="btn btn-secondary btn-sm">Back</a>
</div>
</div>

<table class="table table-bordered text-center align-middle">
<thead>
<tr>
    <th>#</th>
    <th>Medicine</th>
    <th>Batch</th>
    <th>Expiry</th>
    <th>MRP</th>
    <th>Qty</th>
    <th>GST%</th>
    <th>Amount</th>
</tr>
</thead>

<tbody>
<?php
$i=1;
while($row=$res2->fetch_assoc()):
$total += $row['amount'];
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= $row['product'] ?></td>
    <td><?= $row['batch'] ?></td>
    <td><?= date('d-M-Y',strtotime($row['ex_date'])) ?></td>
    <td><?= number_format($row['mrp'],2) ?></td>
    <td><?= $row['new_qty'] ?></td>
    <td><?= $row['gst'] ?>%</td>
    <td><?= number_format($row['amount'],2) ?></td>
</tr>
<?php endwhile; ?>
</tbody>

<tfoot>
<tr>
    <td colspan="7" class="text-end"><strong>Grand Total</strong></td>
    <td><strong>₹<?= number_format($total,2) ?></strong></td>
</tr>
</tfoot>
</table>

<?php endif; ?>

</div>
</div>
</div>
</div>
</div>

<script>
function printReport(){
const section=document.getElementById("printArea").cloneNode(true);
const win=window.open('','','width=1200,height=900');

win.document.write(`
<html>
<head>
<title>Expiry Medicine Print</title>
<style>
@page{size:A4;margin:12mm;}
body{font-family:Arial;color:#333;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th,td{border:1px solid #000;padding:8px;text-align:center;}
.invoice-header{display:flex;justify-content:space-between;align-items:center;
border-bottom:2px solid #198754;padding-bottom:15px;margin-bottom:20px;}
.invoice-header h1{font-size:26px;color:#198754;margin:0;}
.store-info{text-align:right;font-size:14px;}
.invoice-footer{margin-top:30px;border-top:2px solid #198754;
padding-top:10px;text-align:center;font-size:13px;}
button,.btn,.no-print{display:none!important;}
</style>
</head>
<body>

<div class="invoice-header">
<h1>Medical Store</h1>
<div class="store-info">
<p>Shop No.101, Main Bazar, Dudhagon-416315</p>
<p>GSTIN:27ABCDE1234F1Z5</p>
<p>Phone:9876543210</p>
</div>
</div>

${section.innerHTML}

<div class="invoice-footer">
<p>Thank you for your business!</p>
<p>Printed on: ${new Date().toLocaleString()}</p>
</div>

</body>
</html>
`);
win.document.close();
win.print();
win.close();
}
</script>

</body>
</html>

<?php $conn->close(); ?>
