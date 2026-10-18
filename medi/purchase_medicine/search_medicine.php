<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
$query = $conn->real_escape_string($_POST['query']);
$sql = "SELECT DISTINCT medicine_name FROM medicine_master WHERE medicine_name LIKE '%$query%' LIMIT 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='suggestion-item product-item'>" . htmlspecialchars($row['medicine_name']) . "</div>";
    }
} else {
    echo "<div class='suggestion-item text-muted'>No medicine found</div>";
}
?>




