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

    $sql = "SELECT * FROM s_medicin
            WHERE DATE(datetime) BETWEEN '$from_date' AND '$to_date'
            ORDER BY datetime DESC";

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
    <title>Sales Report with GST</title>
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

    <script>
    function printRecords() {
        const printContent = document.getElementById("reportTable").innerHTML;
        const win = window.open('', '', 'width=1000,height=700');

        win.document.write(`
            <html>
            <head>
                <title>Print</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 6px; font-size: 12px; text-align: center; }
                    h2, h3, h4 { margin: 0; text-align: center; }
                </style>
            </head>
            <body>
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
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'navbar8.php'; ?>

        <div class="card">
            <div class="mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="page-title"><i class="bi bi-bar-chart"></i> Purchase Records</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from_date" value="<?= htmlspecialchars($from_date) ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to_date" value="<?= htmlspecialchars($to_date) ?>" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>

                <?php if (!empty($result)): ?>
                <div class="table-responsive" id="reportTable">
                    <div style="text-align: center; margin-bottom: 20px;">
                            <h3 style="color: #2eb8b8;">Purchase Bill Report</h3>
                        </div>

                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Bill No</th>
                                <th>Supplier</th>
                                <th>Medicine</th>
                                <th>Qty</th>
                                <th>MRP</th>
                                <th>GST</th>
                                <th>Total</th>
                                <th>Expiry Date</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row): 
                                $qty = isset($row['qty']) ? $row['qty'] : 0;
                                $mrp = isset($row['mrp']) ? $row['mrp'] : 0;
                                $gst = isset($row['gst']) ? $row['gst'] : 0;
                                $total = $qty * $mrp;
                                $gst_amount = $total * $gst / 100;
                                $total_with_gst = $total + $gst_amount;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['s_name']) ?></td>
                                <td><?= htmlspecialchars($row['product']) ?></td>
                                <td><?= $qty ?></td>
                                <td><?= number_format($mrp, 2) ?></td>
                                <td><?= number_format($gst, 2) ?></td>
                                <td><?= number_format($total_with_gst, 2) ?></td>
                                <td><?= htmlspecialchars($row['ex_date']) ?></td>
                                <td><?= htmlspecialchars($row['datetime']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3 no-print">
                    <button onclick="printRecords()" class="btn btn-success">Print</button>
                </div>

                <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <p class="text-danger text-center">No records found for selected date range.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
                <div class="mt-3">
</body>
</html>
