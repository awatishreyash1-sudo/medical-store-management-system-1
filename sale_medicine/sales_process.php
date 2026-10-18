<?php
include 'config.php';
include 'salesman_auth.php';

$error_message = '';
$success_message = '';
$salesman_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check'])) {

    $pay_mode = $conn->real_escape_string($_POST['pay_mode']);
    $products = $_POST['product'] ?? [];
    $quantities = $_POST['qty1'] ?? [];
    $mrps = $_POST['mrp'] ?? [];
    $amounts = $_POST['amount'] ?? [];
    $batches = $_POST['batch'] ?? [];
    $gst = $_POST['gst'] ?? [];
    $expiry_dates = $_POST['ex_date'] ?? [];
    $sr_numbers = $_POST['sr_no'] ?? [];
    $patient_name = $conn->real_escape_string($_POST['patient_name']);
    $total_amount = floatval($_POST['total_amount']);
    $password = $conn->real_escape_string($_POST['password']);
    $f_total_amount = floatval($_POST['f_total_amount']);
    $city = $conn->real_escape_string($_POST['city']);
    $mobile = $conn->real_escape_string($_POST['mobile']);

    $error = false;

    // 1. Verify Salesman
    $stmt = $conn->prepare("SELECT name FROM salesman WHERE password = ?");
    $stmt->bind_param("s", $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $salesman_name = $result->fetch_assoc()['name'];
    } else {
        $error = true;
        $error_message = "❌ Authentication Failed! Please enter a valid salesman password.";
    }
    $stmt->close();

    // 2. Check Stock and Expiry in ss_medicine
    if (!$error) {
        foreach ($products as $index => $product_id) {
            $qty = intval($quantities[$index]);
            $exp_date = $expiry_dates[$index];

            $today = date('Y-m-d');
            if ($exp_date <= $today) {
                $error = true;
                $error_message = "❌ Sale cannot proceed. Selected product (ID: $product_id) is expired on $exp_date.";
                break;
            }

            $stmt = $conn->prepare("SELECT new_qty FROM ss_medicine WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stock_result = $stmt->get_result();

            if ($stock_result->num_rows > 0) {
                $stock_data = $stock_result->fetch_assoc();
                if ($qty > $stock_data['new_qty']) {
                    $error = true;
                    $error_message = "❌ Insufficient stock for Product ID $product_id. Available quantity is lower than requested.";
                    break;
                }
            } else {
                $error = true;
                $error_message = "❌ Product ID $product_id not found in stock records.";
                break;
            }
            $stmt->close();
        }
    }

    // 3. Insert Sales & Update Stocks
    if (!$error) {
        $stmt = $conn->prepare(
            "INSERT INTO sales_bills (patient_name, total_amount, salesman_name, f_total_amount, city, mobile)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sdssss", $patient_name, $total_amount, $salesman_name, $f_total_amount, $city, $mobile);

        if ($stmt->execute()) {
            $bill_id = $stmt->insert_id;
            $stmt->close();

            foreach ($products as $index => $product_id) {
                $sr_no = intval($sr_numbers[$index]);
                $qty = intval($quantities[$index]);
                $mrp = floatval($mrps[$index]);
                $amount = floatval($amounts[$index]);
                $batch = $conn->real_escape_string($batches[$index]);
                $gst_val = $conn->real_escape_string($gst[$index]);
                $ex_date = $conn->real_escape_string($expiry_dates[$index]);

                // Get product name
                $stmt = $conn->prepare("SELECT product FROM ss_medicine WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $product_result = $stmt->get_result();
                $product_name = $product_result->fetch_assoc()['product'];
                $stmt->close();

                // Update medicine_master stock
                $stock_stmt = $conn->prepare("UPDATE medicine_master SET stock = stock - ? WHERE medicine_name = ?");
                $stock_stmt->bind_param("is", $qty, $product_name);
                $stock_stmt->execute();
                $stock_stmt->close();

                // Update ss_medicine stock
                $stmt = $conn->prepare("UPDATE ss_medicine SET new_qty = new_qty - ? WHERE id = ?");
                $stmt->bind_param("ii", $qty, $product_id);
                $stmt->execute();
                $stmt->close();

                // Insert into sales_medicin
                $stmt = $conn->prepare(
                    "INSERT INTO sales_medicin (bill_id, sr_no, product, qty1, mrp, amount, batch, gst, ex_date, pay_mode)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("iisiddssss", $bill_id, $sr_no, $product_id, $qty, $mrp, $amount, $batch, $gst_val, $ex_date, $pay_mode);

                if (!$stmt->execute()) {
                    $error = true;
                    $error_message = "❌ Error while saving sales details for Product ID $product_id. Please try again.";
                    $stmt->close();
                    break;
                }
                $stmt->close();
            }

            if (!$error) {
                $success_message = "✅ Sales Bill Successfully Generated! Stock has been updated.";
            }
        } else {
            $error_message = "❌ Unable to generate sales bill. Please try again.";
        }
    }
}
?>