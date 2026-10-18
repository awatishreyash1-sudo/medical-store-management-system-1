<?php
include '../connection.php';

$id = intval($_GET['id']);
$message = '';

$medicine = $conn->query("SELECT * FROM medicine_master WHERE id = $id")->fetch_assoc();

if (isset($_POST['update_master'])) {
    $medicine_name = trim($_POST['medicine_name']);
    $company_name = trim($_POST['company_name']);
    $stock = intval($_POST['stock']);

    $update = $conn->query("UPDATE medicine_master SET 
        medicine_name = '$medicine_name',
        company_name  = '$company_name',
        stock         = $stock
        WHERE id = $id");

    $message = $update
        ? "<div class='alert alert-success text-center'>Medicine Updated Successfully.</div>" 
        : "<div class='alert alert-danger text-center'>Error Updating Medicine.</div>";

    header("Refresh:1; url=medicine_master_view.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Update Medicine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="image/logo.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="../nav.css">
    <style>
        body {
            background: #eaf4ff;
        }

        .card {
            left :230px;
            width: 480px;
        }

        .card-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }

        .btn-secondary:hover {
            background-color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar3.php'; ?>

        <div class="col-md-10 d-flex justify-content-center align-items-center" style="min-height: 90vh;">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header py-3">
                    <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Update Medicine</h4>
                </div>

                <div class="card-body bg-white rounded-bottom-4">
                    <?= $message ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Medicine Name</label>
                            <input type="text" name="medicine_name" value="<?= htmlspecialchars($medicine['medicine_name']) ?>" class="form-control form-control-sm" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" value="<?= htmlspecialchars($medicine['company_name']) ?>" class="form-control form-control-sm">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Available Stock</label>
                            <input type="number" name="stock" value="<?= $medicine['stock'] ?>" class="form-control form-control-sm" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="update_master" class="btn btn-primary">
                                <i class="bi bi-check-circle-fill me-1"></i> Update
                            </button>
                            <a href="medicine_master_view.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left-circle me-1"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
