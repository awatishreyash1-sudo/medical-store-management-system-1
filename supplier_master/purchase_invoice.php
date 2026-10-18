<?php
$conn = new mysqli("localhost","root","","medical_shop");

$company  = $_GET['company'];
$supplier = $_GET['supplier'];
$date     = $_GET['date'];

$sql = "
SELECT sm.product, sm.qty1, sm.mrp, sm.amount
FROM ss_medicine sm
JOIN ss_bills sb ON sm.bill_id = sb.id
JOIN supplier_master sp ON sb.s_name = sp.supplier_name
WHERE sp.company_name='$company'
AND sp.supplier_name='$supplier'
AND DATE(sm.datetime)='$date'
";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Purchase Invoice</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
@media print {.no-print{display:none}}
</style>

<script>
/* ===== MEDICAL STORE DETAILS ===== */
const STORE = {
    name: "Shree Medical Store",
    address: "Shop No.101, Main Bazar, Dudhgaon - 416315",
    gst: "27ABCDE1234F1Z5",
    phone: "9876543210"
};

/* ===== PRINT FUNCTION ===== */
function printSection(id){
    const section = document.getElementById(id).cloneNode(true);
    const win = window.open('', '', 'width=1000,height=800');

    win.document.write(`
    <html>
    <head>
        <title>Purchase Invoice Print</title>
        <style>
            body{font-family:Arial;margin:30px;color:#333;}
            table{width:100%;border-collapse:collapse;margin-top:20px;}
            th,td{border:1px solid #ddd;padding:10px;text-align:center;}
            th{background:#198754;color:#fff;}
            .invoice-header{display:flex;justify-content:space-between;border-bottom:2px solid #198754;padding-bottom:15px;}
            .invoice-header h1{margin:0;color:#198754;}
            .store-info{text-align:right;font-size:14px;}
            .invoice-footer{margin-top:40px;border-top:2px solid #198754;padding-top:10px;text-align:center;font-size:13px;}
        </style>
    </head>
    <body>

    <div class="invoice-header">
        <h1>${STORE.name}</h1>
        <div class="store-info">
            <p>${STORE.address}</p>
            <p><b>GSTIN:</b> ${STORE.gst}</p>
            <p><b>Phone:</b> ${STORE.phone}</p>
        </div>
    </div>

    ${section.innerHTML}

    <div class="invoice-footer">
        <p>Thank you for your business</p>
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

</head>

<body>

<div class="container mt-4 border p-4" id="print-area">

<h4 class="text-center mb-3">🧾 PURCHASE INVOICE</h4>

<p><b>Company:</b> <?= $company ?></p>
<p><b>Supplier:</b> <?= $supplier ?></p>
<p><b>Date:</b> <?= date("d-m-Y", strtotime($date)) ?></p>

<table class="table table-bordered mt-3">
<thead class="table-dark">
<tr>
    <th>#</th>
    <th>Medicine</th>
    <th>Qty</th>
    <th>MRP</th>
    <th>Amount</th>
</tr>
</thead>

<tbody>
<?php
$i=1; 
$total=0;
while($r=$res->fetch_assoc()){
    $total += $r['amount'];
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= $r['product'] ?></td>
    <td><?= $r['qty1'] ?></td>
    <td><?= number_format($r['mrp'],2) ?></td>
    <td><?= number_format($r['amount'],2) ?></td>
</tr>
<?php } ?>
</tbody>

<tfoot>
<tr>
    <th colspan="4" class="text-end">Total</th>
    <th><?= number_format($total,2) ?></th>
</tr>
</tfoot>
</table>

</div>

<div class="container text-end mt-3 no-print">
    <button onclick="printSection('print-area')" class="btn btn-success">
        🖨 Print Invoice
    </button>
</div>

</body>
</html>
