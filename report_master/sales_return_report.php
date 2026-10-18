<?php
session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$from_date = $to_date = "";
$result = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    $sql = "SELECT * FROM sales_returns 
            WHERE DATE(return_date) BETWEEN '$from_date' AND '$to_date' 
            ORDER BY return_date DESC";
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Return Report</title>
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
            left: 250px;
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
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'navbar8.php'; ?>

        
<div class="card">
    <div class="card-header text-center">
        <h3>Sales Return Report</h3>
    </div>
    <div class="card-body">

    <!-- Filter Form -->
    <form method="POST" class="row align-items-end g-2 mb-4 no-print">
        <div class="col-md-4">
            <label for="from_date" class="form-label">From Date</label>
            <input type="date" class="form-control" name="from_date" value="<?= htmlspecialchars($from_date) ?>" required>
        </div>
        <div class="col-md-4">
            <label for="to_date" class="form-label">To Date</label>
            <input type="date" class="form-control" name="to_date" value="<?= htmlspecialchars($to_date) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label d-block invisible">Search</label>
            <button type="submit" class="btn btn-danger w-100">Search</button>
        </div>
    </form>

    <?php if (!empty($result)): ?>
    <div id="print-area">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Sales Medicine ID</th>
                    <th>Medicine ID</th>
                    <th>Return Qty</th>
                    <th>MRP</th>
                    <th>GST</th>
                    <th>Base Amt</th>
                    <th>GST Amt</th>
                    <th>Return Amt</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_return = 0;
                foreach ($result as $row):
                    $total_return += $row['return_amount'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['sales_medicin_id']) ?></td>
                    <td><?= htmlspecialchars($row['medicine_id']) ?></td>
                    <td><?= htmlspecialchars($row['return_qty']) ?></td>
                    <td><?= htmlspecialchars($row['mrp']) ?></td>
                    <td><?= htmlspecialchars($row['gst']) ?></td>
                    <td><?= htmlspecialchars($row['base_amount']) ?></td>
                    <td><?= htmlspecialchars($row['gst_amount']) ?></td>
                    <td><?= htmlspecialchars($row['return_amount']) ?></td>
                    <td><?= date("d-m-Y h:i A", strtotime($row['return_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="fw-bold">
                    <td colspan="8" class="text-end">Total Return Amount</td>
                    <td colspan="2"><?= number_format($total_return, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Print Button -->
    <div class="text-end no-print">
        <button onclick="printRecords()" class="btn btn-success">Print</button>
    </div>

    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <p class="text-danger text-center">No records found for selected date range.</p>
    <?php endif; ?>
</div>

<!-- Print Script -->
<script>
function printRecords() {
    const printContent = document.getElementById("print-area").innerHTML;
    const win = window.open('', '', 'width=1000,height=700');

    win.document.write(`
        <html>
        <head>
            <title>Sales Return Report</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 6px; font-size: 12px; text-align: center; }
                h3 { text-align: center; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <h3>Sales Return Report</h3>
            ${printContent}
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
