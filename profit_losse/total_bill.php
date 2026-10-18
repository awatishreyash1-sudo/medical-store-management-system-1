<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$type = $date = $month = "";
$purchase_total = $purchase_return_total = 0;
$sales_total = $sales_return_total = 0;
$net_purchase = $net_sales = $profit = 0;

if (isset($_POST['search'])) {

    $type = $_POST['type'] ?? "";

    if ($type == "daily" && !empty($_POST['date'])) {
        $date = $_POST['date'];
        $purchase_sql = "DATE(datetime) = '$date'";
        $sales_sql = "DATE(datetime) = '$date'";
        $pr_sql = "DATE(return_date) = '$date'";
        $sr_sql = "DATE(return_date) = '$date'";
    }
    elseif ($type == "monthly" && !empty($_POST['month'])) {
        $month = $_POST['month'];
        $purchase_sql = "DATE_FORMAT(datetime,'%Y-%m') = '$month'";
        $sales_sql = "DATE_FORMAT(datetime,'%Y-%m') = '$month'";
        $pr_sql = "DATE_FORMAT(return_date,'%Y-%m') = '$month'";
        $sr_sql = "DATE_FORMAT(return_date,'%Y-%m') = '$month'";
    }
    else {
        $purchase_sql = $sales_sql = $pr_sql = $sr_sql = "1=0";
    }

    $purchase_total = $conn->query("SELECT IFNULL(SUM(amount),0) as total FROM s_medicin WHERE $purchase_sql")->fetch_assoc()['total'];
    $purchase_return_total = $conn->query("SELECT IFNULL(SUM(base_amount),0) as total FROM purchase_return WHERE $pr_sql")->fetch_assoc()['total'];
    $sales_total = $conn->query("SELECT IFNULL(SUM(amount),0) as total FROM sales_medicin WHERE $sales_sql")->fetch_assoc()['total'];
    $sales_return_total = $conn->query("SELECT IFNULL(SUM(return_amount),0) as total FROM sales_returns WHERE $sr_sql")->fetch_assoc()['total'];

    $net_purchase = $purchase_total - $purchase_return_total;
    $net_sales = $sales_total - $sales_return_total;
    $profit = $net_sales - $net_purchase;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Medical Store - Profit & Loss</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>

html, body {
    height:100%;
    margin:0;
}

body {
    background:#f4f6f9;
    font-family: 'Segoe UI', sans-serif;
    overflow-x:hidden;
}

.container-fluid, .row {
    margin:0;
    padding:0;
}

.main-content {
    margin-left:200px;
    padding:30px;
    min-height:100vh;
    width: 900px;
}

.topbar {
    background:white;
    padding:15px 25px;
    border-radius:10px;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
}

.card-box {
    border:none;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,0.05);
}

.table th, .table td {
    vertical-align: middle;
}

</style>
</head>

<body>

<div class="container-fluid">
<div class="row">

    <!-- Sidebar -->
    <?php include 'nav.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Top Header -->
        <div class="topbar d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">📊 Profit & Loss Report</h5>
            <div>Welcome, <strong><?= $_SESSION['name']; ?></strong></div>
        </div>

        <!-- Card -->
        <div class="card card-box p-4">

            <!-- Search Form -->
            <form method="POST" class="row g-3 mb-3">

                <div class="col-md-3">
                    <select name="type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="daily" <?= ($type=="daily")?'selected':'' ?>>Daily</option>
                        <option value="monthly" <?= ($type=="monthly")?'selected':'' ?>>Monthly</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <input type="date" name="date" value="<?= $date ?>" class="form-control">
                </div>

                <div class="col-md-3">
                    <input type="month" name="month" value="<?= $month ?>" class="form-control">
                </div>

                <div class="col-md-3">
                    <button type="submit" name="search" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Generate Report
                    </button>
                </div>

            </form>

            <?php if (!empty($type)): ?>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>Category</th>
                            <th>Amount (₹)</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>Total Purchase</td>
                            <td><?= number_format($purchase_total,2) ?></td>
                        </tr>

                        <tr>
                            <td class="text-danger">Purchase Return (-)</td>
                            <td class="text-danger"><?= number_format($purchase_return_total,2) ?></td>
                        </tr>

                        <tr class="table-info">
                            <td><strong>Net Purchase</strong></td>
                            <td><strong><?= number_format($net_purchase,2) ?></strong></td>
                        </tr>

                        <tr>
                            <td>Total Sales</td>
                            <td class="text-success"><?= number_format($sales_total,2) ?></td>
                        </tr>

                        <tr>
                            <td class="text-danger">Sales Return (-)</td>
                            <td class="text-danger"><?= number_format($sales_return_total,2) ?></td>
                        </tr>

                        <tr class="table-success">
                            <td><strong>Net Sales</strong></td>
                            <td><strong><?= number_format($net_sales,2) ?></strong></td>
                        </tr>

                        <tr class="<?= ($profit >= 0) ? 'table-success' : 'table-danger' ?>">
                            <td><strong>Final Profit / Loss</strong></td>
                            <td>
                                <strong>
                                    ₹ <?= number_format($profit,2) ?>
                                    (<?= ($profit >= 0) ? "Profit" : "Loss" ?>)
                                </strong>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <?php endif; ?>

        </div>

    </div>

</div>
</div>

</body>
</html>
