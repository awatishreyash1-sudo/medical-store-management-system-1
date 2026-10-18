<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_master'])) {
    $medicine_name = trim($conn->real_escape_string($_POST['medicine_name'] ?? ''));
    $company_name = trim($conn->real_escape_string($_POST['company_name'] ?? ''));

    $stock = intval($_POST['stock'] ?? 0);
   

    if (empty($medicine_name) ) {
        $message = "<p style='color:red;'>Please enter valid details.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO medicine_master (medicine_name, company_name, stock) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $medicine_name, $company_name, $stock);
        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Medicine added successfully!</p>";
        } else {
            $message = "<p style='color:red;'>Error: " . $conn->error . "</p>";
        }
        $stmt->close();
    }
}
?>