

<?php
session_start(); // <-- REQUIRED to use $_SESSION

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}


$conn = new mysqli("localhost", "root", "", "medical_shop");
include '../sale_medicine/config.php';
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
      background-color: #eef2f7;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      width: 1000px;
     
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      left :250px;
    }
    .card-header {
      background: #0069d9;
      color: #fff;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }
    h2.page-title {
      color: #333;
      margin: 0;
    }
    .filter-row .form-control {
      height: 45px;
    }
    table thead th {
      background: #0056b3;
      color: #fff;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    table tbody tr:hover {
      background: rgba(0,123,255,0.05);
    }
    .btn-primary, .btn-success {
      border-radius: 20px;
    }
    .no-print {
      margin-bottom: 20px;
    }
    @media print {
      .no-print {
        display: none !important;
      }
      body {
        background-color: #fff;
      }
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
    <?php include 'navbar7.php'; ?>

    
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="bi bi-bar-chart"></i> Sales Report</h2>
        <button class="btn btn-light no-print" onclick="printRecords()">
          <i class="bi bi-printer-fill"></i> Print
        </button>
      </div>
      <div class="card-body">
        <form method="GET" class="row g-3 align-items-end filter-row no-print">
          <div class="col-md-4">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" required value="<?= $_GET['start_date'] ?? '' ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" required value="<?= $_GET['end_date'] ?? '' ?>">
          </div>
          <div class="col-md-4 text-end">
            <button type="submit" class="btn btn-success px-4">
              <i class="bi bi-search"></i> Search
            </button>
            <a href="sell_medicine.php" class="btn btn-secondary px-4">
              <i class="bi bi-arrow-left"></i> Back
            </a>
          </div>
        </form>

        <div class="table-responsive" id="printableArea">
          <table class="table table-striped table-bordered align-middle">
  <thead>
    <tr>
      <th>Salesman</th>
      <th>Patient</th>
      <th>Medicine</th>
      <th>Qty</th>
      <th>MRP</th>
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
    $end = $_GET['end_date'] ?? date('Y-m-d');
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
        $mname = $mname ?: "<span class='text-danger'>Not found</span>";

        echo "<tr>";
        if ($first) {
          echo "<td>{$bill['salesman_name']}</td><td>{$bill['patient_name']}</td>";
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
  <tfoot class="table-light">
    <tr>
      <th colspan="9" class="text-end">Total Sales:</th>
      <th>₹ <?= number_format($total, 2) ?></th>
    </tr>
  </tfoot>
</table>

        </div>
      </div>
    </div>
  </div>
            </div>
            
            <div class="mt-3">
            <script>
    function printRecords() {
      const header = `
        <div class="text-center mb-4">
          <h1 style="color:#0069d9;">Sales Report</h1>
        </div>
         <div class="text-center mb-4">
                                <h4><strong>Medical Store</strong></h4>
                                <p>Shop No. 101, Main Bazar, Dudhagon -416 315</p>
                                <p>GSTIN: 27ABCDE1234F1Z5 | Phone: 9876543210</p>
                            </div>
                            `;
       
      const content = document.getElementById('printableArea').innerHTML;
      const pw = window.open('', '', 'width=1200,height=900');
      pw.document.write('<html><head><title>Print</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>');
      pw.document.write(header + content);
      pw.document.close();
      pw.focus();
      pw.print();
      pw.close();
    }
  </script>

  

</body>
</html>

<script>
function printBill(id) {
  const content = document.getElementById(id).innerHTML;
  const printWindow = window.open('', '', 'width=1000,height=800');
  printWindow.document.write(`
    <html>
    <head>
      <title>Print Bill</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <style>
        body { padding: 20px; font-family: Arial; }
        table, th, td { border: 1px solid #000; border-collapse: collapse; }
        th, td { padding: 6px; text-align: center; }
        .card-header { background-color: #0069d9; color: white; padding: 10px; }
      </style>
    </head>
    <body>
    <div class="text-center mb-4">
                                <h4><strong>Medical Store</strong></h4>
                                <p>Shop No. 101, Main Bazar, Dudhagon -416 315</p>
                                <p>GSTIN: 27ABCDE1234F1Z5 | Phone: 9876543210</p>
                            </div>
      ${content}
    </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}
</script>


<?php
$start = $_GET['start_date'] ?? date('Y-m-d');
$end = $_GET['end_date'] ?? date('Y-m-d');
$total = 0;

$bq = $conn->prepare("SELECT * FROM sales_bills WHERE DATE(datetime) BETWEEN ? AND ? ORDER BY id DESC");
$bq->bind_param("ss", $start, $end);
$bq->execute();
$bills = $bq->get_result();

while ($bill = $bills->fetch_assoc()) {
  $total += $bill['f_total_amount'];
  $bill_id = $bill['id'];
  ?>
  
  <div class="card mb-4 shadow-sm" id="bill-<?= $bill_id ?>">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Patient: <?= htmlspecialchars($bill['patient_name']) ?></h5>
        <small>Salesman: <?= htmlspecialchars($bill['salesman_name']) ?> | <?= date('d-m-Y', strtotime($bill['datetime'])) ?></small>
      </div>
      <button class="btn btn-light btn-sm" onclick="printBill('bill-<?= $bill_id ?>')">
        <i class="bi bi-printer-fill"></i> Print
      </button>
    </div>
    <div class="card-body">
      <p><strong>City:</strong> <?= $bill['city'] ?> | <strong>Mobile:</strong> <?= $bill['mobile'] ?></p>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead class="table-light">
            <tr>
              <th>Medicine</th>
              <th>Qty</th>
              <th>MRP</th>
              <th>GST (%)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $mq = $conn->prepare("SELECT product, qty1, mrp, gst FROM sales_medicin WHERE bill_id=?");
            $mq->bind_param("i", $bill_id);
            $mq->execute();
            $meds = $mq->get_result();
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
              ?>
              <tr>
                <td><?= $mname ?: "<span class='text-danger'>Not found</span>" ?></td>
                <td><?= $med['qty1'] ?></td>
                <td>₹<?= number_format($med['mrp'], 2) ?></td>
                <td><?= $med['gst'] ?>%</td>
              </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-end">Total Amount:</th>
              <th>₹<?= number_format($bill['f_total_amount'], 2) ?></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

<?php } ?>
