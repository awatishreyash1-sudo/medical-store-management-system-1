<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all expiry medicines
$result = $conn->query("SELECT * FROM expiry_medicin ORDER BY datetime DESC");

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
    <title>Expired Medicines List</title>
     <link rel="icon" type="image/jpeg" href="image/logo.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">

     <style>
       
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
            <h2><i class="fas fa-clipboard-list me-2"></i> Expiry Medicine View</h2>
        </div>

        <div class="card-body">

     <div class="table-responsive mt-3">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>MRP</th>
                <th>Amount</th>
                <th>Supplier</th>
                <th>City</th>
                <th>Pay Mode</th>
                <th>Sales Pass</th>
                <th>Batch No</th>
                <th>Expiry Date</th>
                <th>Entry Date</th>
                <th>Company</th>
                <th>GST</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['product']); ?></td>
                        <td><?php echo $row['mrp']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td><?php echo htmlspecialchars($row['s_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['city']); ?></td>
                        <td><?php echo htmlspecialchars($row['pay_mode']); ?></td>
                        <td><?php echo htmlspecialchars($row['sales_pass']); ?></td>
                        <td><?php echo htmlspecialchars($row['batch']); ?></td>
                        <td><?php echo $row['ex_date']; ?></td>
                        <td><?php echo $row['datetime']; ?></td>
                        <td><?php echo htmlspecialchars($row['company']); ?></td>
                        <td><?php echo $row['gst']; ?></td>
                        <td><?php echo $row['new_qty']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="14" class="text-center text-danger">No expired medicines found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="expiry_add.php" class="btn btn-secondary">Back</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
