<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default today's date
$today = date('Y-m-d');

$medicines = [];

// If form submitted, filter by start & end date
if (isset($_POST['search'])) {
    $start_date = $_POST['start_date'] ?? $today;
    $end_date = $_POST['end_date'] ?? $today;
    $query = "SELECT * FROM s_medicin WHERE ex_date BETWEEN '$start_date' AND '$end_date' ORDER BY ex_date ASC";
} else {
    // By default show today's expiry
    $query = "SELECT * FROM s_medicin WHERE ex_date = '$today' ORDER BY ex_date ASC";
}

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $medicines[] = $row;
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
    <title>Expiry Medicine Report</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">
    <style>
        .card{
            top :10px;
           
        }

        .custom-card {
            width: 85%;
            max-width: 1000px;
            
            margin: auto;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            background-color: #ffffff;
            transition: all 0.3s ease;
            left :100px;
        }

      

        .card-header {
            background: linear-gradient(135deg, #17a2b8, #138496);
            padding: 20px;
            color: #fff;
            font-size: 1.6rem;
            border-radius: 20px 20px 0 0;
        }

        .table th {
            background-color: #343a40;
            color: white;
            font-size: 14px;
        }

        .table td {
            font-size: 14px;
        }

        .btn-secondary {
            background-color: #17a2b8;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #138496;
        }
        </style>
</head>
<body>
    
<div class="container-fluid">
    <div class="row">
        <?php include 'navbar4.php'; ?>


<div class="container my-4 d-flex justify-content-center">
    <div class="card custom-card">
        <div class="card-header text-center">
            <h2><i class="fas fa-clipboard-list me-2"></i> Sales Records by Date</h2>
        </div>

    <div class="card-body">

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-5">
            <label class="form-label">Start Date:</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo $_POST['start_date'] ?? $today; ?>">
        </div>
        <div class="col-md-5">
            <label class="form-label">End Date:</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo $_POST['end_date'] ?? $today; ?>">
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" name="search" class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <?php if (!empty($medicines)): ?>
        <div class="card-body">
            <div class="table-responsive mt-3">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                <tr>
                    <th>Product</th>
                    <th>MRP</th>
                    <th>Amount</th>
                    <th>Supplier</th>
                    
                    <th>Pay Mode</th>
                   
                    <th>Batch</th>
                    <th>Expiry Date</th>
                    <th>Purchase Date</th>
                    <th>Company</th>
                    <th>GST</th>
                    <th>New Qty</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($medicines as $medicine): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($medicine['product']); ?></td>
                        <td><?php echo htmlspecialchars($medicine['mrp']); ?></td>
                        <td><?php echo htmlspecialchars($medicine['amount']); ?></td>
                        <td><?php echo htmlspecialchars($medicine['s_name']); ?></td>
                     
                        <td><?php echo htmlspecialchars($medicine['pay_mode']); ?></td>
                  
                        <td><?php echo htmlspecialchars($medicine['batch']); ?></td>
                        <td><?php echo htmlspecialchars($medicine['ex_date']); ?></td>
                        <td><?php echo htmlspecialchars($medicine['datetime']); ?></td>
                        <td><?php echo htmlspecialchars($medicine['company']); ?></td>
                        <td><?php echo htmlspecialchars($medicine['gst']); ?></td>
                        <td><?php echo htmlspecialchars($medicine['new_qty']); ?></td>
                        <td>
    <a href="expiry_add.php?id=<?php echo $medicine['id']; ?>" class="btn btn-sm btn-success">
        <i class="bi bi-plus-circle"></i> Add
    </a>
</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">No expiry medicines found <?php echo isset($_POST['search']) ? 'between selected dates.' : 'today.'; ?></div>
    <?php endif; ?>
</div>
    </div>
    </div>
    </div>
    </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
