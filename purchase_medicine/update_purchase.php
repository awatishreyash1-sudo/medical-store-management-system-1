<?php
session_start();

// ✅ Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// ✅ Database connection
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ DELETE Logic
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $deleteQuery = "DELETE FROM s_medicin WHERE id = $id";
    if ($conn->query($deleteQuery) === TRUE) {
        echo "<script>alert('Record deleted successfully'); window.location.href='update_purchase.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "');</script>";
    }
}

// ✅ Date filter logic
$start_date = '';
$end_date = '';
$where_clause = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    if (!empty($start_date) && !empty($end_date)) {
        $where_clause = "WHERE DATE(datetime) BETWEEN '$start_date' AND '$end_date'";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update/Delete Purchase</title>
    <link rel="icon" href="image/logo.jpeg" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <style>
        body { background-color: #f0f4fa; }
        .custom-card {
            max-width: 1000px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            margin-top: 10px;
            overflow: hidden;
            left: 120px;
        }
        .card-header {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: #fff;
            padding: 25px;
            text-align: center;
            border-radius: 20px 20px 0 0;
        }
        .table thead {
            background-color: #138496;
            color: white;
        }
        .table td, .table th {
            font-size: 14px;
            vertical-align: middle;
        }
        .btn-success { border-radius: 8px; background-color: #28a745; }
        .btn-success:hover { background-color: #218838; }
        .btn-warning, .btn-danger {
            border-radius: 6px;
            font-size: 13px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar.php'; ?>

        <div class="d-flex justify-content-center">
            <div class="card custom-card w-100">
                <div class="card-header">
                    <h3><i class="bi bi-pencil-square me-2"></i> Update / Delete Purchase Records</h3>
                </div>

                <!-- 🔽 Date Filter Form -->
                <div class="card-body">
                    <form method="POST" class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label">Start Date:</label>
                            <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">End Date:</label>
                            <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Generate</button>
                        </div>
                    </form>
                </div>

                <!-- 🔽 Purchase Table -->
                <div class="card-body p-4 table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Batch</th>
                                <th>Qty</th>
                                <th>New Qty</th>
                                <th>MRP</th>
                                <th>Amount</th>
                                <th>Expiry</th>
                                <th>Supplier</th>
                                <th>Salesman</th>
                                <th>Pay Mode</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = "SELECT * FROM s_medicin $where_clause ORDER BY id DESC";
                        $result = $conn->query($query);
                        $sn = 1;
                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $sn++ ?></td>
                                <td><?= htmlspecialchars($row['product']) ?></td>
                                <td><?= htmlspecialchars($row['batch']) ?></td>
                                <td><?= $row['qty'] ?></td>
                                <td><?= $row['new_qty'] ?></td>
                                <td>₹<?= number_format($row['mrp'], 2) ?></td>
                                <td>₹<?= number_format($row['amount'], 2) ?></td>
                                <td><?= $row['ex_date'] ?></td>
                                <td><?= htmlspecialchars($row['s_name']) ?></td>
                                <td><?= htmlspecialchars($row['salesman_name'] ?? '-') ?></td>
                                <td><?= $row['pay_mode'] ?></td>
                                <td><?= date('d-m-Y', strtotime($row['datetime'] ?? '')) ?></td>
                                <td class="action-buttons">
                                    <a href="purchase_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="update_purchase.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this record?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>

                    <a href="add_medicine.php" class="btn btn-success mt-3">
                        <i class="bi bi-plus-circle"></i> Add New Purchase
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
