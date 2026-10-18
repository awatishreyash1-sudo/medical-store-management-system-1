<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Fetch Salesman and Medicine list based on date range
    if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $sql = "SELECT id, s_name, product, batch, mrp, qty, amount, ex_date, pay_mode 
                FROM s_medicin 
                WHERE DATE(datetime) BETWEEN ? AND ? 
                ORDER BY s_name ASC, product ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Salesman Name</th>
                            <th>Medicine</th>
                            <th>Batch</th>
                            <th>MRP</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>Expiry Date</th>
                            <th>Payment Mode</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
            while ($med = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($med['s_name']) . '</td>
                        <td>' . htmlspecialchars($med['product']) . '</td>
                        <td>' . htmlspecialchars($med['batch']) . '</td>
                        <td>' . number_format($med['mrp'], 2) . '</td>
                        <td>' . htmlspecialchars($med['qty']) . '</td>
                        <td>' . number_format($med['amount'], 2) . '</td>
                        <td>' . htmlspecialchars($med['ex_date']) . '</td>
                        <td>' . htmlspecialchars($med['pay_mode']) . '</td>
                        <td>
                            <a href="update_medicine.php?id=' . $med['id'] . '" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                            <a href="add_medicine_delete.php?id=' . $med['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this record?\')"> <i class="bi bi-trash"></i></a>
                        </td>
                      </tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-warning">No records found.</div>';
        }
        $stmt->close();
        exit;
    }
}
?>
