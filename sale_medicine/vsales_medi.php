<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $sql = "SELECT sb.id AS bill_id, sb.patient_name, sb.total_amount, sb.f_total_amount, sb.salesman_name, sb.datetime 
                FROM sales_bills sb WHERE DATE(sb.datetime) BETWEEN ? AND ? ORDER BY sb.id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<table class="table table-bordered"><thead><tr><th>Bill ID</th><th>Date</th><th>Patient Name</th><th>Salesman</th><th>Total</th><th>Final</th><th>Action</th></tr></thead><tbody>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr><td>'.$row['bill_id'].'</td><td>'.$row['datetime'].'</td><td>'.$row['patient_name'].'</td><td>'.$row['salesman_name'].'</td><td>'.$row['total_amount'].'</td><td>'.$row['f_total_amount'].'</td><td><button class="btn btn-info btn-sm view-medicines" data-bill-id="'.$row['bill_id'].'">View Medicines</button>
                </td></tr>';
            }
            echo '</tbody></table>';
        } else { echo '<div class="alert alert-warning">No records found.</div>'; }
        $stmt->close(); exit;
    }

    if (isset($_POST['bill_id'])) {
        $bill_id = $_POST['bill_id'];
        $sql = "SELECT sm.id, sm.qty1, sm.mrp, sm.amount, sm.batch, sm.ex_date, sm.pay_mode, ssm.product AS medicine_name
                FROM sales_medicin sm JOIN ss_medicine ssm ON sm.product = ssm.id WHERE sm.bill_id = ? ORDER BY sm.id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $bill_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<table class="table table-bordered"><thead><tr><th>Medicine</th><th>Batch</th><th>MRP</th><th>Qty</th><th>Amount</th><th>Expiry</th><th>Pay Mode</th><th>Actions</th></tr></thead><tbody>';
            while ($med = $result->fetch_assoc()) {
                echo '<tr><td>'.$med['medicine_name'].'</td>
                <td>'.$med['batch'].'</td><td>'.$med['mrp'].'</td>
                <td>'.$med['qty1'].'</td><td>'.$med['amount'].'</td>
                <td>'.$med['ex_date'].'</td><td>'.$med['pay_mode'].'</td>
                <td><a href="update_sell.php?id='.$med['id'].'" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a> 
                <a href="sell_delete.php?id='.$med['id'].'" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')"><i class="bi bi-trash"></i></a>
                </td></tr>';
            }
            echo '</tbody></table>';
        } else { echo '<div class="alert alert-warning">No medicines found.</div>'; }
        $stmt->close(); exit;
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
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Medicine Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <link rel="stylesheet" href="../nav.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
         .card { 
            margin-top: 20px; 
            margin-right: 100px; 
            border-radius: 15px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
           left: 150px;
        }

        

        .card-header { background: linear-gradient(45deg, #17a2b8, #138496); 
            border-radius: 15px 15px 0 0; 
            color: #fff; text-align: center; 
        }
        .btn-primary { background-color: #17a2b8; 
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
        <?php include 'navbar2.php'; ?> <!-- Sidebar Include -->


         
   <div class="col-md-10 p-4">
            <div class="d-flex justify-content-end">
                <div class="card w-75">
                    <div class="card-header">
                        <h2>Sales Return</h2>
                    </div>
           <div class="card-body">
                 <form id="filter-form" class="row mb-3">
                    <!--1st table-->
                    <div class="col-md-5">
                        <label class="form-label">Start Date:</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">End Date:</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Enter</button>
                    </div>
                </form>

                    <div id="patient-list">
                        <h3>Patients List</h3>
                        <div id="patient-content"></div>
                    </div>

                    <div id="medicine-details" class="d-none">
                        <h3>Medicine Details</h3>
                        <button class="btn btn-secondary mb-3" id="back-to-patients">Back</button>
                        <div id="medicine-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $("#filter-form").submit(function (e) {
        e.preventDefault();
        $.post("", $(this).serialize(), function (data) {
            $("#patient-content").html(data);
        });
    });

    $(document).on("click", ".view-medicines", function () {
        $.post("", { bill_id: $(this).data("bill-id") }, function (data) {
            $("#medicine-content").html(data);
            $("#patient-list").addClass("d-none");
            $("#medicine-details").removeClass("d-none");
        });
    });

    $("#back-to-patients").click(function () {
        $("#medicine-details").addClass("d-none");
        $("#patient-list").removeClass("d-none");
    });
});
</script>
</body>
</html>
