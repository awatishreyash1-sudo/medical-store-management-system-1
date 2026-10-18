<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$code = $_GET['code'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];

    $update = "UPDATE salesman SET name='$name', address='$address', mobile='$mobile' WHERE code='$code'";
    if ($conn->query($update)) {
        header("Location: view_salesman.php");
        exit();
    } else {
        echo "Update failed: " . $conn->error;
    }
} else {
    $result = $conn->query("SELECT * FROM salesman WHERE code='$code'");
    $salesman = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salesman Master</title>

    <!-- Select2 CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">

    <style>
         body {
            background: #f4f6f9;
        }
        .card {
            max-width: 500px;
            width: 100%;
            margin: auto;
            margin-top: 50px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            border-radius: 15px 15px 0 0;
            text-align: center;
            padding: 20px;
        }

    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'navbar5.php'; ?>

<div class="col-md-10 offset-md-1 d-flex justify-content-center align-items-center" style="min-height: 90vh;">
            <div class="card bg-white">
                <div class="card-header">
                    <h3 class="mb-0"><i class="bi bi-plus-circle-dotted"></i> Edit Sales Man</h3>
                </div>
                <div class="card-body p-4">
    <form method="POST" onsubmit="return validateForm()">

    <!-- Code -->
    <div class="mb-3">
        <label class="form-label">Code</label>
        <input type="text"
               class="form-control"
               value="<?= $salesman['code'] ?>"
               disabled>
    </div>

    <!-- Name -->
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text"
               name="name"
               id="name"
               class="form-control"
               value="<?= $salesman['name'] ?>"
               required
               minlength="3"
               pattern="[A-Za-z ]+"
               title="Only letters allowed">
    </div>

    <!-- Address -->
    <div class="mb-3">
        <label class="form-label">Address</label>
        <input type="text"
               name="address"
               id="address"
               class="form-control"
               value="<?= $salesman['address'] ?>"
               required
               minlength="5">
    </div>

    <!-- Mobile -->
    <div class="mb-3">
        <label class="form-label">Mobile</label>
        <input type="text"
               name="mobile"
               id="mobile"
               class="form-control"
               value="<?= $salesman['mobile'] ?>"
               required
               maxlength="10"
               pattern="[0-9]{10}"
               title="Enter 10 digit mobile number">
    </div>

    <button type="submit" class="btn btn-primary">
        Update
    </button>

    <a href="view_salesman.php" class="btn btn-secondary">
        Cancel
    </a>

</form>

<script>

function validateForm() {

    let name = document.getElementById("name").value.trim();
    let address = document.getElementById("address").value.trim();
    let mobile = document.getElementById("mobile").value.trim();

    // Name validation
    if (name.length < 3) {
        alert("Name must be at least 3 characters");
        return false;
    }

    // Only letters check
    let namePattern = /^[A-Za-z ]+$/;

    if (!namePattern.test(name)) {
        alert("Name should contain only letters");
        return false;
    }

    // Address validation
    if (address.length < 5) {
        alert("Address must be at least 5 characters");
        return false;
    }

    // Mobile validation
    let mobilePattern = /^[0-9]{10}$/;

    if (!mobilePattern.test(mobile)) {
        alert("Mobile number must be exactly 10 digits");
        return false;
    }

    return true;
}

</script>
</div>

</body>
</html>
