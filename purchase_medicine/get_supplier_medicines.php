<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");

$supplier_id = intval($_POST['supplier_id'] ?? 0);

if ($supplier_id > 0) {
    $stmt = $conn->prepare("SELECT id, name, mrp, gst FROM medicines WHERE supplier_id = ?");
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Medicine Name</th><th>MRP</th><th>GST (%)</th><th>Quantity</th><th>Batch No</th><th>Expiry Date</th></tr></thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            $id = intval($row['id']);
            $name = htmlspecialchars($row['name']);
            $mrp = number_format($row['mrp'], 2);
            $gst = number_format($row['gst'], 2);

            echo "<tr>";
            echo "<td>$name<input type='hidden' name='medicines[{$id}][medicine_id]' value='{$id}'></td>";
            echo "<td><input type='number' name='medicines[{$id}][mrp]' class='form-control' step='0.01' value='{$mrp}' required></td>";
            echo "<td><input type='number' name='medicines[{$id}][gst]' class='form-control' step='0.01' value='{$gst}'></td>";
            echo "<td><input type='number' name='medicines[{$id}][qty]' class='form-control medicine-qty' min='0' value='0' required></td>";
            echo "<td><input type='text' name='medicines[{$id}][batch_no]' class='form-control'></td>";
            echo "<td><input type='date' name='medicines[{$id}][expiry_date]' class='form-control'></td>";
            echo "</tr>";
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="text-warning">No medicines found for this supplier.</p>';
    }
    $stmt->close();
} else {
    echo '<p class="text-danger">Invalid supplier selected.</p>';
}

$conn->close();
?>
