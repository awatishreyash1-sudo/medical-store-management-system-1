<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
$query = $conn->real_escape_string($_POST['query']);
$sql = "SELECT supplier_name, address, contact_number FROM supplier_master WHERE supplier_name LIKE '%$query%' LIMIT 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='suggestion-item supplier-item' data-address='" . htmlspecialchars($row['address']) . "'data-mobile='" . htmlspecialchars($row['contact_number'], ENT_QUOTES) . "'>" . htmlspecialchars($row['supplier_name']) . "</div>";
    }
} else {
    echo "<div class='suggestion-item text-muted'>No match found</div>";
}
?>
