<?php
// DB connection
$con = new mysqli("localhost", "root", "", "medical_shop");
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Filter logic
$from_date = $to_date = "";
$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    $stmt = $con->prepare("
        SELECT sr.*, sm.product AS medicine_name, smd.batch, smd.ex_date, smd.mrp, smd.gst,
               sb.patient_name, sb.city, sb.mobile, sb.id AS bill_no, DATE(sr.return_date) AS bill_date
        FROM sales_returns sr
        JOIN ss_medicine sm ON sr.medicine_id = sm.id
        JOIN sales_medicin smd ON sr.sales_medicin_id = smd.id
        JOIN sales_bills sb ON smd.bill_id = sb.id
        WHERE DATE(sr.return_date) BETWEEN ? AND ?
        ORDER BY sb.id, sr.return_date
    ");
    $stmt->bind_param("ss", $from_date, $to_date);
    $stmt->execute();
    $results = $stmt->get_result();
    $stmt->close();
}

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Return Report</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            width: 1000px;
            margin-top: 30px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            left:130px;
        }
        .card-header {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            color: #fff;
            text-align: center;
            padding: 20px 15px;
        }
        .bill-header {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .bill-header strong {
            color: #1d3557;
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
        tfoot {
            background-color: #f1f3f5;
        }
        @media print {
            .no-print, .navbar, .btn, footer {
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
        <?php include 'navbar2.php'; ?>

        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    <h4><i class="bi bi-arrow-counterclockwise me-2"></i>Sales Return List</h4>
                </div>

                <div class="card-body">
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
                    </form>

                    <?php if (!empty($results) && $results->num_rows > 0): ?>
                        <?php
                        $current_bill = null;
                        $total_return_all = 0;
                        $i = 1;
                        while ($row = $results->fetch_assoc()):
                            if ($current_bill !== $row['bill_no']):
                                if ($current_bill !== null): ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>Total Return:</strong></td>
                                            <td colspan="2"><strong>₹<?= number_format($bill_total, 2) ?></strong></td>
                                        </tr>
                                    </tfoot>
                                    </table>
                                    <hr>
                                <?php endif;

                                $current_bill = $row['bill_no'];
                                $bill_total = 0;
                                ?>

                                <div class="bill-header mt-3">
                                    <div><strong>Patient:</strong> <?= htmlspecialchars($row['patient_name']) ?> | 
                                         <strong>City:</strong> <?= htmlspecialchars($row['city']) ?> | 
                                         <strong>Mobile:</strong> <?= htmlspecialchars($row['mobile']) ?></div>
                                    <div><strong>Bill No:</strong> <?= $row['bill_no'] ?> | 
                                         <strong>Date:</strong> <?= date("d-M-Y", strtotime($row['bill_date'])) ?></div>
                                </div>

                                <div class="table-responsive">
                                <table class="table table-bordered table-hover text-center align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Batch</th>
                                            <th>Qty</th>
                                            <th>MRP (₹)</th>
                                            <th>GST%</th>
                                            <th>Amount (₹)</th>
                                            <th>Expiry</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            <?php endif;

                            $amount = $row['return_qty'] * $row['mrp'];
                            $bill_total += $amount;
                            $total_return_all += $amount;
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['medicine_name']) ?></td>
                                <td><?= htmlspecialchars($row['batch']) ?></td>
                                <td><?= $row['return_qty'] ?></td>
                                <td>₹<?= number_format($row['mrp'], 2) ?></td>
                                <td><?= $row['gst'] ?>%</td>
                                <td>₹<?= number_format($amount, 2) ?></td>
                                <td><?= $row['ex_date'] ?></td>
                            </tr>
                        <?php endwhile; ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end"><strong>Total Return:</strong></td>
                                <td colspan="2"><strong>₹<?= number_format($bill_total, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                        </table>
                        <div class="alert alert-info text-end"><strong>Grand Total of All Returns: ₹<?= number_format($total_return_all, 2) ?></strong></div>

                    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                        <div class="alert alert-warning text-center mt-4">No sales return records found for the selected date range.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
