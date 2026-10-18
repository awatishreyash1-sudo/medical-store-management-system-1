<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = intval($_GET['id'] ?? 0);

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_name = $_POST['supplier_name'];
    $company_name = $_POST['company_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $gst_number = $_POST['gst_number'];

    $stmt = $conn->prepare("UPDATE supplier_master SET supplier_name=?, company_name=?, contact_number=?, email=?, address=?, gst_number=? WHERE id=?");
    $stmt->bind_param("ssssssi", $supplier_name, $company_name, $contact_number, $email, $address, $gst_number, $id);
    $stmt->execute();
    header("Location: view_supplier.php");
    exit;
}

session_start(); // <-- REQUIRED to use $_SESSION

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}


$conn = new mysqli("localhost", "root", "", "medical_shop");


// Fetch existing supplier
$result = $conn->query("SELECT * FROM supplier_master WHERE id = $id");
$supplier = $result->fetch_assoc();
if (!$supplier) die("Supplier not found.");
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
            background: #f4f6f9;
        }
        .card {
            max-width: 500px;
            width: 100%;
            margin: auto;
            margin-top: 50px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            left: 100px;
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
        <?php include 'navbar6.php'; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person-lines-fill me-2"></i> Edit Supplier
                    </div>

                    <div class="card-body">
                        
    <form method="POST" class="p-4 border rounded bg-white"
      onsubmit="return validateForm()">

    <!-- Supplier Name -->
    <div class="mb-3">
        <label class="form-label">Supplier Name</label>
        <input type="text"
               name="supplier_name"
               id="supplier_name"
               class="form-control"
               value="<?= htmlspecialchars($supplier['supplier_name']) ?>"
               required
               minlength="3"
               pattern="[A-Za-z ]+"
               title="Only letters allowed">
    </div>

    <!-- Company Name -->
    <div class="mb-3">
        <label class="form-label">Company Name</label>
        <input type="text"
               name="company_name"
               id="company_name"
               class="form-control"
               value="<?= htmlspecialchars($supplier['company_name']) ?>"
               required
               minlength="2">
    </div>

    <!-- Contact Number -->
    <div class="mb-3">
        <label class="form-label">Contact Number</label>
        <input type="text"
               name="contact_number"
               id="contact_number"
               class="form-control"
               value="<?= htmlspecialchars($supplier['contact_number']) ?>"
               required
               maxlength="10"
               pattern="[0-9]{10}"
               title="Enter 10 digit mobile number">
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email"
               name="email"
               id="email"
               class="form-control"
               value="<?= htmlspecialchars($supplier['email']) ?>">
    </div>

    <!-- Address -->
    <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address"
                  id="address"
                  class="form-control"
                  required
                  minlength="5"><?= htmlspecialchars($supplier['address']) ?></textarea>
    </div>

    <!-- GST Number -->
    <div class="mb-3">
        <label class="form-label">GST Number</label>
        <input type="text"
               name="gst_number"
               id="gst_number"
               class="form-control"
               value="<?= htmlspecialchars($supplier['gst_number']) ?>"
               maxlength="15"
               pattern="[0-9A-Z]{15}"
               title="Enter valid 15 character GST number">
    </div>

    <button type="submit"
            class="btn btn-success">
        Update Supplier
    </button>

    <a href="view_supplier.php"
       class="btn btn-secondary">
        Cancel
    </a>

</form>

<script>

function validateForm() {

    let supplierName =
        document.getElementById("supplier_name").value.trim();

    let companyName =
        document.getElementById("company_name").value.trim();

    let contact =
        document.getElementById("contact_number").value.trim();

    let address =
        document.getElementById("address").value.trim();

    let gst =
        document.getElementById("gst_number").value.trim();

    // Supplier Name validation
    if (supplierName.length < 3) {
        alert("Supplier name must be at least 3 characters");
        return false;
    }

    // Company Name validation
    if (companyName.length < 2) {
        alert("Company name must be at least 2 characters");
        return false;
    }

    // Contact validation
    let mobilePattern = /^[0-9]{10}$/;

    if (!mobilePattern.test(contact)) {
        alert("Contact number must be exactly 10 digits");
        return false;
    }

    // Address validation
    if (address.length < 5) {
        alert("Address must be at least 5 characters");
        return false;
    }

    // GST validation (optional but if filled must be 15 chars)
    if (gst !== "" && gst.length != 15) {
        alert("GST number must be 15 characters");
        return false;
    }

    return true;
}

</script>
</div>
</div>
</div>
</div>
</body>
</html>
