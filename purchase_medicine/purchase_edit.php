<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id'] ?? 0);

// Fetch old data from s_medicin
$stmt = $conn->prepare("SELECT * FROM s_medicin WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$medicine = $result->fetch_assoc();
$stmt->close();

if (!$medicine) {
    die("Record not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product = trim($_POST['product']);
    $batch = trim($_POST['batch']);
    $qty = intval($_POST['qty']);
    $new_qty = intval($_POST['new_qty']);
    $mrp = floatval($_POST['mrp']);
    $gst = floatval($_POST['gst']);
    $ex_date = trim($_POST['ex_date']);
    $pay_mode = trim($_POST['pay_mode']);
    $s_name = trim($_POST['s_name']);

    $base = $qty * $mrp;
    $amount = $base + ($base * $gst / 100);

    $conn->begin_transaction();

    try {
        // Update s_medicin
        $stmt = $conn->prepare("UPDATE s_medicin SET product=?, batch=?, qty=?, new_qty=?, mrp=?, amount=?, ex_date=?, pay_mode=?, s_name=?, gst=? WHERE id=?");
        $stmt->bind_param("ssiiddsssdi", $product, $batch, $qty, $new_qty, $mrp, $amount, $ex_date, $pay_mode, $s_name, $gst, $id);
        $stmt->execute();
        $stmt->close();

        // Update medicine_master stock
        $old_qty = intval($medicine['new_qty']);
        $stock_diff = $new_qty - $old_qty;

        $check = $conn->prepare("SELECT stock FROM medicine_master WHERE medicine_name = ?");
        if (!$check) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $check->bind_param("s", $product);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $update = $conn->prepare("UPDATE medicine_master SET stock = stock + ? WHERE medicine_name = ?");
            if (!$update) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $update->bind_param("is", $stock_diff, $product);
            $update->execute();
            $update->close();
        }

        $check->close();
        $conn->commit();

        echo "<script>alert('Updated successfully!'); window.location.href='update_purchase.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Update failed: " . $e->getMessage() . "');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Purchase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function calculateAmount() {
            const qty = parseFloat(document.querySelector('input[name="qty"]').value) || 0;
            const mrp = parseFloat(document.querySelector('input[name="mrp"]').value) || 0;
            const gst = parseFloat(document.querySelector('input[name="gst"]').value) || 0;
            const base = qty * mrp;
            const total = base + (base * gst / 100);
            document.getElementById('amount').value = total.toFixed(2);
        }

        document.addEventListener("DOMContentLoaded", function () {
            ['qty', 'mrp', 'gst'].forEach(field => {
                document.querySelector(`input[name="${field}"]`).addEventListener("input", calculateAmount);
            });
        });
    </script>
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white text-center">
            <h4>Update Purchase Entry</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Product</label>
                    <input type="text" name="product" class="form-control" value="<?= htmlspecialchars($medicine['product']) ?>" readonly>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Batch</label>
                        <input type="text" name="batch" class="form-control" value="<?= $medicine['batch'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Quantity</label>
                        <input type="number" name="qty" class="form-control" value="<?= $medicine['qty'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>New Quantity</label>
                        <input type="number" name="new_qty" class="form-control" value="<?= $medicine['new_qty'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>MRP</label>
                        <input type="number" step="0.01" name="mrp" class="form-control" value="<?= $medicine['mrp'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>GST (%)</label>
                        <input type="number" step="0.01" name="gst" class="form-control" value="<?= $medicine['gst'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Amount (Auto)</label>
                        <input type="text" id="amount" class="form-control" readonly value="<?= number_format($medicine['amount'], 2) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Expiry Date</label>
                    <input type="date" name="ex_date" class="form-control" value="<?= $medicine['ex_date'] ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Payment Mode</label>
                        <input type="text" name="pay_mode" class="form-control" value="<?= $medicine['pay_mode'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Supplier Name</label>
                        <input type="text" name="s_name" class="form-control" value="<?= $medicine['s_name'] ?>" required>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success px-4">Update</button>
                    <a href="update_purchase.php" class="btn btn-secondary px-4">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
