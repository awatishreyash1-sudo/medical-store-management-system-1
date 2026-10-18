<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is passed
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete Query
    $sql = "DELETE FROM s_medicin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Success Message
        header("Location: add_medi_veiw.php?msg=Record deleted successfully");
        exit;
    } else {
        // Error Message
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
