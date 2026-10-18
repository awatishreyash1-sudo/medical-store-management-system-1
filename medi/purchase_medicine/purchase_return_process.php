<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $return_qty = $_POST['return_qty'];
    $reason = $_POST['reason'];

    // Fetch product details from s_medicin
    $result = $conn->query("SELECT product, mrp, gst FROM s_medicin WHERE id = $id");
    if ($result->num_rows == 0) {
        echo "<script>alert('Medicine not found'); window.location.href='purchase_return.php';</script>";
        exit;
    }

    $row = $result->fetch_assoc();
    $product_name = $row['product'];
    $mrp = $row['mrp'];
    $gst = $row['gst'];

    // Calculate amounts
    $base = $mrp * $return_qty;
    $gst_amt = ($base * $gst) / 100;
    $return_amount = $base + $gst_amt;

    // Insert into purchase_return
    $stmt = $conn->prepare("INSERT INTO purchase_return 
        (medicine_id, return_qty, reason, mrp, gst, base_amount, gst_amount, return_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iisddddd", $id, $return_qty, $reason, $mrp, $gst, $base, $gst_amt, $return_amount);
        $stmt->execute();
        $stmt->close();
    } else {
        die("Insert error: " . $conn->error);
    }

    // Update s_medicin table
    $stmt = $conn->prepare("UPDATE s_medicin SET new_qty = new_qty - ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $return_qty, $id);
        $stmt->execute();
        $stmt->close();
    }

    // Update medicine_master table
    $stmt = $conn->prepare("UPDATE medicine_master SET stock = stock - ? WHERE medicine_name = ?");
    if ($stmt) {
        $stmt->bind_param("is", $return_qty, $product_name);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>
        alert('Purchase Return Processed Successfully!\\nQty: $return_qty\\nMRP: ₹$mrp\\nGST: $gst%\\nBase: ₹$base\\nGST Amt: ₹$gst_amt\\nReturn Amt: ₹$return_amount');
        window.location.href='purchase_return.php';
    </script>";
}
?>
