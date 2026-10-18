<?php
$conn = new mysqli("localhost","root","","medical_shop");
if($conn->connect_error){
    die("Database Connection Failed");
}

$start    = $_GET['start'] ?? '';
$end      = $_GET['end'] ?? '';
$company  = $_GET['company'] ?? '';
$supplier = $_GET['supplier'] ?? '';

$where = "WHERE 1";
if($start && $end){
    $where .= " AND DATE(sm.datetime) BETWEEN '$start' AND '$end'";
}
if($company){
    $where .= " AND smp.company_name LIKE '%$company%'";
}
if($supplier){
    $where .= " AND smp.supplier_name LIKE '%$supplier%'";
}

$sql = "
SELECT 
 smp.company_name,
 smp.supplier_name,
 DATE(sm.datetime) as pdate,
 sm.product,
 sm.amount
FROM ss_medicine sm
JOIN ss_bills sb ON sm.bill_id = sb.id
JOIN supplier_master smp ON sb.s_name = smp.supplier_name
$where
ORDER BY pdate DESC
";

$result = $conn->query($sql);
if(!$result){
    die($conn->error);
}

$data = [];
while($row = $result->fetch_assoc()){
    $key = $row['company_name'].'|'.$row['supplier_name'].'|'.$row['pdate'];
    $data[$key][] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Purchase Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
body{background:#f4f6f9;font-size:14px;}
.card{max-width:1000px;margin:20px auto 20px 250px;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,.08);}
.card-header{background:#0d6efd;color:#fff;}
th{background:#198754 !important;color:#fff;text-align:center;}
td{text-align:center;vertical-align:middle;}
@media print{
    nav,.btn,form{display:none!important;}
    .card{margin:0;box-shadow:none;}
}
</style>
</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include 'navbar7.php'; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-bag-check"></i> Company Bill Generate</h5>
    </div>

    <div class="card-body">

        <form class="row g-2 mb-3">
            <div class="col"><input type="date" name="start" value="<?= $start ?>" class="form-control"></div>
            <div class="col"><input type="date" name="end" value="<?= $end ?>" class="form-control"></div>
            <div class="col"><input type="text" name="company" value="<?= $company ?>" placeholder="Company" class="form-control"></div>
            <div class="col"><input type="text" name="supplier" value="<?= $supplier ?>" placeholder="Supplier" class="form-control"></div>
            <div class="col"><button class="btn btn-dark w-100">Search</button></div>
        </form>

        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>Company</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Medicine</th>
                <th>Amount</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            </thead>

            <tbody>
            <?php
            $i=1;
            foreach($data as $rows){
                $rowspan = count($rows);
                $total = array_sum(array_column($rows,'amount'));
                $first = true;

                foreach($rows as $r){
            ?>
            <tr>
                <?php if($first){ ?>
                <td rowspan="<?= $rowspan ?>"><?= $i++ ?></td>
                <td rowspan="<?= $rowspan ?>"><?= $r['company_name'] ?></td>
                <td rowspan="<?= $rowspan ?>"><?= $r['supplier_name'] ?></td>
                <td rowspan="<?= $rowspan ?>"><?= $r['pdate'] ?></td>
                <?php } ?>

                <td><?= $r['product'] ?></td>
                <td><?= number_format($r['amount'],2) ?></td>

                <?php if($first){ ?>
                <td rowspan="<?= $rowspan ?>" class="fw-bold"><?= number_format($total,2) ?></td>
                <td rowspan="<?= $rowspan ?>">
                    <a class="btn btn-success btn-sm d-flex align-items-center gap-1"
   href="purchase_invoice.php?company=<?= $r['company_name'] ?>&supplier=<?= $r['supplier_name'] ?>&date=<?= $r['pdate'] ?>">
   <i class="bi bi-file-earmark-plus-fill"></i> Generate Bill
</a>

                </td>
                <?php } ?>
            </tr>
            <?php $first=false; } } ?>
            </tbody>
        </table>

    </div> <!-- card-body -->
</div> <!-- card -->

</div> <!-- row -->
</div> <!-- container-fluid -->

</body>
</html>
