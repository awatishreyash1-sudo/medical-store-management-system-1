<?php
session_start();
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Expired Medicines Search</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../nav.css">

<style>
body{
    background: linear-gradient(to right,#eef2f3,#dfe9f3);
    font-family:'Segoe UI',sans-serif;
}
.main-card{
    width:75%;
    margin:30px auto;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
    background:#fff;
    padding:25px;
    margin-left:250px;
}
.table th{
    background:#1e293b;
    color:#fff;
}
.exp-date{
    color:#dc3545;
    font-weight:600;
}
</style>
</head>
<body>

<div class="container-fluid">
<div class="row">
<?php include 'navbar4.php'; ?>

<div class="main-card">

<h3 class="text-center mb-4">
<i class="bi bi-search text-primary"></i>
Search Expired Medicines (Date Wise)
</h3>

<!-- ================= SEARCH FORM ================= -->
<form method="POST" class="row g-3 mb-4 justify-content-center">
    <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control"
               value="<?= htmlspecialchars($start_date) ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control"
               value="<?= htmlspecialchars($end_date) ?>" required>
    </div>

    <div class="col-md-2 align-self-end">
        <button type="submit" name="search" class="btn btn-primary w-100">
            <i class="bi bi-search"></i> Search
        </button>
    </div>
</form>

<?php
/* ================= SHOW RECORD ONLY AFTER SEARCH ================= */
if (isset($_POST['search'])) {

    if ($start_date > $end_date) {
        echo "<div class='alert alert-danger text-center'>
                Start Date cannot be greater than End Date
              </div>";
    } else {

        // Supplier list based on date range
        $stmt_supplier = $conn->prepare("
            SELECT DISTINCT s_name, city
            FROM expiry_medicin
            WHERE DATE(datetime) BETWEEN ? AND ?
            ORDER BY s_name ASC
        ");
        $stmt_supplier->bind_param("ss", $start_date, $end_date);
        $stmt_supplier->execute();
        $supplier_result = $stmt_supplier->get_result();

        if ($supplier_result->num_rows > 0) {

            while ($supplier = $supplier_result->fetch_assoc()) {

                $supplier_name = $supplier['s_name'];
                $supplier_city = $supplier['city'];

                echo "<h5 class='mt-4 text-primary'>
                        <i class='bi bi-building'></i>
                        {$supplier_name}
                        <span class='text-muted'>({$supplier_city})</span>
                      </h5>";

                $stmt_medicine = $conn->prepare("
                    SELECT * FROM expiry_medicin
                    WHERE s_name = ?
                    AND DATE(datetime) BETWEEN ? AND ?
                    ORDER BY datetime DESC
                ");

                $stmt_medicine->bind_param("sss", $supplier_name, $start_date, $end_date);
                $stmt_medicine->execute();
                $medicines = $stmt_medicine->get_result();

                if ($medicines->num_rows > 0) {

                    echo "<div class='table-responsive'>
                            <table class='table table-bordered table-hover text-center'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th>Expiry</th>
                                    <th>Qty</th>
                                    <th>MRP</th>
                                    <th>GST%</th>
                                    <th>Amount</th>
                                    <th>Entry Date</th>
                                </tr>
                            </thead>
                            <tbody>";

                    while ($row = $medicines->fetch_assoc()) {

                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>".htmlspecialchars($row['product'])."</td>
                                <td>".htmlspecialchars($row['batch'])."</td>
                                <td class='exp-date'>{$row['ex_date']}</td>
                                <td>{$row['new_qty']}</td>
                                <td>{$row['mrp']}</td>
                                <td>{$row['gst']}%</td>
                                <td>{$row['amount']}</td>
                                <td>{$row['datetime']}</td>
                              </tr>";
                    }

                    echo "</tbody></table></div>";

                } else {
                    echo "<p class='text-muted'>No records found.</p>";
                }

                $stmt_medicine->close();
            }

        } else {
            echo "<div class='alert alert-warning text-center'>
                    No records found between selected dates.
                  </div>";
        }

        $stmt_supplier->close();
    }
}
?>

</div>
</div>
</div>

</body>
</html>