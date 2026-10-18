<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$code = $_GET['code'] ?? '';
if (!empty($code)) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete from users table
        $conn->query("DELETE FROM users WHERE code = '$code'");

        // Delete from salesman table
        $conn->query("DELETE FROM salesman WHERE code = '$code'");

        // Commit transaction
        $conn->commit();
        header("Location: view_salesman.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Delete failed: " . $e->getMessage();
    }
} else {
    echo "Invalid salesman code.";
}
?>
