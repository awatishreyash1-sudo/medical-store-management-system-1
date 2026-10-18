<?php
session_start(); // <-- REQUIRED to use $_SESSION

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

include 'add_medi.php';
$conn = new mysqli("localhost", "root", "", "medical_shop");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medicine Purchase Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link rel="icon" href="image/logo.jpeg">

    <style>
        body {
            background-color: #f0f4fa;
        }
        .card {
            max-width: 500px;
            margin: auto;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border: none;
            left :100px;
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
        <?php include 'navbar.php'; ?>
        <div class="col-md-10 offset-md-1 d-flex justify-content-center align-items-center" style="min-height: 90vh;">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Medicine Purchase Entry</h4>
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
    <label for="sales_pass" class="form-label">Salesman Password</label>
    <input type="password" name="sales_pass" class="form-control" required>
</div>

                        <!-- Supplier Name -->
                        <div class="mb-3 position-relative">
                            <label class="form-label">Supplier Name</label>
                            <input type="text" id="supplierInput" name="s_name" class="form-control" placeholder="Type supplier name..." required>
                            <div id="supplierList" class="suggestion-box d-none"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" id="companyInput" name="company" class="form-control" readonly>
                        </div>

                        <!-- Product Name -->
                        <div class="mb-3 position-relative">
                            <label class="form-label">Product Name</label>
                            <input type="text" id="productInput" name="product" class="form-control" placeholder="Type product name..." required>
                            <div id="productList" class="suggestion-box d-none"></div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">MRP</label>
                                <input type="number" name="mrp" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST (%)</label>
                                <input type="number" name="gst" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="qty" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Mode</label>
                                <select name="pay_mode" class="form-select">
                                    <option value="">Select Mode</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Credit">Credit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Batch No</label>
                                <input type="text" name="batch" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" name="ex_date" class="form-control">
                            </div>
                        </div>

                        <div class="mt-3 d-grid gap-2">
                            <button type="submit" name="add_medicine" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Add Purchase
                            </button>
                            <a href="purchase_view.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left-circle"></i> Back
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
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

    $(document).on('click', '.supplier-item', function () {
        const name = $(this).text();
        const company = $(this).data('company');
        $('#supplierInput').val(name);
        $('#companyInput').val(company);
        $('#supplierList').addClass('d-none');
    });

    // Product live search
    $('#productInput').on('keyup', function () {
        let query = $(this).val();
        if (query.length > 0) {
            $.post('search_product.php', { query: query }, function (data) {
                $('#productList').html(data).removeClass('d-none');
            });
        } else {
            $('#productList').addClass('d-none');
        }
    });

    $(document).on('click', '.product-item', function () {
        const name = $(this).text();
        $('#productInput').val(name);
        $('#productList').addClass('d-none');
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
</body>
</html>
