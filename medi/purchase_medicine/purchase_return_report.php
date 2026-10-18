<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$returns = [];
$formSubmitted = false;
$supplierSearch = '';
$totalAmount = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formSubmitted = true;
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $supplierSearch = isset($_POST['supplier_search']) ? trim($_POST['supplier_search']) : '';

    $sql = "SELECT 
                pr.*, 
                sm.product, 
                sm.s_name AS supplier_name, 
                sup.company_name AS company_name 
            FROM purchase_return pr
            JOIN s_medicin sm ON pr.medicine_id = sm.id
            LEFT JOIN supplier_master sup ON sm.s_name = sup.supplier_name
            WHERE DATE(pr.return_date) BETWEEN ? AND ?";

    if (!empty($supplierSearch)) {
        $sql .= " AND (sm.s_name LIKE ? OR sup.company_name LIKE ?)";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    if (!empty($supplierSearch)) {
        $searchTerm = "%" . $supplierSearch . "%";
        $stmt->bind_param("ssss", $start, $end, $searchTerm, $searchTerm);
    } else {
        $stmt->bind_param("ss", $start, $end);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $returns[] = $row;
        $totalAmount += $row['return_amount']; // accumulate total
    }
}

session_start(); // <-- REQUIRED to use $_SESSION

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}


$conn = new mysqli("localhost", "root", "", "medical_shop");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Return Report</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="../nav.css">

    <style>
       

        

        .card {
            bottom :120px;
            left: 220px;
            width: 900px;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            padding: 25px;
            color: #fff;
            text-align: center;
            border-radius: 20px 20px 0 0;
        }

        .card-body {
            padding: 25px;
            background-color: #fff;
        }

        .btn-primary {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-primary:hover {
            background-color: #138496;
        }

        h3 {
            margin-top: 20px;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 5px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar.php'; ?>

<div class="mt-5">
       <div class="col-md-10 d-flex justify-content-center align-items-center mt-4 mb-4" style="min-height: 85vh;">
            <div class="card shadow-lg rounded-4" style="width: 1000px;">
                <div class="card-header bg-info text-white text-center rounded-top-4 py-3">
                    <h3 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> Medicine Purchase Return Records</h3>
                </div>

                 
                    <div class="card-body">
                        
            <form method="POST" class="row g-3 mb-4 no-print">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="supplier_search" class="form-label">Search Supplier/Company</label>
                    <input type="text" id="supplier_search" name="supplier_search" value="<?= htmlspecialchars($supplierSearch) ?>" class="form-control" placeholder="Enter supplier or company name">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>

            <?php if (!empty($returns)): ?>
              
                <table class="table table-bordered">
                    <thead class="table-danger text-center">
                        <tr>
                            <th>#</th>
                            <th>Supplier</th>
                            <th>Company</th>
                            <th>Product</th>
                            <th>Return Qty</th>
                            <th>MRP</th>
                            <th>GST %</th>
                            <th>Base Amt</th>
                            <th>GST Amt</th>
                            <th>Total Return</th>
                            <th>Reason</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($returns as $row): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td><?= htmlspecialchars($row['company_name']) ?></td>
                                <td><?= htmlspecialchars($row['product']) ?></td>
                                <td><?= $row['return_qty'] ?></td>
                                <td>₹<?= number_format($row['mrp'], 2) ?></td>
                                <td><?= $row['gst'] ?>%</td>
                                <td>₹<?= number_format($row['base_amount'], 2) ?></td>
                                <td>₹<?= number_format($row['gst_amount'], 2) ?></td>
                                <td>₹<?= number_format($row['return_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($row['reason']) ?></td>
                                <td><?= date('d-m-Y', strtotime($row['return_date'])) ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>

                <div class="text-end mt-3">
                    <h5><strong>Total Return Amount: ₹<?= number_format($totalAmount, 2) ?></strong></h5>
                </div>
            <?php elseif ($formSubmitted): ?>
                <div class="alert alert-warning text-center">No return records found for given filters.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
            </div>
            </div>
            </div>
</body>
</html>
