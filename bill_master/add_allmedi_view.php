<?php
$conn = mysqli_connect("localhost", "root", "", "medical_shop");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$supplier_name = $_GET['supplier_name'] ?? '';

$sql = "SELECT 
            b.id AS bill_id,
            b.datetime,
            b.s_name,
            b.address,
            b.mobile,
            m.product,
            m.batch,
            m.qty1,
            m.mrp,
            m.gst,
            m.ex_date
        FROM ss_bills b
        JOIN ss_medicine m ON b.id = m.bill_id
        WHERE 1=1";

if ($start_date && $end_date) {
    $sql .= " AND DATE(b.datetime) BETWEEN '$start_date' AND '$end_date'";
}

if ($supplier_name) {
    $sql .= " AND b.s_name='" . mysqli_real_escape_string($conn, $supplier_name) . "'";
}

$sql .= " ORDER BY b.s_name, b.datetime, b.id";
$result = mysqli_query($conn, $sql);

/* ===== GROUP BY SUPPLIER + DATE ===== */
$grouped_data = [];

while ($row = mysqli_fetch_assoc($result)) {

    /* 🔥 EXPIRY RETURN AMOUNT FETCH */
    $expiry_query = "SELECT SUM(amount) as total_return 
                     FROM expiry_medicin 
                     WHERE product='".mysqli_real_escape_string($conn,$row['product'])."' 
                     AND batch='".mysqli_real_escape_string($conn,$row['batch'])."' 
                     AND s_name='".mysqli_real_escape_string($conn,$row['s_name'])."' 
                     AND status='used'";
                     
    $expiry_result = mysqli_query($conn, $expiry_query);
    $expiry_data = mysqli_fetch_assoc($expiry_result);
    $row['return_amount'] = $expiry_data['total_return'] ?? 0;

    $dateKey = date('Y-m-d', strtotime($row['datetime']));
    $groupKey = $row['s_name'] . '_' . $dateKey;
    $bill_id = $row['bill_id'];

    if (!isset($grouped_data[$groupKey])) {
        $grouped_data[$groupKey] = [
            'supplier' => [
                'name'   => $row['s_name'],
                'city'   => $row['address'],
                'mobile' => $row['mobile'],
                'date'   => $dateKey
            ],
            'bills' => []
        ];
    }

    if (!isset($grouped_data[$groupKey]['bills'][$bill_id])) {
        $grouped_data[$groupKey]['bills'][$bill_id] = [
            'datetime' => $row['datetime'],
            'medicines' => []
        ];
    }

    $grouped_data[$groupKey]['bills'][$bill_id]['medicines'][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Purchase Bill Report</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
body{background:#f4f6f9;font-size:14px;}
.card{max-width:1000px;margin:20px auto;margin-left:250px;}
.report-card{background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;margin-bottom:25px;}
.supplier-header{font-weight:600;color:#198754;border-bottom:1px dashed #aaa;padding-bottom:6px;margin-bottom:12px;}
th{background:#198754;color:white;text-align:center;}
td{text-align:center;}
@media print{
    .btn,nav,form{display:none !important;}
}
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">
<?php include 'navbar7.php'; ?>

<div class="card p-3">
<h4><i class="bi bi-bag-check"></i> Purchase Bill Report</h4>

<form class="row g-2 mb-4">
    <div class="col-md-3">
        <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
    </div>
    <div class="col-md-3">
        <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
    </div>
    <div class="col-md-4">
        <input type="text" name="supplier_name" class="form-control" placeholder="Supplier Name" value="<?= $supplier_name ?>">
    </div>
    <div class="col-md-2">
        <button class="btn btn-success w-100">Search</button>
    </div>
</form>

<?php
$sn = 1;
$grand_total = 0;

foreach ($grouped_data as $index => $group):
    $supplier = $group['supplier'];
    $bills = $group['bills'];
    $supplier_total = 0;
    $pid = "print_" . md5($index);
?>
<div class="report-card" id="<?= $pid ?>">

<div class="supplier-header"><h6>
Supplier: <?= $supplier['name'] ?> |
City: <?= $supplier['city'] ?> |
Mobile: <?= $supplier['mobile'] ?> |
Date: <strong><?= date("d-M-Y", strtotime($supplier['date'])) ?></strong></h6>

<button class="btn btn-success btn-sm float-end btn-print"
 style="margin-top:-33px;"
 onclick="printSection('<?= $pid ?>')">
 <i class="bi bi-printer"></i> Print
</button>
</div>

<?php foreach ($bills as $bill_id => $bill): 
$bill_total = 0;
?>

<table class="table table-bordered table-sm">
<thead>
<tr>
<th>#</th>
<th>Product</th>
<th>Batch</th>
<th>Qty</th>
<th>MRP</th>
<th>GST%</th>
<th>Amount</th>
<th>Expiry Return</th>
<th>Final Amount</th>
<th>Expiry</th>
</tr>
</thead>
<tbody>
<?php foreach ($bill['medicines'] as $m):

$amount = $m['qty1'] * $m['mrp'];
$return = $m['return_amount'];
$final_amount = $amount - $return;

$bill_total += $final_amount;
$supplier_total += $final_amount;
$grand_total += $final_amount;
?>
<tr>
<td><?= $sn++ ?></td>
<td><?= $m['product'] ?></td>
<td><?= $m['batch'] ?></td>
<td><?= $m['qty1'] ?></td>
<td><?= number_format($m['mrp'],2) ?></td>
<td><?= $m['gst'] ?>%</td>
<td><?= number_format($amount,2) ?></td>
<td class="text-danger">-₹<?= number_format($return,2) ?></td>
<td class="fw-bold">₹<?= number_format($final_amount,2) ?></td>
<td><?= $m['ex_date'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="text-end fw-bold">Bill Total: ₹<?= number_format($bill_total,2) ?></div>
<hr>
<?php endforeach; ?>

<div class="text-end text-success fw-bold">
Supplier Total: ₹<?= number_format($supplier_total,2) ?>
</div>

</div>
<?php endforeach; ?>

<div class="text-end fw-bold fs-5">
Overall Grand Total: ₹<?= number_format($grand_total,2) ?>
</div>

</div>
</div>
</div>

<!-- 🔥 PRINT METHOD SAME AS YOUR ORIGINAL -->
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
            th{background-color:#198754;color:white;font-weight:bold;}

            .invoice-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;border-bottom:2px solid #198754;padding-bottom:15px;}
            .invoice-header h1{font-size:28px;color:#198754;margin:0;}
            .store-info{text-align:right;font-size:14px;}

            h6{
                font-size:16px;
                font-weight:600;
                margin-bottom:12px;
            }

            .invoice-footer{margin-top:40px;border-top:2px solid #198754;padding-top:15px;text-align:center;font-size:14px;color:#555;}

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