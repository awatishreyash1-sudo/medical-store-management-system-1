<?php
session_start();

if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medical_shop");
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
body {
    background-color:#eef2f7;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.card {
    width:1000px;
    border-radius:12px;
    box-shadow:0 8px 24px rgba(0,0,0,0.1);
    left:250px;
}
.card-header {
    background:#0069d9;
    color:#fff;
}
table thead th {
    background:#0056b3;
    color:#fff;
    position:sticky;
    top:0;
}
@media print {
    .no-print { display:none !important; }
    body { background:#fff; }
}
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">

<?php include 'navbar7.php'; ?>

<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
    <h2><i class="bi bi-bar-chart"></i> Sales Records</h2>
    <button class="btn btn-light no-print" onclick="printRecords()">
        <i class="bi bi-printer-fill"></i> Print
    </button>
</div>

<div class="card-body">

<form method="GET" class="row g-3 align-items-end no-print">
    <div class="col-md-4">
        <label>Start Date</label>
        <input type="date" name="start_date" class="form-control"
               value="<?= $_GET['start_date'] ?? '' ?>" required>
    </div>
    <div class="col-md-4">
        <label>End Date</label>
        <input type="date" name="end_date" class="form-control"
               value="<?= $_GET['end_date'] ?? '' ?>" required>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-success"><i class="bi bi-search"></i> Search</button>
        <a href="sell_medicine.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</form>

<div class="table-responsive mt-3" id="printableArea">
<table class="table table-bordered table-striped align-middle">

<thead>
<tr>
    <th>Salesman</th>
    <th>Patient</th>
    <th>Medicine</th>
    <th>Qty</th>
    <th>Selling Price</th>
    <th>GST (%)</th>
    <th>City</th>
    <th>Mobile</th>
    <th>Date</th>
    <th>Amount</th>
</tr>
</thead>

<tbody>
<?php
$start = $_GET['start_date'] ?? date('Y-m-d');
$end   = $_GET['end_date'] ?? date('Y-m-d');
$total = 0;

$bq = $conn->prepare("SELECT * FROM sales_bills WHERE DATE(datetime) BETWEEN ? AND ? ORDER BY id DESC");
$bq->bind_param("ss", $start, $end);
$bq->execute();
$bills = $bq->get_result();

while ($bill = $bills->fetch_assoc()) {

    $total += $bill['f_total_amount'];

    $mq = $conn->prepare("SELECT product, qty1, mrp, gst FROM sales_medicin WHERE bill_id=?");
    $mq->bind_param("i", $bill['id']);
    $mq->execute();
    $meds = $mq->get_result();

    $first = true;

    while ($med = $meds->fetch_assoc()) {

        $pid = $med['product'];
        $mname = '';

        $st = $conn->prepare("SELECT medicine_name FROM medicine_master WHERE id=?");
        $st->bind_param("i", $pid);
        $st->execute();
        $st->bind_result($mname);
        $st->fetch();
        $st->close();

        if (!$mname) {
            $st2 = $conn->prepare("SELECT product FROM ss_medicine WHERE id=?");
            $st2->bind_param("i", $pid);
            $st2->execute();
            $st2->bind_result($mname);
            $st2->fetch();
            $st2->close();
        }

        echo "<tr>";

        if ($first) {
            echo "<td>{$bill['salesman_name']}</td>";
            echo "<td>{$bill['patient_name']}</td>";
        } else {
            echo "<td></td><td></td>";
        }

        echo "<td>{$mname}</td>";
        echo "<td>{$med['qty1']}</td>";
        echo "<td>₹".number_format($med['mrp'],2)."</td>";
        echo "<td>{$med['gst']}%</td>";
        echo "<td>{$bill['city']}</td>";
        echo "<td>{$bill['mobile']}</td>";
        echo "<td>".date('d-m-Y', strtotime($bill['datetime']))."</td>";

        if ($first) {
            echo "<td>₹".number_format($bill['f_total_amount'],2)."</td>";
            $first = false;
        } else {
            echo "<td></td>";
        }

        echo "</tr>";
    }
}
?>
</tbody>

<tfoot>
<tr>
    <th colspan="9" class="text-end">Total Sales :</th>
    <th>₹ <?= number_format($total,2) ?></th>
</tr>
</tfoot>

</table>
</div>

</div>
</div>

</div>
</div>

<script>
function printRecords() {
    const content = document.getElementById('printableArea').innerHTML;
    const pw = window.open('', '', 'width=1200,height=900');
    pw.document.write('<html><head><title>Print</title>');
    pw.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    pw.document.write('</head><body>');
    pw.document.write('<h2 class="text-center mb-4">Sales Report</h2>');
    pw.document.write(content);
    pw.document.write('</body></html>');
    pw.document.close();
    pw.print();
}
</script>

</body>
</html>
