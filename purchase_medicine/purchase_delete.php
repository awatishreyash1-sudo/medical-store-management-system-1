<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete logic
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Step 1: Fetch product name and new_qty from s_medicin
    $stmt = $conn->prepare("SELECT product, new_qty FROM s_medicin WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->bind_result($product, $new_qty);

    if ($stmt->fetch()) {
        $stmt->close();

        // Step 2: Update stock in medicine_master (reduce stock by new_qty)
        $updateStmt = $conn->prepare("UPDATE medicine_master SET stock = stock - ? WHERE medicine_name = ?");
        $updateStmt->bind_param("is", $new_qty, $product);
        $updateStmt->execute();
        $updateStmt->close();

        // Step 3: Delete the s_medicin record
        $deleteStmt = $conn->prepare("DELETE FROM s_medicin WHERE id = ?");
        $deleteStmt->bind_param("i", $delete_id);
        if ($deleteStmt->execute()) {
            echo "<script>alert('Record deleted and stock updated successfully'); window.location.href='update_purchase.php';</script>";
        } else {
            echo "<script>alert('Error deleting record'); window.location.href='update_purchase.php';</script>";
        }
        $deleteStmt->close();
    } else {
        echo "<script>alert('Record not found'); window.location.href='update_purchase.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request'); window.location.href='update_purchase.php';</script>";
}

$conn->close();
?>
