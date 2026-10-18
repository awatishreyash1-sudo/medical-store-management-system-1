<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Delete supplier if delete_id is set
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM supplier_master WHERE id = $delete_id");
    header("Location: view_supplier.php"); // Prevent re-delete on refresh
    exit;
}

// Search logic
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $sql = "SELECT * FROM supplier_master WHERE supplier_name LIKE '%$search%' OR company_name LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM supplier_master";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border: none;
            left :150px;
            width: 1000px;
        }
        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            border-radius: 20px 20px 0 0;
        }
        .table thead {
            background-color: #17a2b8;
            color: white;
        }
        .search-box {
            max-width: 400px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'navbar6.php'; ?>
        <div class="col-md-10 offset-md-1 mt-5">
            <div class="card">
                <div class="card-header text-center">
                    <h3><i class="bi bi-card-list"></i> View Supplier</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4 d-flex justify-content-center">
                        <input type="text" name="search" class="form-control search-box me-2" placeholder="Search by Supplier or Company Name" value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-info"><i class="bi bi-search"></i> Search</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Supplier Name</th>
                                    <th>Company Name</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>GST Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    $count = 1;
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                            <td>{$count}</td>
                                            <td>{$row['supplier_name']}</td>
                                            <td>{$row['company_name']}</td>
                                            <td>{$row['contact_number']}</td>
                                            <td>{$row['email']}</td>
                                            <td>{$row['address']}</td>
                                            <td>{$row['gst_number']}</td>
                                            <td>
                                                <a href='update_supplier.php?id={$row['id']}' class='btn btn-sm btn-warning'><i class='bi bi-pencil'></i></a>
                                                <a href='view_supplier.php?delete_id={$row['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure to delete this supplier?');\"><i class='bi bi-trash'></i></a>
                                            </td>
                                        </tr>";
                                        $count++;
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>No suppliers found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div> <!-- card-body -->
            </div> <!-- card -->
        </div> <!-- col -->
    </div> <!-- row -->
</div> <!-- container-fluid -->
</body>
</html>
