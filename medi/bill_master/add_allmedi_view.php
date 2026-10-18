<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "medical_shop";

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$start_date = $end_date = "";
$filter_query = "SELECT b.id AS bill_id, b.datetime, b.s_name, b.city, b.mobile, 
                        m.product, m.batch, m.qty1, m.mrp, m.gst, m.ex_date 
                 FROM ss_bills b 
                 JOIN ss_medicine m ON b.id = m.bill_id 
                 WHERE 1=1";

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    if (!empty($start_date) && !empty($end_date)) {
        $filter_query .= " AND DATE(b.datetime) BETWEEN '$start_date' AND '$end_date'";
    }
}

$filter_query .= " ORDER BY b.datetime DESC, b.id DESC";
$data = mysqli_query($conn, $filter_query);

if (!$data) {
    die("Query Error: " . mysqli_error($conn));
}

$grouped_data = [];
while ($row = mysqli_fetch_assoc($data)) {
    $key = $row['datetime'] . '_' . $row['s_name'];
    $grouped_data[$key]['supplier'] = [
        'name' => $row['s_name'],
        'city' => $row['city'],
        'mobile' => $row['mobile'],
        'datetime' => $row['datetime']
    ];
    $grouped_data[$key]['medicines'][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Bill Report</title>
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../nav.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            max-width:1000px;
            margin-top: 30px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            left:120px;
        }
        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: #fff;
            padding: 20px;
            font-weight: bold;
        }
        table th {
            background-color: #1d3557;
            color: #fff;
        }
        table td, table th {
            font-size: 14px;
            vertical-align: middle;
            text-align: center;
        }
        .btn-print {
            margin: 10px;
        }
        @media print {
            .btn, .form-label, form, .btn-print, nav, .print-hide { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar7.php'; ?>

        <div class="container">
            <div class="row justify-content-center mt-4 mb-4">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-header text-center">
                            <h3><i class="bi bi-receipt-cutoff me-2"></i> Purchase Bill Report</h3>
                        </div>
                        <div class="card-body">
                            <form class="row g-3 mb-4" method="GET">
                                <div class="col-md-4">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="form-control" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> Search</button>
                                    <a href="add_allmedi_view.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Clear</a>
                                </div>
                            </form>
    </div>
    </div>

                            <div class="text-center mb-4">
                                <h4><strong>Medical Store</strong></h4>
                                <p>Shop No. 101, Main Bazar, Dudhagon -416 315</p>
                                <p>GSTIN: 27ABCDE1234F1Z5 | Phone: 9876543210</p>
                            </div>

                            <!-- START PRINT AREA -->
                            <div id="print-area">
                            <?php
                            $sn = 1;
                            $grand_total = 0;
                            foreach ($grouped_data as $key => $entry):
                                $supplier = $entry['supplier'];
                                $medicines = $entry['medicines'];
                                $section_total = 0;
                                $print_id = "print-section-" . md5($key);
                            ?>
                            <div class="card mb-4 shadow">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>
                                        Supplier: <?= htmlspecialchars($supplier['name']) ?> |
                                        City: <?= htmlspecialchars($supplier['city']) ?> |
                                        Mobile: <?= htmlspecialchars($supplier['mobile']) ?> |
                                        Date: <?= date("d-m-Y", strtotime($supplier['datetime'])) ?>
                                    </span>
                                    <button class="btn btn-dark btn-print" onclick="printSection('<?= $print_id ?>')">
                                        <i class="bi bi-printer-fill me-1"></i> Print
                                    </button>
                                </div>


                                <div class="card-body p-0" id="<?= $print_id ?>">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Medicine</th>
                                                <th>Batch</th>
                                                <th>Qty</th>
                                                <th>MRP</th>
                                                <th>GST%</th>
                                                <th>Amount</th>
                                                <th>Expiry</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($medicines as $med): 
                                                $amount = $med['qty1'] * $med['mrp'];
                                                $section_total += $amount;
                                                $grand_total += $amount;
                                            ?>
                                            <tr>
                                                <td><?= $sn++ ?></td>
                                                <td><?= htmlspecialchars($med['product']) ?></td>
                                                <td><?= htmlspecialchars($med['batch']) ?></td>
                                                <td><?= $med['qty1'] ?></td>
                                                <td>₹<?= number_format($med['mrp'], 2) ?></td>
                                                <td><?= $med['gst'] ?>%</td>
                                                <td>₹<?= number_format($amount, 2) ?></td>
                                                <td><?= htmlspecialchars($med['ex_date']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <div class="text-end mt-2 me-3 mb-2">
                                        <strong>Total Amount: ₹<?= number_format($section_total, 2) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <div class="text-end mt-3">
                                <h5 class="fw-bold">Grand Total: ₹<?= number_format($grand_total, 2) ?></h5>
                            </div>

                            <div class="text-center mt-3">
                                <p><strong>Thank you for your purchase!</strong></p>
                                <p>Printed on: <?= date("d-m-Y h:i A") ?></p>
                            </div>
                            </div>
                            <!-- END PRINT AREA -->

                            <div class="text-end mt-3">
                                <button onclick="printRecords()" class="btn btn-dark btn-print">
                                    <i class="bi bi-printer-fill me-1"></i> Print Bill
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                                            </div>

<script>
function printSection(id) {
    const printContent = document.getElementById(id).closest('.card').innerHTML;
    const win = window.open('', '', 'width=900,height=700');
    win.document.write(`
        <html>
        <head>
            <title>Print</title>
            <style>
                body { font-family: 'Segoe UI', sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 8px; text-align: center; }
                th { background-color: #1d3557; color: white; }
                h4, p { margin: 4px 0; text-align: center; }
                .text-end { text-align: right; margin-top: 10px; margin-right: 20px; }
                .card-header {
                    font-weight: bold;
                    background-color: #138496;
                    color: white;
                    padding: 10px;
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
            <h4>Medical Store</h4>
            <p>Shop No. 101, Main Bazar, Dudhagon -416 315</p>
            <p>GSTIN: 27ABCDE1234F1Z5 | Phone: 9876543210</p>
            <h4>Purchase Bill</h4>
            
            ${printContent}
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
    win.print();
    win.close();
}

function printRecords() {
    const printContent = document.getElementById("print-area").innerHTML;
    const win = window.open('', '', 'width=1000,height=700');
    win.document.write(`
        <html>
        <head>
            <title>Print</title>
            <style>
                body {
                    font-family: 'Segoe UI', sans-serif;
                    background: #f5f7fa;
                }
                .card-header {
                    background: linear-gradient(45deg, #17a2b8, #138496);
                    color: #fff;
                    padding: 15px;
                    font-size: 16px;
                    font-weight: bold;
                    border-radius: 15px 15px 0 0;
                }
                .card-body {
                    padding: 15px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 0;
                }
                th {
                    background-color: #1d3557;
                    color: #fff;
                    font-size: 14px;
                    padding: 8px;
                    border: 1px solid #dee2e6;
                }
                td {
                    font-size: 14px;
                    padding: 8px;
                    border: 1px solid #dee2e6;
                    text-align: center;
                }
                .text-center { text-align: center; }
                .text-end { text-align: right; margin-right: 20px; }
                .fw-bold { font-weight: bold; }
                h4, p { margin: 4px 0; }
                .table-light { background-color: #f8f9fa; }
            </style>
        </head>
        <body>
         <div class="text-center mb-4">
                                <h4><strong>Medical Store</strong></h4>
                                <p>Shop No. 101, Main Bazar, Dudhagon -416 315</p>
                                <p>GSTIN: 27ABCDE1234F1Z5 | Phone: 9876543210</p>
                            </div>
            ${printContent}
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
    win.print();
    win.close();
}
</script>

</body>
</html>
