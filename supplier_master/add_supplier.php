<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_name = $_POST['supplier_name'];
    $company_name = $_POST['company_name'];
    $contact = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $gst = $_POST['gst_number'];

    if (!empty($supplier_name)) {
        $stmt = $conn->prepare("INSERT INTO supplier_master (supplier_name, company_name, contact_number, email, address, gst_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $supplier_name, $company_name, $contact, $email, $address, $gst);
        if ($stmt->execute()) {
            $success = "Supplier added successfully!";
        } else {
            $error = "Error adding supplier.";
        }
        $stmt->close();
    } else {
        $error = "Supplier name is required.";
    }
}

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
    <title>Add Supplier</title>
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
        <?php include 'navbar6.php'; ?>


 <div class="col-md-10 offset-md-1 d-flex justify-content-center align-items-center" style="min-height: 90vh;">
            <div class="card bg-white">
                <div class="card-header">
                    <h3 class="mb-0"><i class="bi bi-plus-circle-dotted"></i> Add Supplier</h3>
                </div>
                <div class="card-body p-4">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateForm()">

    <!-- Supplier Name -->
    <div class="mb-3">
        <label class="form-label">
            Supplier Name <span class="text-danger">*</span>
        </label>
        <input type="text"
               name="supplier_name"
               id="supplier_name"
               class="form-control"
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
               class="form-control">
    </div>

    <!-- Contact Number -->
    <div class="mb-3">
        <label class="form-label">Contact Number</label>
        <input type="text"
               name="contact_number"
               id="contact_number"
               class="form-control"
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
               class="form-control">
    </div>

    <!-- Address -->
    <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address"
                  id="address"
                  class="form-control"
                  rows="2"
                  minlength="5"></textarea>
    </div>

    <!-- GST Number -->
    <div class="mb-3">
        <label class="form-label">GST Number</label>
        <input type="text"
               name="gst_number"
               id="gst_number"
               class="form-control"
               maxlength="15"
               pattern="[0-9A-Z]{15}"
               title="Enter valid 15 digit GST number">
    </div>

    <div class="d-grid gap-2">
        <button type="submit"
                class="btn btn-primary w-100">
            Add Supplier
        </button>
    </div>

</form>

<script>

function validateForm() {

    let name = document.getElementById("supplier_name").value;
    let contact = document.getElementById("contact_number").value;
    let email = document.getElementById("email").value;
    let gst = document.getElementById("gst_number").value;

    // Supplier Name validation
    if (name.length < 3) {
        alert("Supplier name must be at least 3 characters");
        return false;
    }

    // Contact Number validation
    if (contact !== "" && contact.length != 10) {
        alert("Contact number must be 10 digits");
        return false;
    }

    // Email validation
    if (email !== "" && !email.includes("@")) {
        alert("Enter valid email address");
        return false;
    }

    // GST validation
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

</body>
</html>
