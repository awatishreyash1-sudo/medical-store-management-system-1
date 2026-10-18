<?php
session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "medical_shop");
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
      background-color: #f5f8fc;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card,.card {
         width: 1000px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
      margin-bottom: 20px;
      left:250px;
    }
    .card-header {
      background: #007bff;
      color: white;
      font-weight: 500;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }
    th {
      background-color: #0056b3;
      color: white;
    }
    .btn-print {
      border-radius: 20px;
    }
    @media print {
      .no-print { display: none !important; }
      body { background-color: #fff; }
    }
  </style>
</head>
<body>
    <div class="container-fluid">
    <div class="row">
    <?php include 'navbar8.php'; ?>

    <div class="card">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h2 class="text-primary"><i class="bi bi-bar-chart"></i> Sales Report</h2>
    <button class="btn btn-success btn-print" onclick="printPage()"><i class="bi bi-printer-fill"></i> Print All</button>
  </div>


  <form method="GET" class="row g-3 align-items-end no-print mb-4">
    <div class="col-md-4">
      <label class="form-label">Start Date</label>
      <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">End Date</label>
      <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>" required>
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
    </div>
</div>
</div>
  </form>
<div class="mt-3">
  <div id="printableArea">
  <?php
    $start = $_GET['start_date'] ?? date('Y-m-01');
    $end = $_GET['end_date'] ?? date('Y-m-d');
    $total = 0;

    $stmt = $conn->prepare("SELECT * FROM sales_bills WHERE DATE(datetime) BETWEEN ? AND ? ORDER BY id DESC");
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $bills = $stmt->get_result();

    while ($bill = $bills->fetch_assoc()):
      $total += $bill['f_total_amount'];
      $bill_id = $bill['id'];
  ?>
    <div class="card" id="bill-<?= $bill_id ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <strong>Patient:</strong> <?= htmlspecialchars($bill['patient_name']) ?> |
          <strong>Salesman:</strong> <?= htmlspecialchars($bill['salesman_name']) ?> |
          <strong>Date:</strong> <?= date('d-m-Y', strtotime($bill['datetime'])) ?>
        </div>
        <button class="btn btn-light btn-sm no-print" onclick="printBill('bill-<?= $bill_id ?>')"><i class="bi bi-printer-fill"></i> Print</button>
      </div>
      <div class="card-body">
        <p><strong>City:</strong> <?= $bill['city'] ?> | <strong>Mobile:</strong> <?= $bill['mobile'] ?></p>
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>Medicine</th>
              <th>Qty</th>
              <th>MRP</th>
              <th>GST (%)</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $med_stmt = $conn->prepare("SELECT product, qty1, mrp, gst FROM sales_medicin WHERE bill_id = ?");
              $med_stmt->bind_param("i", $bill_id);
              $med_stmt->execute();
              $meds = $med_stmt->get_result();

              while ($med = $meds->fetch_assoc()):
                $med_name_stmt = $conn->prepare("SELECT product FROM s_medicin WHERE id = ?");
                $med_name_stmt->bind_param("i", $med['product']);
                $med_name_stmt->execute();
                $med_name_stmt->bind_result($medicine_name);
                $med_name_stmt->fetch();
                $med_name_stmt->close();
            ?>
            <tr>
              <td><?= htmlspecialchars($medicine_name ?: 'Unknown') ?></td>
              <td><?= $med['qty1'] ?></td>
              <td>₹<?= number_format($med['mrp'], 2) ?></td>
              <td><?= $med['gst'] ?>%</td>
            </tr>
            <?php endwhile; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-end">Final Amount:</th>
              <th>₹<?= number_format($bill['f_total_amount'], 2) ?></th>
            </tr>
          </tfoot>
        </table>
        <div class="text-end fw-bold fs-5 mt-4">
    Total Sales: ₹<?= number_format($total, 2) ?>
  </div>
      </div>
    </div>
  <?php endwhile; ?>
  </div>

  
</div>

<script>
function printPage() {
  const content = document.getElementById("printableArea").innerHTML;
  const win = window.open('', '', 'width=1200,height=900');
  win.document.write('<html><head><title>Print Sales Report</title>');
  win.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
  win.document.write('</head><body>');
  win.document.write('<h2 class="text-center text-primary mb-4">Sales Report</h2>');
  win.document.write(content);
  win.document.write('</body></html>');
  win.document.close();
  win.print();
}

function printBill(id) {
  const content = document.getElementById(id).innerHTML;
  const win = window.open('', '', 'width=1000,height=800');
  win.document.write('<html><head><title>Print Bill</title>');
  win.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
  win.document.write('</head><body>');
  win.document.write(content);
  win.document.write('</body></html>');
  win.document.close();
  win.print();
}
</script>

</body>
</html>
