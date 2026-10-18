<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");

if (isset($_POST['supplier_name'])) {

    $supplier = $conn->real_escape_string($_POST['supplier_name']);

    $stmt = $conn->prepare("
        SELECT SUM(amount) AS total 
        FROM expiry_medicin 
        WHERE s_name = ? AND status = 'pending'
    ");

    $stmt->bind_param("s", $supplier);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo ($row['total'] != NULL) ? $row['total'] : 0;

    $stmt->close();
}
?>