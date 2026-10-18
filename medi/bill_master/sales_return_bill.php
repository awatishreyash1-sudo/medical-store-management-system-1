<?php
// DB connection
$con = new mysqli("localhost", "root", "", "medical_shop");
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

session_start(); // REQUIRED to use $_SESSION
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$from_date = $to_date = "";
$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    $stmt = $con->prepare("
        SELECT sr.*, sm.product AS medicine_name
        FROM sales_returns sr
        JOIN s_medicin sm ON sr.medicine_id = sm.id
        WHERE DATE(sr.return_date) BETWEEN ? AND ?
        ORDER BY sr.return_date DESC
    ");
    $stmt->bind_param("ss", $from_date, $to_date);
    $stmt->execute();
    $results = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Return Report</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
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
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            background-color: #1d3557;
            border-color: #1d3557;
        }
        .btn-primary:hover {
            background-color: #16324f;
        }
        .btn-danger {
            background-color: #e63946;
            border: none;
        }
        .btn-danger:hover {
            background-color: #d62839;
        }
        table th {
            background-color: #1d3557;
            color: #fff;
            font-size: 14px;
        }
        table td {
            font-size: 14px;
            vertical-align: middle;
        }
        .table-responsive {
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border-radius: 10px;
            overflow-x: auto;
        }
        tfoot {
            background-color: #f1f3f5;
        }
        .no-print {
            margin-bottom: 25px;
        }
        @media print {
            .no-print, .navbar, footer {
                display: none !important;
            }
            body {
                background: white;
            }
            .card {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar7.php'; ?>

        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    <h4><i class="bi bi-arrow-counterclockwise me-2"></i>Sales Return Bill</h4>
                </div>

                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="POST" class="row g-3 justify-content-center no-print">
                        <div class="col-md-4">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="date" name="from_date" value="<?= $from_date ?>" required class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="date" name="to_date" value="<?= $to_date ?>" required class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
                        </div>
                        <?php if (!empty($results) && $results->num_rows > 0): ?>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger w-100" onclick="printRecords()">
                                <i class="bi bi-printer-fill"></i> Print
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>

                    <!-- Printable Content -->
                    <div id="printableArea">
                        <?php if (!empty($results) && $results->num_rows > 0): ?>
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Return Date</th>
                                        <th>Medicine Name</th>
                                        <th>Returned Qty</th>
                                        <th>MRP (₹)</th>
                                        <th>GST (%)</th>
                                        <th>Base Amount (₹)</th>
                                        <th>GST Amount (₹)</th>
                                        <th>Total Return (₹)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $total = 0;
                                    while ($row = $results->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['return_date'])) ?></td>
                                        <td><?= htmlspecialchars($row['medicine_name']) ?></td>
                                        <td><?= $row['return_qty'] ?></td>
                                        <td><?= number_format($row['mrp'], 2) ?></td>
                                        <td><?= $row['gst'] ?>%</td>
                                        <td><?= number_format($row['base_amount'], 2) ?></td>
                                        <td><?= number_format($row['gst_amount'], 2) ?></td>
                                        <td><strong><?= number_format($row['return_amount'], 2) ?></strong></td>
                                    </tr>
                                    <?php $total += $row['return_amount']; endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-end"><strong>Total Return Amount:</strong></td>
                                        <td><strong><?= number_format($total, 2) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                        <div class="alert alert-warning text-center mt-4">No sales return records found for the selected date range.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Script -->
<script>
function printRecords() {
    const printContents = document.getElementById('printableArea').innerHTML;
    const printWindow = window.open('', '', 'width=1200,height=900');
    printWindow.document.write(`
        <html>
        <head>
            <title>Sales Return Report</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                table, th, td { border: 1px solid #000; }
                th, td { padding: 8px; text-align: center; font-size: 14px; }
                th { background-color: #1d3557; color: #fff; }
            </style>
        </head>
        <body>
            <h3 class="text-center">Sales Return Report</h3>
            ${printContents}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}
</script>

</body>
</html>
