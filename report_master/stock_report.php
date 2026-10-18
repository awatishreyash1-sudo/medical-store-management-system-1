<?php
session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "medical_shop");

// Default empty filter values
$start_date = $end_date = "";
$whereClause = "";

// If filter is applied
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    if (!empty($start_date) && !empty($end_date)) {
        $whereClause = "WHERE DATE(sm.datetime) BETWEEN '$start_date' AND '$end_date'";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stock Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../nav.css">
  <style>
    body { background-color: #f9f9f9; font-family: 'Segoe UI', sans-serif; }
    .card { box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 12px; padding: 20px; left: 350px; width: 800px; }
    th { background: #007bff; color: white; position: sticky; top: 0; }
    table tbody tr:hover { background-color: #f1f1f1; }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <?php include 'navbar8.php'; ?>
    <div class="container mt-4">
      <div class="card">
        <h3 class="mb-4 text-primary text-center">🧾 Stock Report</h3>

        <!-- Total Stock Table -->
        <h5>Total Available Stock</h5>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Medicine Name</th>
              <th>Total Stock (Qty)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $result = $conn->query("SELECT id, product, qty FROM s_medicin");
            $i = 1;
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['product']) ?></td>
              <td><?= $row['qty'] ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <!-- Date Filter Form -->
        <form method="GET" class="row g-3 align-items-center mb-4 mt-5">
          <div class="col-auto">
            <label for="start_date" class="col-form-label">Start Date:</label>
          </div>
          <div class="col-auto">
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" required>
          </div>
          <div class="col-auto">
            <label for="end_date" class="col-form-label">End Date:</label>
          </div>
          <div class="col-auto">
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" required>
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="stock_report.php" class="btn btn-secondary">Reset</a>
          </div>
        </form>

        <!-- Daily Sales Summary -->
        <h5>Daily Stock Sold Summary</h5>
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>Date</th>
              <th>Medicine Name</th>
              <th>Sold Qty</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $query = "
              SELECT 
                DATE(sm.datetime) as sale_date, 
                sm.product as product_id, 
                SUM(sm.qty1) as total_sold, 
                m.product as product_name
              FROM sales_medicin sm
              JOIN s_medicin m ON sm.product = m.id
              $whereClause
              GROUP BY sale_date, product_id
              ORDER BY sale_date DESC
            ";
            $sales = $conn->query($query);
            if ($sales->num_rows > 0) {
              while ($row = $sales->fetch_assoc()):
            ?>
            <tr>
              <td><?= date('d-m-Y', strtotime($row['sale_date'])) ?></td>
              <td><?= htmlspecialchars($row['product_name']) ?></td>
              <td><?= $row['total_sold'] ?></td>
            </tr>
            <?php endwhile; } else { ?>
            <tr>
              <td colspan="3" class="text-center text-muted">No records found for selected date range.</td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
