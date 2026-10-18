<?php
$con = new mysqli("localhost", "root", "", "medical_shop");
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$view_bill = $_GET['bill_no'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
<title>Sales Return Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
body{background:#f5f7fa;font-family:'Segoe UI',sans-serif;}
.card{width:1000px;margin-top:30px;border:none;border-radius:15px;
      box-shadow:0 8px 30px rgba(0,0,0,0.08);left:130px;}
.card-header{background:linear-gradient(135deg,#1d3557,#457b9d);color:#fff;text-align:center;}
.bill-header{margin-bottom:10px;padding:10px;border:1px solid #ddd;
             border-radius:8px;background:#f8f9fa;}
table th{background:#1d3557;color:#fff;}
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
<h4>Sales Return Report</h4>
</div>

<div class="card-body">

<form method="GET" class="row g-3 justify-content-center no-print">
    <div class="col-md-4">
        <label>From Date</label>
        <input type="date" name="from_date" value="<?= $from_date ?>" required class="form-control">
    </div>
    <div class="col-md-4">
        <label>To Date</label>
        <input type="date" name="to_date" value="<?= $to_date ?>" required class="form-control">
    </div>
    <div class="col-md-2 d-flex gap-2 align-items-end">
        <button class="btn btn-primary w-100">Search</button>
        <button onclick="printReport()" type="button"
            class="btn btn-success d-flex align-items-center justify-content-center"
            style="width:140px;">
            <i class="bi bi-printer me-2"></i> Print
        </button>
    </div>
</form>

<hr>

<?php
// ================= SUMMARY =================
if($view_bill == ""):

if($from_date && $to_date){

$stmt = $con->prepare("
SELECT sb.id AS bill_no,
       sb.patient_name,
       sb.city,
       sb.mobile,
       GROUP_CONCAT(DISTINCT sm.product SEPARATOR ', ') AS medicine_name,
       DATE(sr.return_date) AS bill_date,
       SUM(sr.return_qty * smd.mrp) AS total_amount
FROM sales_returns sr
JOIN ss_medicine sm ON sr.medicine_id = sm.id
JOIN sales_medicin smd ON sr.sales_medicin_id = smd.id
JOIN sales_bills sb ON smd.bill_id = sb.id
WHERE DATE(sr.return_date) BETWEEN ? AND ?
GROUP BY sb.id
ORDER BY sb.id DESC
");
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$result = $stmt->get_result();

$finalTotal = 0;
?>

<h5 class="mb-3">Patient Return Summary</h5>

<table class="table table-bordered table-hover text-center">
<thead>
<tr>
<th>#</th>
<th>Patient</th>
<th>City</th>
<th>Mobile</th>
<th>Medicine</th>
<th>Date</th>
<th>Total</th>
<th class="no-print">Action</th>
</tr>
</thead>
<tbody>

<?php $i=1; while($row=$result->fetch_assoc()):
$finalTotal += $row['total_amount'];
?>
<tr>
<td><?= $i++ ?></td>
<td><?= $row['patient_name'] ?></td>
<td><?= $row['city'] ?></td>
<td><?= $row['mobile'] ?></td>
<td><?= $row['medicine_name'] ?></td>
<td><?= date("d-M-Y",strtotime($row['bill_date'])) ?></td>
<td><strong>₹<?= number_format($row['total_amount'],2) ?></strong></td>
<td class="no-print">
<a href="?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&bill_no=<?= $row['bill_no'] ?>"
   class="btn btn-success btn-sm">
View Bills
</a>
</td>
</tr>
<?php endwhile; ?>

</tbody>

<tfoot>
<tr class="table-warning">
<td colspan="6" class="text-end"><strong>Final Return Amount</strong></td>
<td colspan="2"><strong>₹<?= number_format($finalTotal,2) ?></strong></td>
</tr>
<tr>
<td colspan="8" class="text-end">
<strong style="color:red;">
Total Loss (Sales Return): ₹<?= number_format($finalTotal,2) ?>
</strong>
</td>
</tr>
</tfoot>
</table>

<?php
$stmt->close();
}
?>

<?php
// ================= DETAIL VIEW =================
else:

$stmt2 = $con->prepare("
SELECT sr.*, sm.product AS medicine_name, smd.batch,
       smd.ex_date, smd.mrp, smd.gst,
       sb.patient_name, sb.city, sb.mobile,
       DATE(sr.return_date) AS bill_date
FROM sales_returns sr
JOIN ss_medicine sm ON sr.medicine_id = sm.id
JOIN sales_medicin smd ON sr.sales_medicin_id = smd.id
JOIN sales_bills sb ON smd.bill_id = sb.id
WHERE sb.id = ?
");
$stmt2->bind_param("i",$view_bill);
$stmt2->execute();
$bill = $stmt2->get_result();

$firstRow = $bill->fetch_assoc();
$total=0;
?>

<div class="bill-header d-flex justify-content-between align-items-center">
<div>
<strong>Patient:</strong> <?= $firstRow['patient_name'] ?><br>
<strong>City:</strong> <?= $firstRow['city'] ?> |
<strong>Mobile:</strong> <?= $firstRow['mobile'] ?><br>
<strong>Date:</strong> <?= date("d-M-Y",strtotime($firstRow['bill_date'])) ?>
</div>

<div class="no-print">
<a href="sales_return_report.php?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>"
class="btn btn-secondary btn-sm">Back</a>
</div>
</div>

<?php $bill->data_seek(0); ?>

<table class="table table-bordered text-center align-middle">
<thead>
<tr>
<th>#</th><th>Medicine</th><th>Batch</th><th>Qty</th>
<th>MRP</th><th>GST%</th><th>Amount</th><th>Expiry</th>
</tr>
</thead>
<tbody>

<?php $i=1; while($row=$bill->fetch_assoc()):
$amount = $row['return_qty'] * $row['mrp'];
$total += $amount;
?>
<tr>
<td><?= $i++ ?></td>
<td><?= $row['medicine_name'] ?></td>
<td><?= $row['batch'] ?></td>
<td><?= $row['return_qty'] ?></td>
<td><?= number_format($row['mrp'],2) ?></td>
<td><?= $row['gst'] ?>%</td>
<td><?= number_format($amount,2) ?></td>
<td><?= $row['ex_date'] ?></td>
</tr>
<?php endwhile; ?>

</tbody>
<tfoot>
<tr>
<td colspan="6" class="text-end"><strong>Total</strong></td>
<td colspan="2"><strong>₹<?= number_format($total,2) ?></strong></td>
</tr>
</tfoot>
</table>

<?php
$stmt2->close();
endif;
?>

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
<title>Sales Return Print</title>
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
