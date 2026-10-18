<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");

if (!isset($_GET['id'])) {
    die("Invalid Bill ID.");
}

$bill_id = intval($_GET['id']);
$success_message = '';
$error_message = '';

$bill_sql = "SELECT * FROM ss_bills WHERE id = $bill_id";
$bill_result = $conn->query($bill_sql);
if ($bill_result->num_rows === 0) {
    die("Bill not found.");
}
$bill = $bill_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s_name = $conn->real_escape_string($_POST['s_name']);
    $address = $conn->real_escape_string($_POST['address']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $pay_mode = $conn->real_escape_string($_POST['pay_mode']);
    $total_amount = floatval($_POST['total_amount']);
    $f_total_amount = floatval($_POST['f_total_amount']);

    $products = $_POST['product'];
    $batches = $_POST['batch'];
    $gst_values = $_POST['gst'];
    $mrps = $_POST['mrp'];
    $expiry_dates = $_POST['ex_date'];
    $qtys = $_POST['qty1'];
    $amounts = $_POST['amount'];
    $sr_nos = $_POST['sr_no'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("UPDATE ss_bills SET s_name=?, address=?, mobile=?, pay_mode=?, total_amount=?, f_total_amount=? WHERE id=?");
        $stmt->bind_param("ssssddi", $s_name, $address, $mobile, $pay_mode, $total_amount, $f_total_amount, $bill_id);
        $stmt->execute();
        $stmt->close();

        $conn->query("DELETE FROM ss_medicine WHERE bill_id = $bill_id");

        foreach ($products as $index => $product) {
            $sr_no = intval($sr_nos[$index]);
            $batch = $conn->real_escape_string($batches[$index]);
            $gst = floatval($gst_values[$index]);
            $mrp = floatval($mrps[$index]);
            $ex_date = $conn->real_escape_string($expiry_dates[$index]);
            $qty = intval($qtys[$index]);
            $amount = floatval($amounts[$index]);

            $stmt = $conn->prepare("INSERT INTO ss_medicine (bill_id, sr_no, product, qty1, new_qty, mrp, amount, batch, gst, ex_date, pay_mode) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisiddsssss", $bill_id, $sr_no, $product, $qty, $qty, $mrp, $amount, $batch, $gst, $ex_date, $pay_mode);
            $stmt->execute();
            $stmt->close();

            $stock_stmt = $conn->prepare("UPDATE medicine_master SET stock = stock + ? WHERE medicine_name = ?");
            $stock_stmt->bind_param("is", $qty, $product);
            $stock_stmt->execute();
            $stock_stmt->close();
        }

        $conn->commit();
        $success_message = "Bill updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Update failed: " . $e->getMessage();
    }
}

$meds = $conn->query("SELECT * FROM ss_medicine WHERE bill_id = $bill_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Purchase Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../nav.css">
     <style>
         body {
            background-color: #f0f4fa;
        }
        .card {
            max-width: 1000px;
            width: 1000px;
            margin: auto;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border: none;
            left :30px;
        }
        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            padding: 20px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }
        .suggestion-box {
            position: absolute;
            background: #fff;
            z-index: 1000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
        }
        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background-color: #f0f0f0;
        }

        </style>
</head>
<body>
    <div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'navbar.php'; ?>

<div class="container-fluid">
    <div class="row justify-content-center mt-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow rounded-4">
                <div class="card-header bg-info text-white text-center rounded-top-4 py-3">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> Edit Purchase Bill #<?= $bill_id ?></h4>
                </div>

                <div class="card-body">
    

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row mb-3 position-relative">
            <div class="col-md-6">
                <label>Supplier Name</label>
                <input type="text" id="supplierInput" name="s_name" class="form-control" value="<?= $bill['s_name'] ?>" autocomplete="off" required>
                <div id="supplierList" class="suggestion-box d-none"></div>
            </div>
            <div class="col-md-6">
                <label>Address</label>
                <input type="text" id="addressInput" name="address" class="form-control" value="<?= $bill['address'] ?>" required>
            </div>
            <div class="col-md-6">
    <label>Mobile</label>
    <input type="text" id="mobileInput" name="mobile" class="form-control" 
           value="<?= $bill['mobile'] ?>" 
           pattern="\d{10}" maxlength="10"
           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
           title="Please enter a valid 10-digit mobile number" required>
