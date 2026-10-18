<?php
session_start(); // <-- REQUIRED to use $_SESSION

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

include 'add_medicine_master1.php';
$conn = new mysqli("localhost", "root", "", "medical_shop");
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Medicine</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../nav.css">

    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            width: 100%;
            max-width: 480px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            margin: auto;
            left :120px;
        }

        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: #fff;
            padding: 20px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-success {
            border-radius: 10px;
        }

        .btn-secondary {
            border-radius: 10px;
        }

        label {
            font-weight: 500;
        }

       
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar3.php'; ?>

        <div class="col-md-10 offset-md-1 d-flex justify-content-center align-items-center" style="min-height: 90vh;">
            <div class="card bg-white">
                <div class="card-header">
                    <h3 class="mb-0"><i class="bi bi-plus-circle-dotted"></i> Add New Medicine</h3>
                </div>
                <div class="card-body p-4">

                    <!-- Alert Message -->
                    <?php echo $message; ?>

                    <!-- Add Medicine Form -->
                    <form method="POST">
                        <div class="mb-3">
                            <label for="medicine_name" class="form-label">Medicine Name <span class="text-danger">*</span></label>
                            <input type="text" name="medicine_name" class="form-control" id="medicine_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" id="company_name" required>
                        </div>

                       
                        <div class="mb-4">
                            <label for="stock" class="form-label">Opening Stock</label>
                            <input type="number" name="stock" class="form-control" id="stock" min="0" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="add_master" class="btn btn-success">
                                <i class="bi bi-plus-circle-fill"></i> Add Medicine
                            </button>
                            <a href="medicine_master_view.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left-circle"></i> Back to List
                            </a>
                        </div>
                    </form>

                </div>
            </div>  
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
