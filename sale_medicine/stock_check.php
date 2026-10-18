<?php
function checkStock($conn, $medicineId, $qty) {
    // Prepare the query to get stock from medicine_master based on ID
    $stmt = $conn->prepare("SELECT stock FROM medicine_master WHERE id = ?");
    $stmt->bind_param("i", $medicineId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($stock = $result->fetch_assoc()) {
        $stmt->close();

        // Check if requested qty exceeds available stock
        if ($qty > $stock['stock']) {
            return "Requested quantity ($qty) exceeds available stock ({$stock['stock']}) for Product ID $medicineId.";
        } else {
            return true; // Stock is enough
        }
    } else {
        $stmt->close();
        return "Product with ID $medicineId not found in Medicine Master.";
    }
}
?>
