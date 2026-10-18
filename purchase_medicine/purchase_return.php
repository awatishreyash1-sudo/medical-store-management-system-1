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
        body { background: #f5f7fa; 
            font-family: 'Segoe UI', sans-serif; 
        }
        .card { 
            margin-top: 30px; 
            border-radius: 15px; 
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08); 
            overflow: hidden; 
            left:120px;
        }
        .card-header { 
            background: linear-gradient(45deg, #17a2b8, #138496); 
            color: #fff; 
            padding: 20px; 
        }
        table th { 
            background-color: #1d3557; 
            color: #fff; 
        }
        table td { 
            font-size: 14px; 
        }
        @media print {
            .no-print, .navbar, footer { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
    <?php include 'navbar.php'; ?>

    <div class="row justify-content-center mt-4 mb-4">
        <div class="col-lg-10">
            <div class="card shadow rounded-4">
                <div class="card-header text-white text-center">
                    <h4><i class="bi bi-pencil-square"></i> Purchase Entry</h4>
                </div>
                <div class="card-body">
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
                        <div class="card my-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong>
                                    Bill ID: <?= $bill['id'] ?> |
                                    Supplier: <?= $bill['s_name'] ?> |
                                    Amount: ₹<?= $bill['f_total_amount'] ?> |
                                    Pay Mode: <?= $bill['pay_mode'] ?>

                                </strong>
                                <a href="purchase_return_all.php?id=<?= $bill['id'] ?>&supplier=<?= urlencode($bill['s_name']) ?>" class="btn btn-sm btn-danger">Return</a>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
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
                                            <td><?= $med['new_qty'] ?></td>
                                            <td><?= $med['amount'] ?></td>
                                        </tr>
                                        <?php endwhile; $med_stmt->close(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-warning mt-4">No purchase entries found for the selected date range.</div>
                <?php endif; $stmt->close(); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
