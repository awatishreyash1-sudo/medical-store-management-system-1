<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$database = 'medical_shop';

$con = new mysqli($host, $user, $pass, $database);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Initialize variables
$product = $qty = $mrp = $batch = $ex_date = $pay_mode = $available_stock = $gst = $return_qty_db = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch sale data
if ($id > 0) {
    $stmt = $con->prepare("
        SELECT sm.qty1, sm.return_qty, sm.mrp, sm.batch, sm.ex_date, sm.pay_mode, sm.gst,
               ssm.id AS product, ssm.new_qty AS available_stock, ssm.product AS medicine_name
        FROM sales_medicin sm
        JOIN ss_medicine ssm ON sm.product = ssm.id
        WHERE sm.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $product = $row['product'];
        $medicine_name = $row['medicine_name'];
        $qty = $row['qty1'];
        $return_qty_db = $row['return_qty'];
        $mrp = $row['mrp'];
        $batch = $row['batch'];
        $ex_date = $row['ex_date'];
        $pay_mode = $row['pay_mode'];
        $available_stock = $row['available_stock'];
        $gst = $row['gst'];
    }
    $stmt->close();
}

// On form submit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return_submit'])) {
    $return_qty = intval($_POST['return_qty']);
    $final_sold_qty = $qty - $return_qty_db;

    if ($return_qty <= 0 || $return_qty > $final_sold_qty) {
        echo "<script>alert('Invalid return quantity.');</script>";
    } else {
        $base_amount = $return_qty * $mrp;
        $gst_amount = $base_amount * $gst / 100;
        $return_amount = $base_amount + $gst_amount;

        // 1. Insert into sales_returns
        $stmt_ret = $con->prepare("
            INSERT INTO sales_returns (
                sales_medicin_id, medicine_id, return_qty, mrp, gst, 
                base_amount, gst_amount, return_amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_ret->bind_param(
            "iiiddddd",
            $id, $product, $return_qty, $mrp, $gst,
            $base_amount, $gst_amount, $return_amount
        );
        $stmt_ret->execute();
        $stmt_ret->close();

        // 2. Update s_medicin new_qty
        $stmt2 = $con->prepare("UPDATE ss_medicine SET new_qty = new_qty + ? WHERE id = ?");
        $stmt2->bind_param("ii", $return_qty, $product);
        $stmt2->execute();
        $stmt2->close();

        // 3. Update medicine_master stock
        $stmt3 = $con->prepare("UPDATE medicine_master SET stock = stock + ? WHERE medicine_name = ?");
        $stmt3->bind_param("is", $return_qty, $medicine_name);
        $stmt3->execute();
        $stmt3->close();

        // 4. Update sales_medicin return_qty
        $stmt4 = $con->prepare("UPDATE sales_medicin SET return_qty = return_qty + ? WHERE id = ?");
        $stmt4->bind_param("ii", $return_qty, $id);
        $stmt4->execute();
        $stmt4->close();

        echo "<script>alert('Sales return recorded successfully.');</script>";
        echo '<meta http-equiv="refresh" content="0; url=vsales_medi.php">';
    }
}

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Return</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <style>
        .card {
            left: 220px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar2.php'; ?>

        <div class="col-md-10 d-flex justify-content-center align-items-center mt-4 mb-4" style="min-height: 85vh;">
            <div class="card shadow-lg rounded-4" style="width: 500px;">
                <div class="card-header bg-danger text-white text-center rounded-top-4 py-3">
                    <h3 class="mb-0 fw-bold"><i class="bi bi-arrow-counterclockwise"></i> Sales Return Entry</h3>
                </div>

                <div class="card-body">
                   <form method="POST">
    <input type="hidden" name="id" value="<?php echo $id; ?>">

    <div class="mb-3">
        <label class="fw-semibold">Medicine Name</label>
        <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($medicine_name); ?>" disabled>
    </div>

    <div class="row g-2">
        <div class="col-md-4">
            <label class="fw-semibold">Sold Qty</label>
            <input type="text" id="sold_qty" class="form-control form-control-sm bg-light" value="<?php echo htmlspecialchars($qty); ?>" readonly>
        </div>

        <div class="col-md-4">
            <label class="fw-semibold">Already Returned</label>
            <input type="text" id="already_returned" class="form-control form-control-sm bg-light" value="<?php echo htmlspecialchars($return_qty_db); ?>" readonly>
        </div>

        <div class="col-md-4">
            <label class="fw-semibold">Returnable Qty</label>
            <input type="text" id="returnable_qty" class="form-control form-control-sm bg-light" value="<?php echo ($qty - $return_qty_db); ?>" readonly>
        </div>

        <div class="col-md-6">
            <label class="fw-semibold">Return Qty</label>
            <input type="number" name="return_qty" class="form-control form-control-sm" required min="1" max="<?php echo ($qty - $return_qty_db); ?>">
        </div>

        <div class="col-md-6">
            <label class="fw-semibold">MRP</label>
            <input type="text" class="form-control form-control-sm" name="mrp" value="<?php echo htmlspecialchars($mrp); ?>" readonly>
        </div>

        <div class="col-md-6">
            <label class="fw-semibold">GST (%)</label>
            <input type="text" class="form-control form-control-sm" name="gst" value="<?php echo htmlspecialchars($gst); ?>" readonly>
        </div>

        <div class="col-md-6">
            <label class="fw-semibold">Batch</label>
            <input type="text" class="form-control form-control-sm" name="batch" value="<?php echo htmlspecialchars($batch); ?>" readonly>
        </div>

        <div class="col-md-6">
            <label class="fw-semibold">Expiry Date</label>
            <input type="date" class="form-control form-control-sm" name="ex_date" value="<?php echo htmlspecialchars($ex_date); ?>" readonly>
        </div>

        <div class="col-md-6">
            <label class="fw-semibold">Payment Mode</label>
            <input type="text" class="form-control form-control-sm" name="pay_mode" value="<?php echo htmlspecialchars($pay_mode); ?>" readonly>
        </div>
    </div>

    <div class="text-center mt-3">
        <button type="submit" name="return_submit" class="btn btn-danger">Submit Return</button>
        <a href="vsales_medi.php" class="btn btn-secondary">Back</a>
    </div>
</form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function updateTotals() {
        let qty = parseFloat($("#qty").val()) || 0;
        let mrp = parseFloat($("#mrp").val()) || 0;
        let gst = parseFloat($("input[name='gst']").val()) || 0;

        let amount = qty * mrp;
        let gstAmount = amount * gst / 100;
        let total = amount + gstAmount;

        $("#total_amount").val(total.toFixed(2));
        $("#f_total").val(total.toFixed(2));
    }

    $("#qty, #mrp, input[name='gst']").on("input", updateTotals);
    updateTotals();
});
</script>

</body>
</html>
