<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $sql = "SELECT * FROM salesman WHERE name LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM salesman";
}
$result = $conn->query($sql);

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
    <meta charset="UTF-8">
    <title>View Salesman</title>

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .card {
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            margin-top: 60px;
            left: 150px;
            width: 1000px;
        }

        .card-header {
            background: linear-gradient(to right, #17a2b8, #138496);
            color: white;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            font-size: 1.3rem;
            font-weight: 600;
            text-align: center;
            padding: 15px;
        }

        .search-form input {
            border-radius: 25px;
            padding-left: 20px;
        }

        .search-form button {
            border-radius: 25px;
        }

        table thead {
            background-color: #17a2b8;
            color: #fff;
        }

        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar5.php'; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person-lines-fill me-2"></i> View Salesman
                    </div>

                    <div class="card-body">
                        <!-- Search Form -->
                        <form method="GET" class="search-form d-flex justify-content-center mb-4">
                            <input type="text" name="search" class="form-control me-2" style="max-width: 400px;" placeholder="Search by Name" value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-info"><i class="bi bi-search"></i> Search</button>
                        </form>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Mobile</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>
                                                <td>{$row['code']}</td>
                                                <td>{$row['name']}</td>
                                                <td>{$row['address']}</td>
                                                <td>{$row['mobile']}</td>
                                                <td>
                                                    <a href='edit_salesman.php?code={$row['code']}' class='btn btn-sm btn-warning me-2'><i class='bi bi-pencil-square'></i></a>
                                                    <a href='delete_salesman.php?code={$row['code']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this salesman?');\"><i class='bi bi-trash'></i></a>
                                                </td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5'>No salesman found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div> <!-- End table -->
                    </div> <!-- End card-body -->
                </div> <!-- End card -->
            </div> <!-- End col -->
        </div> <!-- End row -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
