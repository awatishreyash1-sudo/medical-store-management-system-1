<?php
// purchase_report.php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $sql = "SELECT id, supplier_name, product_name, batch_no, purchase_price, quantity, total_amount, expiry_date, bill_no, payment_mode 
                FROM purchase_entry 
                WHERE DATE(purchase_date) BETWEEN ? AND ? 
                ORDER BY supplier_name ASC, product_name ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Product</th>
                        <th>Batch No</th>
                        <th>Purchase Price</th>
                        <th>Quantity</th>
                        <th>Total Amount</th>
                        <th>Expiry Date</th>
                        <th>Bill No</th>
                        <th>Payment Mode</th>
                    </tr>
                </thead>
                <tbody>';

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($row['supplier_name']) . '</td>
                        <td>' . htmlspecialchars($row['product_name']) . '</td>
                        <td>' . htmlspecialchars($row['batch_no']) . '</td>
                        <td>' . number_format($row['purchase_price'], 2) . '</td>
                        <td>' . htmlspecialchars($row['quantity']) . '</td>
                        <td>' . number_format($row['total_amount'], 2) . '</td>
                        <td>' . htmlspecialchars($row['expiry_date']) . '</td>
                        <td>' . htmlspecialchars($row['bill_no']) . '</td>
                        <td>' . htmlspecialchars($row['payment_mode']) . '</td>
                    </tr>';
            }
        } else {
            echo '<tr><td colspan="9" class="text-center">No records found.</td></tr>';
        }

        echo '</tbody></table>';

        $stmt->close();
        exit;
    }
}
?>
