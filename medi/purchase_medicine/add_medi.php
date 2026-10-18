<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_medicine'])) {

    $product = trim($conn->real_escape_string($_POST['product'] ?? ''));
    $mrp = floatval($_POST['mrp'] ?? 0);
    $gst = floatval($_POST['gst'] ?? 0);
    $qty = intval($_POST['qty'] ?? 0);
    $s_name = trim($conn->real_escape_string($_POST['s_name'] ?? ''));
    $city = trim($conn->real_escape_string($_POST['city'] ?? ''));
    $pay_mode = trim($conn->real_escape_string($_POST['pay_mode'] ?? ''));
    $sales_pass = trim($_POST['sales_pass'] ?? '');
    $batch = trim($conn->real_escape_string($_POST['batch'] ?? ''));
    $ex_date = trim($conn->real_escape_string($_POST['ex_date'] ?? ''));
    $company = trim($conn->real_escape_string($_POST['company'] ?? ''));

    // GST Calculation
    $basic_amount = $mrp * $qty;
    $gst_amount = ($basic_amount * $gst) / 100;
    $total_amount = $basic_amount + $gst_amount;

    // Step 1: Verify Salesman
    $salesman_name = '';
    $stmt = $conn->prepare("SELECT name FROM salesman WHERE password = ?");
    $stmt->bind_param("s", $sales_pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $salesman_row = $result->fetch_assoc();
        $salesman_name = $salesman_row['name'];
        $stmt->close();

        // Step 2: Check if product already exists in s_medicin
        $stmt = $conn->prepare("SELECT id, qty FROM s_medicin WHERE product = ? AND batch = ?");
        $stmt->bind_param("ss", $product, $batch);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $existing_qty);
            $stmt->fetch();
            $stmt->close();

            $new_qty = $existing_qty + $qty;
            $new_amount = $new_qty * $mrp;

            // Update s_medicin
            $update_stmt = $conn->prepare("UPDATE s_medicin SET mrp = ?, qty = ?, new_qty = ?, amount = ? WHERE id = ?");
            $update_stmt->bind_param("ddidi", $mrp, $new_qty, $new_qty, $new_amount, $id);
            $update_stmt->execute();
            $update_stmt->close();

            $message .= "Medicine updated successfully! ";
        } else {
            $stmt->close();

            // Insert new medicine into s_medicin
            $insert_stmt = $conn->prepare("INSERT INTO s_medicin (product, mrp, gst, qty, new_qty, amount, s_name, city, pay_mode, sales_pass, batch, ex_date, company, salesman_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sddidsssssssss", $product, $mrp, $gst, $qty, $qty, $total_amount, $s_name, $city, $pay_mode, $sales_pass, $batch, $ex_date, $company, $salesman_name);
            $insert_stmt->execute();
            $insert_stmt->close();

            $message .= "New medicine added";
        }

        // Step 3: Update medicine_master stock
        $check = $conn->prepare("SELECT stock FROM medicine_master WHERE medicine_name = ?");
        $check->bind_param("s", $product);
        $check->execute();
        $check->bind_result($existing_stock);

        if ($check->fetch()) {
            $check->close();
            $new_stock = $existing_stock + $qty;

            $update_stock = $conn->prepare("UPDATE medicine_master SET stock = ? WHERE medicine_name = ?");
            $update_stock->bind_param("is", $new_stock, $product);
            $update_stock->execute();
            $update_stock->close();

            $message .= "& Medicine stock updated!";
        } else {
            $check->close();
            $message .= "Warning: Medicine not found in Medicine Master. Please add it first.";
        }

    } else {
        $stmt->close();
        $message = " Invalid Salesman Password! Medicine not added.";
    }
}
?>
