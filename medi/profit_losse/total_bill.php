<?php
session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch purchase dates for dropdown
$dateOptions = [];
$result = $conn->query("SELECT DISTINCT DATE(datetime) as date FROM s_medicin ORDER BY DATE(datetime) ASC");
while ($row = $result->fetch_assoc()) {
    $dateOptions[] = $row['date'];
}

// Form values
$purchase_start_date = $purchase_end_date = "";
$from_date = $to_date = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $purchase_start_date = $_POST['purchase_start_date'] ?? "";
    $purchase_end_date = $_POST['purchase_end_date'] ?? "";
    $from_date = $_POST['from_date'] ?? "";
    $to_date = $_POST['to_date'] ?? "";
}

function getStats($conn, $table, $amount_column, $date_column, $from = "", $to = "") {
    $where = "1=1";
    if (!empty($from) && !empty($to)) {
        $where = "DATE($date_column) BETWEEN '$from' AND '$to'";
    }
    $sql = "SELECT COUNT(*) as count, SUM($amount_column) as total FROM $table WHERE $where";
    $res = $conn->query($sql);
    $row = $res && $res->num_rows ? $res->fetch_assoc() : ['count' => 0, 'total' => 0];
    return ['count' => $row['count'], 'total' => $row['total']];
}

$purchase = ['count' => 0, 'total' => 0];
if (!empty($purchase_start_date) && !empty($purchase_end_date)) {
    $sql = "SELECT COUNT(*) as count, SUM(amount) as total FROM s_medicin 
            WHERE DATE(datetime) BETWEEN '$purchase_start_date' AND '$purchase_end_date'";
    $res = $conn->query($sql);
    $purchase = $res && $res->num_rows ? $res->fetch_assoc() : ['count' => 0, 'total' => 0];
}

$purchase_return = getStats($conn, 'purchase_return', 'base_amount', 'return_date', $from_date, $to_date);
$expiry = getStats($conn, 'expiry_medicin', 'amount', 'datetime', $from_date, $to_date);
$sales = getStats($conn, 'sales_medicin', 'amount', 'datetime', $from_date, $to_date);
$sales_return = getStats($conn, 'sales_returns', 'return_amount', 'return_date', $from_date, $to_date);

$net_purchase = $purchase['total'] - $purchase_return['total'] - $expiry['total'];
$net_sales = $sales['total'] - $sales_return['total'];
$net_profit = $net_sales - $net_purchase;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profit & Loss Report (Table)</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../nav.css">
    <style>
       body {
            background: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            margin-top: 30px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            left: 130px;
            width: 1000px;
        }
        .card-header {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            color: #fff;
            text-align: center;
            padding: 25px 15px;
        }
        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 22px;
        }
        .table-bordered th, .table-bordered td { vertical-align: middle; }
        .heading { font-size: 24px; font-weight: bold; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
        <?php include 'nav.php'; ?>

 <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    <h4><i class="bi bi-arrow-counterclockwise me-2"></i>Sales Return Bill</h4>
                </div>

                <div class="card-body">

        <form method="POST" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Purchase Start Date</label>
                <select name="purchase_start_date" class="form-select" required>
                    <option value="">-- Start Date --</option>
                    <?php foreach ($dateOptions as $date): ?>
                        <option value="<?= $date ?>" <?= ($purchase_start_date == $date) ? 'selected' : '' ?>><?= $date ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Purchase End Date</label>
                <select name="purchase_end_date" class="form-select" required>
                    <option value="">-- End Date --</option>
                    <?php foreach ($dateOptions as $date): ?>
                        <option value="<?= $date ?>" <?= ($purchase_end_date == $date) ? 'selected' : '' ?>><?= $date ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>From Date (Returns/Sales)</label>
                <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>" required>
            </div>
            <div class="col-md-3">
                <label>To Date (Returns/Sales)</label>
                <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>" required>
            </div>
            <div class="col-12 text-center mt-3">
                <button class="btn btn-primary">📅 Generate Report</button>
            </div>
        </form>

        <?php if ($purchase['count'] > 0): ?>
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Amount (₹)</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Total Purchase</td>
                        <td><?= number_format($purchase['total'], 2) ?></td>
                        <td><?= $purchase['count'] ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Purchase Return</td>
                        <td class="text-danger">-<?= number_format($purchase_return['total'], 2) ?></td>
                        <td><?= $purchase_return['count'] ?></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Expired Medicines</td>
                        <td class="text-warning">-<?= number_format($expiry['total'], 2) ?></td>
                        <td><?= $expiry['count'] ?></td>
                    </tr>
                    <tr class="table-info">
                        <td>4</td>
                        <td><strong>Net Purchase</strong></td>
                        <td><strong><?= number_format($net_purchase, 2) ?></strong></td>
                        <td>—</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Total Sales</td>
                        <td class="text-success"><?= number_format($sales['total'], 2) ?></td>
                        <td><?= $sales['count'] ?></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Sales Return</td>
                        <td class="text-danger">-<?= number_format($sales_return['total'], 2) ?></td>
                        <td><?= $sales_return['count'] ?></td>
                    </tr>
                    <tr class="table-success">
                        <td>7</td>
                        <td><strong>Net Sales</strong></td>
                        <td><strong><?= number_format($net_sales, 2) ?></strong></td>
                        <td>—</td>
                    </tr>
                    <tr class="<?= ($net_profit >= 0) ? 'table-success' : 'table-danger' ?>">
                        <td>8</td>
                        <td><strong>Final Profit / Loss</strong></td>
                        <td><strong><?= number_format($net_profit, 2) ?> <?= ($net_profit >= 0) ? '(Profit)' : '(Loss)' ?></strong></td>
                        <td>—</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">No purchase data found for selected date range.</div>
        <?php endif; ?>
    </div>
</div>
</div>
</div>
</div>
<div class="mt-3">
</body>
</html>
