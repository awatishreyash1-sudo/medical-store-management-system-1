<?php
session_start(); // Always start session FIRST

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection (only once)
include '../connection.php'; // This should define $conn

// Search Logic
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM medicine_master";
if (!empty($search)) {
    // Use prepared statements (optional but safer)
    $query .= " WHERE medicine_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$result = $conn->query($query);
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medicine Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link rel="icon" href="image/logo.jpeg" type="image/jpeg">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            margin-top: 30px;
            width :90%;
            left :160px;
        }
        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px;
        }
        .btn-sm {
            border-radius: 10px;
        }
        .table th, .table td {
            vertical-align: middle !important;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar3.php'; ?>

        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0"><i class="bi bi-capsule"></i> Medicine List</h4>
                </div>

                <div class="card-body">

                    <!-- Search + Action Buttons -->
                    <form class="row g-2 justify-content-between mb-3" method="GET">
                        <div class="col-md-4">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control form-control-sm" placeholder="Search Medicine...">
                        </div>
                        <div class="col-md-8 text-end">
                            <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Search</button>
                            <a href="medicine_master_view.php" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                            <a href="add_medicine_master.php" class="btn btn-sm btn-success"><i class="bi bi-plus-circle-fill"></i> Add New</a>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Medicine Name</th>
                                    <th>Company</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($row['medicine_name']) ?></td>
                                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                                        <td><?= $row['stock'] ?></td>
                                        <td>
                                            <a href="medicine_master_update.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-1" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="medicine_master_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this medicine?')" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
