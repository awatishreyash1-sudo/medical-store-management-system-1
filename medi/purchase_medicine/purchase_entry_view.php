<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Purchase Entry</title>
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            left: 30px;
            max-width: 1000px;
            width: 1000px;
        }
        .card-header {
             background: linear-gradient(45deg, #17a2b8, #138496);
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
        <!-- Sidebar -->
        <?php include 'navbar.php'; ?>

<div class="container-fluid">
    <div class="row justify-content-center mt-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow rounded-4">
                <div class="card-header bg-info text-white text-center rounded-top-4 py-3">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> purches Entry Report</h4>
                </div>

    <div class="card-body">

    <!-- Date Filter -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" required>
        </div>
        <div class="col-md-4">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" required>
        </div>
        <div class="col-md-4 align-self-end">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>
    </div>
    </div>

    <?php if ($start_date && $end_date): ?>
        <?php
        $stmt = $conn->prepare("SELECT * FROM ss_bills WHERE DATE(datetime) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $bills = $stmt->get_result();

        if ($bills->num_rows > 0): ?>
            <?php while ($bill = $bills->fetch_assoc()): ?>
                <div class="card mb-4 shadow">
                    <div class="card-header bg-info text-white d-flex justify-content-between">
                        <strong>
                            Bill ID: <?= $bill['id'] ?> |
                            Supplier: <?= $bill['s_name'] ?> |
                            Amount: ₹<?= $bill['f_total_amount'] ?>
                        </strong>
                        <div>
                            <a href="purchase1_edit.php?id=<?= $bill['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="purchase_delete.php?id=<?= $bill['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this bill?')">Delete</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Medicine</th>
                                    <th>Batch</th>
                                    <th>GST</th>
                                    <th>MRP</th>
                                    <th>Expiry</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $bill_id = $bill['id'];
                                $med_stmt = $conn->prepare("SELECT * FROM ss_medicine WHERE bill_id = ?");
                                $med_stmt->bind_param("i", $bill_id);
                                $med_stmt->execute();
                                $med_result = $med_stmt->get_result();
                                while ($med = $med_result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= $med['sr_no'] ?></td>
                                    <td><?= $med['product'] ?></td>
                                    <td><?= $med['batch'] ?></td>
                                    <td><?= $med['gst'] ?></td>
                                    <td><?= $med['mrp'] ?></td>
                                    <td><?= $med['ex_date'] ?></td>
                                    <td><?= $med['qty1'] ?></td>
                                    <td><?= $med['amount'] ?></td>
                                </tr>
                                <?php endwhile; $med_stmt->close(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-warning">No purchase entries found for the selected date range.</div>
        <?php endif; $stmt->close(); ?>
    <?php endif; ?>
</div>
        </div>
        </div>

</body>
</html>
