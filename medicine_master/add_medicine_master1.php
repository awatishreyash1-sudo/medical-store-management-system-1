<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_master'])) {

    $medicine_name = trim($_POST['medicine_name'] ?? '');
    $company_name  = trim($_POST['company_name'] ?? '');
    $stock         = intval($_POST['stock'] ?? 0);

    // Validation
    if (empty($medicine_name) || empty($company_name) || $stock < 0) {

        $message = "
        <div class='alert alert-warning alert-dismissible fade show text-center' role='alert'>
            <strong>Warning!</strong> Please enter valid details.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";

    } else {

        $stmt = $conn->prepare("INSERT INTO medicine_master (medicine_name, company_name, stock) VALUES (?, ?, ?)");

        // s = string, s = string, i = integer
        $stmt->bind_param("ssi", $medicine_name, $company_name, $stock);

        if ($stmt->execute()) {

            $message = "
            <div class='alert alert-success alert-dismissible fade show text-center' role='alert'>
                <strong>Success!</strong> Medicine added successfully.
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";

        } else {

            $message = "
            <div class='alert alert-danger alert-dismissible fade show text-center' role='alert'>
                <strong>Error!</strong> Something went wrong. Please try again.
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
        }

        $stmt->close();
    }
}
?>