<?php
include 'config.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo "Invalid Bill ID.";
    exit;
}

// Fetch bill
$bill_stmt = $conn->prepare("SELECT * FROM sales_bills WHERE id = ?");
$bill_stmt->bind_param("i", $id);
$bill_stmt->execute();
$bill = $bill_stmt->get_result()->fetch_assoc();
if (!$bill) {
    echo "Bill not found.";
    exit;
}

// Fetch medicines in the bill
$med_stmt = $conn->prepare("SELECT * FROM sales_medicin WHERE bill_id = ?");
$med_stmt->bind_param("i", $id);
$med_stmt->execute();
$medicines = $med_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salesman_name = $_POST['salesman_name'];
    $patient_name = $_POST['patient_name'];
    $city = $_POST['city'];
    $mobile = $_POST['mobile'];

    $conn->begin_transaction();

    try {
        // Update bill info
        $update_bill = $conn->prepare("UPDATE sales_bills SET salesman_name=?, patient_name=?, city=?, mobile=? WHERE id=?");
        $update_bill->bind_param("ssssi", $salesman_name, $patient_name, $city, $mobile, $id);
        $update_bill->execute();
        $update_bill->close();

        $final_total = 0;

        foreach ($_POST['medicine_id'] as $index => $mid) {
            $qty = intval($_POST['qty'][$index]);
            $mrp = floatval($_POST['mrp'][$index]);
            $gst = floatval($_POST['gst'][$index]);

            // Fetch current sale row
            $get_sale = $conn->prepare("SELECT product, qty1 FROM sales_medicin WHERE id=?");
            $get_sale->bind_param("i", $mid);
            $get_sale->execute();
            $sale_data = $get_sale->get_result()->fetch_assoc();
            $product_id = intval($sale_data['product']);  // Make sure this is the correct product ID
            $old_qty = intval($sale_data['qty1']);
            $get_sale->close();

            $diff_qty = $qty - $old_qty;

            // Update sales_medicin
            $update_m = $conn->prepare("UPDATE sales_medicin SET qty1=?, mrp=?, gst=? WHERE id=?");
            $update_m->bind_param("idii", $qty, $mrp, $gst, $mid);
            $update_m->execute();
            $update_m->close();

            // Update medicine_master stock
            $update_master = $conn->prepare("UPDATE medicine_master SET stock = stock - ? WHERE id = ?");
            $update_master->bind_param("ii", $diff_qty, $product_id);
            $update_master->execute();
            $update_master->close();

            // Update ss_medicine stock
            $update_s = $conn->prepare("UPDATE ss_medicine SET new_qty = new_qty - ? WHERE id = ?");
            $update_s->bind_param("ii", $diff_qty, $product_id);
            $update_s->execute();
            $update_s->close();

            // Calculate totals
            $item_total = $qty * $mrp;
            $gst_amount = $item_total * ($gst / 100);
            $final_total += $item_total + $gst_amount;
        }

        // Update final total
        $update_final = $conn->prepare("UPDATE sales_bills SET f_total_amount=? WHERE id=?");
        $update_final->bind_param("di", $final_total, $id);
        $update_final->execute();
        $update_final->close();

        $conn->commit();
        echo "<script>alert('Bill updated successfully.'); window.location.href='';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='color:red;'>Error: " . $e->getMessage() . "</div>";
    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Edit Sales Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">
    <style>
             body {
            background: #f4f6f9;
        }
        .card {
            max-width: 500px;
            width: 100%;
            margin: auto;
            margin-top: 50px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            left: 100px;
        }
        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            border-radius: 15px 15px 0 0;
            text-align: center;
            padding: 20px;
        }
        </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'navbar2.php'; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person-lines-fill me-2"></i> Upadate Sels List
                    </div>

                    <div class="card-body">
    <form method="POST">
        <div class="row mb-3">
           <div class="mb-3">
                <label>Salesman</label>
                <input type="text" name="salesman_name" class="form-control" value="<?= htmlspecialchars($bill['salesman_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Patient</label>
                <input type="text" name="patient_name" class="form-control" value="<?= htmlspecialchars($bill['patient_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label>City</label>
                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($bill['city']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Mobile</label>
                <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($bill['mobile']) ?>" required>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Medicine</th>
                <th>Qty</th>
                <th>MRP</th>
                <th>GST (%)</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($medicines as $index => $med): ?>
                <?php
                $pid = $med['product'];
                $mname = '';
                $q1 = $conn->prepare("SELECT medicine_name FROM medicine_master WHERE id=?");
                $q1->bind_param("i", $pid);
                $q1->execute();
                $q1->bind_result($mname);
                $q1->fetch();
                $q1->close();

                if (!$mname) {
                    $q2 = $conn->prepare("SELECT product FROM ss_medicine WHERE id=?");
                    $q2->bind_param("i", $pid);
                    $q2->execute();
                    $q2->bind_result($mname);
                    $q2->fetch();
                    $q2->close();
                }
                ?>
                <tr>
                    <td><?= htmlspecialchars($mname ?: "Unknown") ?></td>
                    <td>
                        <input type="number" name="qty[]" value="<?= $med['qty1'] ?>" class="form-control" required>
                        <input type="hidden" name="medicine_id[]" value="<?= $med['id'] ?>">
                    </td>
                    <td><input type="number" name="mrp[]" value="<?= $med['mrp'] ?>" step="0.01" class="form-control" required></td>
                    <td><input type="number" name="gst[]" value="<?= $med['gst'] ?>" step="0.01" class="form-control" required></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Update Bill</button>
        <a href="sale_veiw.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
