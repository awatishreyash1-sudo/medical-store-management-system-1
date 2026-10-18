<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Purchase Return Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
body{background:#f4f6f9;font-family:'Segoe UI',Tahoma,sans-serif;font-size:14px;}
.card{max-width:1000px;margin:20px auto;margin-left:250px;border-radius:10px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.08);}
.card-header{background:#0d6efd;color:#fff;padding:15px 25px;border-radius:10px 10px 0 0;}
.page-title{font-size:20px;margin:0;}
table{font-size:13px;}
th{!important;color:#fff;text-align:center;}
td{text-align:center;}
@media print{.btn,nav,form{display:none!important}.card{box-shadow:none;border:none}}
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">
<?php include 'navbar7.php'; ?>

<div class="card">
<div class="card-header">
<h2 class="page-title"><i class="bi bi-arrow-return-left"></i> Purchase Return Report</h2>
</div>

<div class="card-body">
<form method="GET" class="row g-3 mb-4">
<div class="col-md-4">
<label>Start Date</label>
<input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
</div>
<div class="col-md-4">
<label>End Date</label>
<input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
</div>
<div class="col-md-4 align-self-end">
<button class="btn btn-danger">Search</button>
</div>
</form>
</div>
</div>

<?php if($start_date && $end_date):

/* 🔥 FIXED QUERY – DISTINCT */
$query = "
SELECT DISTINCT 
    b.id AS bill_id,
    b.s_name,
    b.city,
    b.mobile,
    r.return_date
FROM purchase_return_medicine r
JOIN ss_bills b ON b.id = r.bill_id
WHERE DATE(r.return_date) BETWEEN ? AND ?
ORDER BY b.s_name, r.return_date
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss",$start_date,$end_date);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while($r = $res->fetch_assoc()){
    $data[$r['s_name']]['city']   = $r['city'];
    $data[$r['s_name']]['mobile'] = $r['mobile'];
    $data[$r['s_name']]['bills'][] = $r;
}

$grand_total = 0;
$pid = 1;
?>

<?php foreach($data as $supplier=>$sup): ?>
<div class="card my-4">


<?php
$supplier_total = 0;
foreach($sup['bills'] as $b):
$print_id = "print_".$pid++;
$bill_id  = $b['bill_id'];
$return_date = $b['return_date'];

$m = $conn->prepare("
    SELECT * 
    FROM purchase_return_medicine 
    WHERE bill_id=? AND return_date=?
");
$m->bind_param("is",$bill_id,$return_date);
$m->execute();
$meds = $m->get_result();
$total = 0;
?>

<div id="<?= $print_id ?>" class="p-3 border-top">
<div class="d-flex justify-content-between mb-2">
<h6>Supplier: <?= $supplier ?> | City: <?= $sup['city'] ?> | Mobile: <?= $sup['mobile'] ?> | Date: <?= date('d-M-Y',strtotime($return_date)) ?></h6>
<button class="btn btn-success btn-sm btn-print"
 onclick="printSection('<?= $print_id ?>')">
<i class="bi bi-printer-fill"></i> Print
</button>
</div>

<table class="table table-bordered table-sm">
<thead>
<tr>
<th>#</th><th>Product</th><th>Batch</th><th>GST%</th>
<th>MRP</th><th>Expiry</th><th>Qty</th>
<th>Return Amt</th><th>GST Amt</th><th>Total</th>
</tr>
</thead>
<tbody>

<?php $i=1; while($md=$meds->fetch_assoc()):
$gst_amt = ($md['return_amount']*$md['gst'])/(100+$md['gst']);
$total += $md['return_amount'];
?>
<tr>
<td><?= $i++ ?></td>
<td><?= $md['product'] ?></td>
<td><?= $md['batch'] ?></td>
<td><?= $md['gst'] ?>%</td>
<td>₹<?= number_format($md['mrp'],2) ?></td>
<td><?= $md['ex_date'] ?></td>
<td><?= $md['return_qty'] ?></td>
<td>₹<?= number_format($md['return_amount'],2) ?></td>
<td>₹<?= number_format($gst_amt,2) ?></td>
<td>₹<?= number_format($md['return_amount'],2) ?></td>
</tr>
<?php endwhile; ?>

</tbody>
<tfoot>
<tr>
<th colspan="9" class="text-end">Total</th>
<th>₹<?= number_format($total,2) ?></th>
</tr>
</tfoot>
</table>
</div>

<?php
$supplier_total += $total;
endforeach;
$grand_total += $supplier_total;
?>

<div class="text-end p-3">
<strong class="text-primary">Supplier Total: ₹<?= number_format($supplier_total,2) ?></strong>
</div>
</div>
<?php endforeach; ?>

<div class="card">
<div class="card-body text-end">
<h5 class="text-danger">Grand Total Return: ₹<?= number_format($grand_total,2) ?></h5>
</div>
</div>

<?php endif; ?>
</div>
</div>

<script>
function printSection(id){
    const section = document.getElementById(id).cloneNode(true);

    const win = window.open('', '', 'width=1000,height=800');
    win.document.write(`
    <html>
    <head>
        <title>Purchase Bill Print</title>
        <style>
            body{font-family:Arial;margin:30px;color:#333;}
            table{width:100%;border-collapse:collapse;margin-top:20px;font-size:14px;}
            th,td{border:1px solid #ddd;padding:12px;text-align:center;}
            th{font-weight:bold;}

            .invoice-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;border-bottom:2px solid #198754;padding-bottom:15px;}
            .invoice-header h1{font-size:28px;color:#198754;margin:0;}
            .store-info{text-align:right;font-size:14px;}

            /* SUPPLIER / PATIENT LINE FONT SIZE INCREASE */
            h6{
                font-size:16px;
                font-weight:600;
                margin-bottom:12px;
            }

            .invoice-footer{margin-top:40px;border-top:2px solid #198754;padding-top:15px;text-align:center;font-size:14px;color:#555;}

            /* PRINT TIME BUTTON HIDE */
            button, .btn {
                display:none !important;
            }
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
    win.focus();
    win.print();
    win.close();
}
</script>


</body>
</html>
