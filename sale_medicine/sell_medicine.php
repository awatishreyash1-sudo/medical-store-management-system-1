<?php
session_start(); // <-- REQUIRED to use $_SESSION

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

include 'sales_process.php';
$conn = new mysqli("localhost", "root", "", "medical_shop");
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
        .card{
            left :120px;
        }

        </style>
    
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'navbar2.php'; ?>

<div class="container-fluid">
    <div class="row justify-content-center mt-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow rounded-4">
                <div class="card-header bg-info text-white text-center rounded-top-4 py-3">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> Medicine Sales Entry</h4>
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
                            <div class="col-md-6">
                                <label class="fw-semibold">Salesman Password</label>
                                <input type="password" class="form-control form-control-sm" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-semibold">Patient Name</label>
                                <input type="text" name="patient_name" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-semibold">City</label>
                                <input type="text" name="city" class="form-control form-control-sm" required>
                            </div>
                           <div class="col-md-6">
    <label class="fw-semibold">Mobile</label>
    <input type="text"
           name="mobile"
           id="mobileInput"
           class="form-control form-control-sm"
           maxlength="10"
           pattern="[0-9]{10}"
           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
           title="Please enter a valid 10-digit mobile number"
           required>
</div>
                            <div class="col-md-6">
                                <label class="fw-semibold">Payment Mode</label>
                                <select name="pay_mode" class="form-select form-select-sm" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Card">Card</option>
                                    <option value="UPI">UPI</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="bg-info text-white rounded-3 px-3 py-2 text-center mb-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-capsule-pill"></i> Medicine List</h5>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered table-hover" id="medicine_table">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>Sr No.</th>
                                        <th>Medicine</th>
                                        <th>Batch</th>
                                        <th>GST (%)</th>
                                        <th>MRP</th>
                                        <th>Expiry</th>
                                        <th>Qty</th>
                                        <th>Amount</th>
                                        <th>Stock</th>
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
                            <div class="col-md-4">
                                <label class="fw-semibold">Total Amount</label>
                                <input type="text" name="total_amount" id="total_amount" class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-semibold">Discount (%)</label>
                                <input type="text" name="discount" id="dics" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
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

<script src="sale_medicine_script.js"></script>


</script>
</body>
</html>
