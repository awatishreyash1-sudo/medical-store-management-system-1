<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search query safely
$query = $conn->real_escape_string($_POST['query'] ?? '');

// Fetch matching medicines with stock > 0
$sql = "SELECT * FROM ss_medicine WHERE product LIKE '%$query%' AND new_qty > 0 LIMIT 5";
$result = $conn->query($sql);

$output = '';

// Loop through results and build dropdown buttons
while ($row = $result->fetch_assoc()) {
    $mrpWithAdd = $row['mrp'] + 3;  // MRP में ₹3 जोड़ना

    $output .= "<button type='button' class='dropdown-item' onclick='selectMedicine(this.closest(\".dropdown-item\"), this.closest(\"tr\"))' 
        data-id='{$row['id']}'
        data-name='{$row['product']}'
        data-batch='{$row['batch']}'
        data-gst='{$row['gst']}'
        data-mrp='{$mrpWithAdd}'
        data-expiry='{$row['ex_date']}'
        data-stock='{$row['new_qty']}'
    >{$row['product']}</button>";
}

// If no result found
echo $output ?: '<span class="dropdown-item text-muted">No matches found</span>';
?>
