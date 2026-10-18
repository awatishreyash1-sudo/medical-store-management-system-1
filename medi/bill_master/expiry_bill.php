<?php
session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$from = $to = "";
$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];

    $stmt = $conn->prepare("SELECT * FROM expiry_medicin WHERE ex_date BETWEEN ? AND ? ORDER BY ex_date ASC");
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expiry Medicine Bill Report</title>
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
            margin-left: auto;
            margin-right: auto;
            left:120px;
        }
        .card-header {
            background: #0069d9;
            color: #fff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
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
        .btn-primary, .btn-danger {
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
        <?php include 'navbar7.php'; ?>
    </div>

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Expiry Medicine Bill Report</h4>
                <button class="btn btn-danger btn-sm no-print" onclick="printRecords()"><i class="bi bi-printer-fill"></i> Print</button>
            </div>

            <div class="p-4">
                <!-- Filter Form -->
                <form method="post" class="row g-3 mb-4 no-print">
                    <div class="col-md-5">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from_date" required value="<?= htmlspecialchars($from) ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to_date" required value="<?= htmlspecialchars($to) ?>">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>

                <!-- Report Table -->
                <?php if ($results && $results->num_rows > 0): ?>
                    <div id="reportContent">
                        <table class="table table-bordered table-striped text-center align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Company</th>
                                    <th>Batch</th>
                                    <th>Expiry Date</th>
                                    <th>Enter Date</th>
                                    <th>Qty</th>
                                    <th>MRP</th>
                                    <th>GST%</th>
                                    <th>Pay Mode</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_amount = 0;
                                $counter = 1;
                                while ($row = $results->fetch_assoc()):
                                    $total_amount += $row['amount'];
                                ?>
                                <tr>
                                    <td><?= $counter++ ?></td>
                                    <td><?= htmlspecialchars($row['product']) ?></td>
                                    <td><?= htmlspecialchars($row['company']) ?></td>
                                    <td><?= htmlspecialchars($row['batch']) ?></td>
                                    <td><?= date("d-m-Y", strtotime($row['ex_date'])) ?></td>
                                    <td><?= $row['datetime'] ?></td>
                                    <td><?= $row['new_qty'] ?></td>
                                    <td><?= number_format($row['mrp'], 2) ?></td>
                                    <td><?= number_format($row['gst'], 2) ?></td>
                                    <td><?= $row['pay_mode'] ?></td>
                                    <td><?= number_format($row['amount'], 2) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="10" class="text-end">Total Amount:</th>
                                    <th class="text-start">₹ <?= number_format($total_amount, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="alert alert-warning text-center">No expiry medicine found for selected date range.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Print Script -->
<script>
function printRecords() {
    const printContent = document.getElementById("reportContent").innerHTML;
    const win = window.open('', '', 'width=1000,height=700');

    win.document.write(`
        <html>
        <head>
            <title>Print - Expiry Medicine Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 6px; font-size: 12px; text-align: center; }
                h2 { text-align: center; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <h2>Expiry Medicine Report</h2>
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
