<?php
session_start();

if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medical_shop");

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check'])) {

    $pay_mode = $_POST['pay_mode'];
    $products = $_POST['product'] ?? [];
    $quantities = $_POST['qty1'] ?? [];
    $mrps = $_POST['mrp'] ?? [];
    $amounts = $_POST['amount'] ?? [];
    $batches = $_POST['batch'] ?? [];
    $gst = $_POST['gst'] ?? [];
    $expiry_dates = $_POST['ex_date'] ?? [];
    $sr_numbers = $_POST['sr_no'] ?? [];

    $s_name = $_POST['s_name'];
    $total_amount = floatval($_POST['total_amount']);
    $expiry_return_amount = floatval($_POST['expiry_return_total'] ?? 0);
    $f_total_amount = floatval($_POST['f_total_amount']);
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $password = $_POST['password'];

    $error = false;
    $salesman_name = '';
    $message = '';

    // 🔹 1. Verify Salesman
    $stmt = $conn->prepare("SELECT name FROM salesman WHERE password = ?");
    $stmt->bind_param("s", $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $salesman_name = $result->fetch_assoc()['name'];
    } else {
        $error = true;
        $error_message = "Invalid Salesman Password!";
    }
    $stmt->close();

    // 🔹 2. Insert Bill
    if (!$error) {

        $stmt = $conn->prepare(
            "INSERT INTO ss_bills 
            (s_name, total_amount, expiry_return_amount, salesman_name, f_total_amount, mobile, address, pay_mode)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sddsssss",
            $s_name,
            $total_amount,
            $expiry_return_amount,
            $salesman_name,
            $f_total_amount,
            $mobile,
            $address,
            $pay_mode
        );

        if ($stmt->execute()) {

            $bill_id = $stmt->insert_id;
            $stmt->close();

            // 🔹 3. Insert Medicines + Update Stock
            foreach ($products as $index => $product) {

                $sr_no = intval($sr_numbers[$index]);
                $qty = intval($quantities[$index]);
                $mrp = floatval($mrps[$index]);
                $amount = floatval($amounts[$index]);
                $batch = $batches[$index];
                $gst_val = $gst[$index];
                $ex_date = $expiry_dates[$index];

                // Insert Medicine
                $insert_stmt = $conn->prepare(
                    "INSERT INTO ss_medicine 
                    (bill_id, sr_no, product, qty1, new_qty, mrp, amount, batch, gst, ex_date, pay_mode)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $insert_stmt->bind_param(
                    "iisiddsssss",
                    $bill_id,
                    $sr_no,
                    $product,
                    $qty,
                    $qty,
                    $mrp,
                    $amount,
                    $batch,
                    $gst_val,
                    $ex_date,
                    $pay_mode
                );

                $insert_stmt->execute();
                $insert_stmt->close();

                // Update Stock
                $stock_check = $conn->prepare("SELECT stock FROM medicine_master WHERE medicine_name = ?");
                $stock_check->bind_param("s", $product);
                $stock_check->execute();
                $stock_check->bind_result($existing_stock);

                if ($stock_check->fetch()) {
                    $stock_check->close();

                    $new_stock = $existing_stock + $qty;

                    $update_stock = $conn->prepare("UPDATE medicine_master SET stock = ? WHERE medicine_name = ?");
                    $update_stock->bind_param("is", $new_stock, $product);
                    $update_stock->execute();
                    $update_stock->close();
                } else {
                    $stock_check->close();
                }
            }

            // 🔥 4. Expiry mark as USED (VERY IMPORTANT)
            if ($expiry_return_amount > 0) {

                $update_expiry = $conn->prepare(
                    "UPDATE expiry_medicin 
                     SET status = 'used' 
                     WHERE s_name = ? 
                     AND status = 'pending'"
                );

                $update_expiry->bind_param("s", $s_name);
                $update_expiry->execute();
                $update_expiry->close();
            }

            $success_message = "Purchase recorded successfully!";
        } else {
            $error_message = "Bill insert failed!";
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sell Medicine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../nav.css">
    <style>
         body {
            background-color: #f0f4fa;
        }
        .card {
            max-width: 1200px;
            width: 1030px;
            margin: auto;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border: none;
            left :10px;
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

.table-responsive { overflow: visible !important; } /* scrollbar remove */
.medicine_dropdown { position:absolute; top:100%; left:0; z-index:1050; width:100%; max-height:200px; overflow-y:auto; }



        

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
                    <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> Purchase Entry</h4>
                </div>

                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?= $success_message ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="fw-semibold">Salesman Password</label>
                                <input type="password" class="form-control form-control-sm" name="password" required>
                            </div>

                            <div class="col-md-6 position-relative">
                            <label class="fw-semibold">Supplier Name</label>
                            <input type="text" id="supplierInput" name="s_name" class="form-control form-control-sm" placeholder="Type supplier name..." required>
                            <div id="supplierList" class="suggestion-box d-none"></div>
                        </div>

                            <div class="col-md-6">
                                <label class="fw-semibold">Address</label>
                                <input type="text" id="addressInput" name="address" class="form-control form-control-sm" required readonly>
                            </div>
                            <div class="col-md-6">
    <label class="fw-semibold">Mobile</label>
    <input type="text" id="mobileInput" name="mobile" 
           class="form-control form-control-sm" 
           maxlength="10" 
           pattern="\d{10}" 
           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
           title="Please enter a valid 10-digit mobile number" 
           required readonly>
</div>

                            <div class="col-md-6">
                                <label class="fw-semibold">Payment Mode</label>
                                <select name="pay_mode" class="form-select form-select-sm" required>
                                    <option value="Cash">Cash</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Card">Card</option>
                                    
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="bg-info text-white rounded-3 px-3 py-2 text-center mb-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-capsule-pill"></i> Add Medicine List</h5>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered table-hover" id="medicine_table">
                                <thead class="table-light text-cenetr">
                                    <tr>
                                        <th width="60px">Sr No.</th>
                                        <th>Medicine</th>
                                        <th>Batch</th>
                                        <th width="60px">GST</th>
                                        <th width="60px">MRP</th>
                                        <th>Expiry</th>
                                        <th width="80px">Qty</th>
                                        <th width="100px">Amount</th>
                                       
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="text-end mb-4">
                            <button type="button" class="btn btn-primary btn-sm" onclick="addMedicineRow()">
                                <i class="bi bi-plus-circle"></i> Add Medicine
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="fw-semibold">Total Amount</label>
                                <input type="text" name="total_amount" id="total_amount" class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-semibold">Discount (%)</label>
                                <input type="text" name="discount" id="dics" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-3">
    <label class="fw-semibold text-danger">Expiry Return Amount</label>
    <input type="text" 
           name="expiry_return_total" 
           id="expiry_return_total" 
           class="form-control form-control-sm bg-light text-danger fw-bold" 
           readonly>
</div>

                            <div class="col-md-3">
                                <label class="fw-semibold">Final Total</label>
                                <input type="text" name="f_total_amount" id="final_total_amount" class="form-control form-control-sm" readonly>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="check" class="btn btn-success px-4">
                                <i class="bi bi-check-circle-fill"></i> Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<script src="purches.js"></script>

<script>
$(document).ready(function () {

    // 🔥 Common Final Calculation Function
    function calculateFinalTotal() {

        let total = parseFloat($('#total_amount').val()) || 0;
        let discount = parseFloat($('#dics').val()) || 0;
        let expiry = parseFloat($('#expiry_return_total').val()) || 0;

        let discountAmount = (total * discount) / 100;
        let final = total - discountAmount - expiry;

        $('#final_total_amount').val(final.toFixed(2));
    }

    // Supplier live search
    $('#supplierInput').on('keyup', function () {
        let query = $(this).val();
        if (query.length > 0) {
            $.post('search_supplier.php', { query: query }, function (data) {
                $('#supplierList').html(data).removeClass('d-none');
            });
        } else {
            $('#supplierList').addClass('d-none');
        }
    });

    // ✅ Supplier select (expiry set करायचा पण minus नाही)
    $(document).on('click', '.supplier-item', function () {

        const name = $(this).text();
        const address = $(this).data('address');
        const mobile = $(this).data('mobile');

        $('#supplierInput').val(name);
        $('#addressInput').val(address);
        $('#mobileInput').val(mobile);
        $('#supplierList').addClass('d-none');

        $.post('get_expiry_return_total.php', { supplier_name: name }, function (data) {

            $('#expiry_return_total').val(parseFloat(data) || 0);

            calculateFinalTotal(); // 🔥 आता proper calculation
        });
    });

    // 🔥 Total बदलला की final update
    $('#total_amount').on('input', function () {
        calculateFinalTotal();
    });

    // 🔥 Discount बदलला की final update
    $('#dics').on('input', function () {
        calculateFinalTotal();
    });

    // Hide on outside click
    $(document).click(function (e) {
        if (!$(e.target).closest('#supplierInput, #supplierList').length) {
            $('#supplierList').addClass('d-none');
        }
        if (!$(e.target).closest('#productInput, #productList').length) {
            $('#productList').addClass('d-none');
        }
    });

});
</script>