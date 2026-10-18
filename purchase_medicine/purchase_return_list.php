<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");

$supplier_filter = $_GET['supplier'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Build the SQL query dynamically based on filters
$sql = "SELECT b.s_name, b.mobile, b.address, r.product, r.batch, r.return_qty, r.return_amount, r.return_date FROM purchase_return_medicine r JOIN ss_bills b ON r.bill_id = b.id WHERE 1";

if (!empty($supplier_filter)) {
    $supplier_filter = $conn->real_escape_string($supplier_filter);
    $sql .= " AND b.s_name LIKE '%$supplier_filter%'";
}

if (!empty($start_date) && !empty($end_date)) {
    $start_date = $conn->real_escape_string($start_date);
    $end_date = $conn->real_escape_string($end_date);
    $sql .= " AND DATE(r.return_date) BETWEEN '$start_date' AND '$end_date'";
}

$sql .= " ORDER BY b.s_name, r.return_date DESC";

$result = $conn->query($sql);

// Group by supplier
$suppliers = [];
while ($row = $result->fetch_assoc()) {
    $key = $row['s_name'] . '_' . $row['mobile'] . '_' . $row['address'];
    $suppliers[$key][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Return List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../nav.css">
    <style>
        body { 
            background-color: #f8f9fa; 
        }
        .container { 
            margin-top: 20px; 
        }
        .card { 
            border-radius: 20px; 
            box-shadow: 0 6px 12px rgba(0,0,0,0.1); 
            max-width: 1000px; 
            width: 1000px;
            left:20px; 
        }
        .card-header { 
            background-color: #17a2b8; 
            color: white; 
            font-weight: bold; 
        }
        table th, table td { 
            vertical-align: middle; 
            text-align:center;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar.php'; ?>
        <div class="container mt-4 mb-4">

            <div class="row justify-content-center mt-4 mb-4">
                <div class="col-lg-8">
                    <div class="card shadow rounded-4">
                        <div class="card-header bg-info text-white text-center rounded-top-4 py-3">
                            <h4 class="mb-0 fw-bold"><i class="bi bi-arrow-left-right"></i> Purchase Return List</h4>
                        </div>
                        <div class="card-body">
                            <!-- Filter Form -->
                            <form method="GET" class="mb-4">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label for="supplier" class="form-label">Supplier Name</label>
                                        <input type="text" name="supplier" id="supplier" class="form-control" placeholder="Enter supplier name" value="<?= htmlspecialchars($supplier_filter) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="mt-5">

                    <?php if (empty($suppliers)): ?>
                        <div class="alert alert-info text-center mt-4">No return records found.</div>
                    <?php else: ?>
                        <?php foreach ($suppliers as $key => $returns): 
                            $first = $returns[0];
                        ?>
                        <div class="card shadow rounded-4 mb-4">
                            <div class="card-header bg-primary text-white rounded-top-4">
                                <h5 class="mb-0">
                                    Supplier: <?= htmlspecialchars($first['s_name']) ?> |
                                    Mobile: <?= htmlspecialchars($first['mobile']) ?> |
                                    City: <?= htmlspecialchars($first['address']) ?>
                                </h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>Medicine</th>
                                                <th>Batch</th>
                                                <th>Return Qty</th>
                                                <th>Return Amount</th>
                                                <th>Return Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($returns as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['product']) ?></td>
                                                <td><?= htmlspecialchars($row['batch']) ?></td>
                                                <td class="text-center"><?= $row['return_qty'] ?></td>
                                                <td class="text-center"><?= number_format($row['return_amount'], 2) ?></td>
                                                <td class="text-center"><?= date('d-m-Y', strtotime($row['return_date'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
