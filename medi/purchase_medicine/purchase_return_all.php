<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");

if (!isset($_GET['id'])) {
    die("Invalid Bill ID.");
}

$bill_id = intval($_GET['id']);
$success_message = '';
$error_message = '';

// Fetch bill info
$bill_result = $conn->query("SELECT * FROM ss_bills WHERE id = $bill_id");
if ($bill_result->num_rows === 0) {
    die("Bill not found.");
}
$bill = $bill_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        $products = $_POST['product'];
        $sr_nos = $_POST['sr_no'];
        $batches = $_POST['batch'];
        $gst_values = $_POST['gst'];
        $mrps = $_POST['mrp'];
        $expiry_dates = $_POST['ex_date'];
        $return_qtys = $_POST['return_qty'];
        $return_amounts = $_POST['return_amount'];

        foreach ($products as $index => $product) {
            $sr_no = intval($sr_nos[$index]);
            $batch = $conn->real_escape_string($batches[$index]);
            $gst = floatval($gst_values[$index]);
            $mrp = floatval($mrps[$index]);
            $ex_date = $conn->real_escape_string($expiry_dates[$index]);
            $return_qty = intval($return_qtys[$index]);
            $return_amount = floatval($return_amounts[$index]);

            if ($return_qty > 0) {
                // 1. Insert into return table with correct bind types
                $stmt = $conn->prepare("INSERT INTO purchase_return_medicine 
                    (bill_id, sr_no, product, batch, gst, mrp, ex_date, return_qty, return_amount) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissddsid", $bill_id, $sr_no, $product, $batch, $gst, $mrp, $ex_date, $return_qty, $return_amount);
                $stmt->execute();
                $stmt->close();

                // 2. Update stock in ss_medicine
                $update_ss = $conn->prepare("UPDATE ss_medicine 
                    SET new_qty = new_qty - ? 
                    WHERE bill_id = ? AND sr_no = ?");
                $update_ss->bind_param("iii", $return_qty, $bill_id, $sr_no);
                $update_ss->execute();
                $update_ss->close();

                // 3. Update stock in medicine_master
                $update_mm = $conn->prepare("UPDATE medicine_master SET stock = stock - ? WHERE medicine_name = ?");
                $update_mm->bind_param("is", $return_qty, $product);
                $update_mm->execute();
                $update_mm->close();
            }
        }

        $conn->commit();
        $success_message = "Return details saved and stock updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Failed to save return: " . $e->getMessage();
    }
}

// Fetch medicine rows for the bill
$meds = $conn->query("SELECT * FROM ss_medicine WHERE bill_id = $bill_id");
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Purchase Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../nav.css">
     <style>
         body {
            background-color: #f0f4fa;
        }
        .card {
            max-width: 1000px;
            width: 1000px;
            margin: auto;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border: none;
            left :30px;
        }
        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            padding: 20px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }
        .suggestion-box {
            position: absolute;
            background: #fff;
            z-index: 1000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
        }
        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background-color: #f0f0f0;
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
                    <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> Edit Purchase Bill #<?= $bill_id ?></h4>
             </div>

                 <div class="card-body">
    

                    <?php if ($success_message): ?>
                 <div class="alert alert-success"><?= $success_message ?></div>
                   <?php endif; ?>
                   <?php if ($error_message): ?>
                  <div class="alert alert-danger"><?= $error_message ?></div>
                   <?php endif; ?>

                 <form method="POST">
                  <div class="row mb-3 position-relative">
                    <div class="col-md-6">
                      <label>Supplier Name</label>
                      <input type="text" id="supplierInput" name="s_name" class="form-control" value="<?= $bill['s_name'] ?>" autocomplete="off" required>
                     <div id="supplierList" class="suggestion-box d-none"></div>
                    </div>
                  <div class="col-md-6">
                     <label>Address</label>
                     <input type="text" id="addressInput" name="address" class="form-control" value="<?= $bill['address'] ?>" required>
                 </div>
            <div class="col-md-6">
    <label>Mobile</label>
    <input type="text" id="mobileInput" name="mobile" class="form-control" 
           value="<?= $bill['mobile'] ?>" 
           pattern="\d{10}" maxlength="10"
           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
           title="Please enter a valid 10-digit mobile number" required>
</div>

            <div class="col-md-6">
                <label>Payment Mode</label>
                <input type="text" name="pay_mode" class="form-control" value="<?= $bill['pay_mode'] ?>" required>
            </div>
        </div>

        <hr class="my-4">

                        <div class="bg-info text-white rounded-3 px-3 py-2 text-center mb-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-capsule-pill"></i> Add Medicine List</h5>
                        </div>

        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered table-hover" id="medicine_table">
                                <thead class="table-light text-center">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Product</th>
                    <th>Batch</th>
                    <th>GST %</th>
                    <th>MRP</th>
                    <th>Expiry</th>
                    <th>Qty</th>
                    <th>Amount</th>
                    <th>Return Qty</th>
                    <th>Return Amount</th>


                </tr>
            </thead>
            <tbody>
                <?php while ($med = $meds->fetch_assoc()): ?>
                <tr>
                    <td><input type="number" name="sr_no[]" class="form-control" value="<?= $med['sr_no'] ?>" readonly></td>
    <td><input type="text" name="product[]" class="form-control" value="<?= $med['product'] ?>" readonly></td>
    <td><input type="text" name="batch[]" class="form-control" value="<?= $med['batch'] ?>" readonly></td>
    <td><input type="number" step="0.01" name="gst[]" class="form-control" value="<?= $med['gst'] ?>" readonly></td>
    <td><input type="number" step="0.01" name="mrp[]" class="form-control" value="<?= $med['mrp'] ?>" readonly></td>
    <td><input type="date" name="ex_date[]" class="form-control" value="<?= $med['ex_date'] ?>" readonly></td>
    <td><input type="number" class="form-control" value="<?= $med['new_qty'] ?>" readonly></td>
    <td><input type="number" step="0.01" class="form-control" value="<?= $med['amount'] ?>" readonly></td>
    <td><input type="number" name="return_qty[]" class="form-control return-qty" min="0" max="<?= $med['new_qty'] ?>" value="0"></td>
    <td><input type="text" name="return_amount[]" class="form-control return-amount" readonly></td>
</tr>
                <?php endwhile; ?>
            </tbody>
        </table>
                </div>
               

        <div class="row g-3">
            <div class="col-md-6">
                <label>Total Amount</label>
                <input type="text" name="total_amount" class="form-control" value="<?= $bill['total_amount'] ?>" readonly>
            </div>
            <div class="col-md-6">
                <label>Final Amount</label>
                <input type="text" name="f_total_amount" class="form-control" value="<?= $bill['f_total_amount'] ?>" readonly>
            </div>
        </div>


   
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Purches Return</button>
            <a href="purchase_return.php" class="btn btn-secondary">Back</a>
        </div>
         <div class="mt-4">
       
    </form>
</div>
                </div>
                </div>
                </div>
                </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    function calculateReturnAmount(row) {
        const mrp = parseFloat(row.find('[name="mrp[]"]').val()) || 0;
        const gst = parseFloat(row.find('[name="gst[]"]').val()) || 0;
        const return_qty = parseFloat(row.find('.return-qty').val()) || 0;
        const base = mrp * return_qty;
        const gst_amount = base * gst / 100;
        const total = base + gst_amount;
        row.find('.return-amount').val(total.toFixed(2));
    }

    $('tbody').on('input', '.return-qty', function () {
        const row = $(this).closest('tr');
        calculateReturnAmount(row);
    });
});
</script>
</body>
</html>