</div>

            <div class="col-md-6">
                <label>Payment Mode</label>
                <input type="text" name="pay_mode" class="form-control" value="<?= $bill['pay_mode'] ?>" required>
            </div>
        </div>

        <hr class="my-4">

                        <div class="bg-info text-white rounded-3 px-3 py-2 text-center mb-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-capsule-pill"></i> Add Medicine List</h5>
                        </div>

        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered table-hover" id="medicine_table">
                                <thead class="table-light text-center">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Product</th>
                    <th>Batch</th>
                    <th>GST %</th>
                    <th>MRP</th>
                    <th>Expiry</th>
                    <th>Qty</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($med = $meds->fetch_assoc()): ?>
                <tr>
                    <td><input type="number" name="sr_no[]" class="form-control" value="<?= $med['sr_no'] ?>"></td>
                    <td>
                        <input type="text" name="product[]" class="form-control productInput" value="<?= $med['product'] ?>" autocomplete="off">
                        <div class="suggestion-box d-none productList"></div>
                    </td>
                    <td><input type="text" name="batch[]" class="form-control" value="<?= $med['batch'] ?>"></td>
                    <td><input type="number" step="0.01" name="gst[]" class="form-control" value="<?= $med['gst'] ?>"></td>
                    <td><input type="number" step="0.01" name="mrp[]" class="form-control" value="<?= $med['mrp'] ?>"></td>
                    <td><input type="date" name="ex_date[]" class="form-control" value="<?= $med['ex_date'] ?>"></td>
                    <td><input type="number" name="qty1[]" class="form-control" value="<?= $med['qty1'] ?>"></td>
                    <td><input type="number" step="0.01" name="amount[]" class="form-control" value="<?= $med['amount'] ?>" readonly></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
                </div>
               

        <div class="row g-3">
            <div class="col-md-6">
                <label>Total Amount</label>
                <input type="text" name="total_amount" class="form-control" value="<?= $bill['total_amount'] ?>" readonly>
            </div>
            <div class="col-md-6">
                <label>Final Amount</label>
                <input type="text" name="f_total_amount" class="form-control" value="<?= $bill['f_total_amount'] ?>" readonly>
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Update Bill</button>
            <a href="purchase_entry_view.php" class="btn btn-secondary">Back</a>
        </div>
         <div class="mt-4">
    </form>
</div>
                </div>
                </div>
                </div>
                </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function calculateAmount(row) {
    const qty = parseFloat(row.find('[name="qty1[]"]').val()) || 0;
    const mrp = parseFloat(row.find('[name="mrp[]"]').val()) || 0;
    const gst = parseFloat(row.find('[name="gst[]"]').val()) || 0;
    const amount = ((mrp * qty) + ((mrp * qty) * gst / 100)).toFixed(2);
    row.find('[name="amount[]"]').val(amount);
}

function updateTotals() {
    let total = 0;
    $('tbody tr').each(function () {
        const amt = parseFloat($(this).find('[name="amount[]"]').val()) || 0;
        total += amt;
    });
    $('[name="total_amount"]').val(total.toFixed(2));
    $('[name="f_total_amount"]').val(total.toFixed(2));
}

$(document).ready(function () {
    $('tbody tr').each(function () {
        const row = $(this);
        row.find('[name="qty1[]"], [name="mrp[]"], [name="gst[]"]').on('input', function () {
            calculateAmount(row);
            updateTotals();
        });
    });

    $('#supplierInput').on('keyup', function () {
        const query = $(this).val();
        if (query !== '') {
            $.post('search_supplier.php', { query }, function (data) {
                $('#supplierList').html(data).removeClass('d-none');
            });
        } else {
            $('#supplierList').addClass('d-none');
        }
    });

    $(document).on('click', '.supplier-item', function () {
        const name = $(this).text();
        const address = $(this).data('address');
        const mobile = $(this).data('mobile');
        $('#supplierInput').val(name);
        $('#addressInput').val(address);
        $('#mobileInput').val(mobile);
        $('#supplierList').addClass('d-none');
    });

    $(document).on('keyup', '.productInput', function () {
        const input = $(this);
        const query = input.val();
        const suggestionBox = input.siblings('.productList');

        if (query !== '') {
            $.post('search_medicine.php', { query }, function (data) {
                suggestionBox.html(data).removeClass('d-none');
            });
        } else {
            suggestionBox.addClass('d-none');
        }
    });

    $(document).on('click', '.product-item', function () {
        const name = $(this).text();
        $(this).closest('td').find('.productInput').val(name);
        $(this).parent().addClass('d-none');
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('#supplierInput, #supplierList').length) {
            $('#supplierList').addClass('d-none');
        }
        $('.productList').each(function () {
            if (!$(e.target).closest('.productInput, .productList').length) {
                $(this).addClass('d-none');
            }
        });
    });
});
</script>
</body>
</html>
