<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Purchase Return</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../nav.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .card { margin-top: 30px; border-radius: 15px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08); overflow: hidden; left:120px; }
        .card-header { background: linear-gradient(45deg, #dc3545, #c82333); color: #fff; padding: 20px; }
        table th { background-color: #6c757d; color: #fff; }
        table td { font-size: 14px; }
        @media print {
            .no-print, .navbar, footer { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
    <?php include 'navbar7.php'; ?>

    <div class="row justify-content-center mt-4 mb-4">
        <div class="col-lg-10">
            <div class="card shadow rounded-4">
                <div class="card-header text-white text-center">
                    <h4><i class="bi bi-arrow-counterclockwise"></i> Purchase Return Report</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" required>
                        </div>
                        <div class="col-md-4 align-self-end">
                            <button type="submit" class="btn btn-danger">Search</button>
                        </div>
                    </form>
                </div>
            </div>


            <div class="text-center mb-4">
                                <h4><strong>Medical Store</strong></h4>
                                <p>Shop No. 101, Main Bazar, Dudhagon -416 315</p>
                                <p>GSTIN: 27ABCDE1234F1Z5 | Phone: 9876543210</p>
                            </div>

            <div id="print-area">
            <?php
            $grand_total_with_gst = 0;
            ?>

            <?php if ($start_date && $end_date): ?>
                <?php
                $stmt = $conn->prepare("
                    SELECT 
                        b.id AS bill_id,
                        b.s_name AS supplier_name,
                        b.mobile,
                        b.address,
                        r.return_date
                    FROM 
                        purchase_return_medicine r
                    JOIN 
                        ss_bills b ON r.bill_id = b.id
                    WHERE 
                        DATE(r.return_date) BETWEEN ? AND ?
                    GROUP BY 
                        r.bill_id, r.return_date
                    ORDER BY 
                        r.return_date DESC
                ");
                $stmt->bind_param("ss", $start_date, $end_date);
                $stmt->execute();
                $returns = $stmt->get_result();

                if ($returns->num_rows > 0): ?>
                    <?php $counter = 1; ?>
<?php while ($row = $returns->fetch_assoc()): ?>
    <?php $print_id = 'return_card_' . $counter++; ?>
    <div class="card my-4" id="<?= $print_id ?>">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>
                Return Bill ID: <?= $row['bill_id'] ?> |
                Supplier: <?= $row['supplier_name'] ?> |
                Return Date: <?= $row['return_date'] ?>
            </strong>
            <button class="btn btn-dark btn-print" onclick="printSection('<?= $print_id ?>')">
                <i class="bi bi-printer-fill me-1"></i> Print
            </button>
        </div>

                            <div class="card-body p-0">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Sr No.</th>
                                            <th>Medicine</th>
                                            <th>Batch</th>
                                            <th>GST%</th>
                                            <th>MRP</th>
                                            <th>Expiry</th>
                                            <th>Return Qty</th>
                                            <th>Return Amount</th>
                                            <th>GST Amount</th>
                                            <th>Total (with GST)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $bill_id = $row['bill_id'];
                                        $return_date = $row['return_date'];

                                        $med_stmt = $conn->prepare("
                                            SELECT * FROM purchase_return_medicine 
                                            WHERE bill_id = ? AND return_date = ?
                                        ");
                                        $med_stmt->bind_param("is", $bill_id, $return_date);
                                        $med_stmt->execute();
                                        $meds = $med_stmt->get_result();

                                        $sr = 1;
                                        $bill_total_with_gst = 0;
                                        while ($med = $meds->fetch_assoc()):
                                            $gst_amount = ($med['return_amount'] * $med['gst']) / (100 + $med['gst']);
                                            $total_with_gst = $med['return_amount']; // Already includes GST in return_amount
                                            $bill_total_with_gst += $total_with_gst;
                                        ?>
                                        <tr>
                                            <td><?= $sr++ ?></td>
                                            <td><?= $med['product'] ?></td>
                                            <td><?= $med['batch'] ?></td>
                                            <td><?= $med['gst'] ?>%</td>
                                            <td><?= $med['mrp'] ?></td>
                                            <td><?= $med['ex_date'] ?></td>
                                            <td><?= $med['return_qty'] ?></td>
                                            <td>₹<?= number_format($med['return_amount'], 2) ?></td>
                                            <td>₹<?= number_format($gst_amount, 2) ?></td>
                                            <td>₹<?= number_format($total_with_gst, 2) ?></td>
                                        </tr>
                                        <?php endwhile; $med_stmt->close(); ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="9" class="text-end">Total Return Amount (with GST):</th>
                                            <th>₹<?= number_format($bill_total_with_gst, 2) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <?php $grand_total_with_gst += $bill_total_with_gst; ?>
                    <?php endwhile; ?>

                    

                    <!-- Grand Total -->
                    <div class="card mb-4">
                        <div class="card-body text-end">
                            <h5 class="text-danger">Grand Total (All Returns with GST): ₹<?= number_format($grand_total_with_gst, 2) ?></h5>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert alert-warning mt-4">No purchase return entries found for the selected date range.</div>
                <?php endif; $stmt->close(); ?>
            <?php endif; ?>
            <div class="text-end mt-3">
                                <button onclick="printRecords()" class="btn btn-dark btn-print">
                                    <i class="bi bi-printer-fill me-1"></i> Print Bill
                                </button>
                            </div>

            
        </div>
    </div>
</div>

                            

<script>
function printSection(id) {
    const section = document.getElementById(id).cloneNode(true);

    // Remove print buttons inside the cloned section
    const printButtons = section.querySelectorAll('.btn-print');
    printButtons.forEach(btn => btn.remove());

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
            <h4>Purchase Return</h4>

            ${section.innerHTML}
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
    win.print();
    win.close();
}


function printRecords() {
    const section = document.getElementById("print-area").cloneNode(true);

    // Remove print buttons before printing
    const printButtons = section.querySelectorAll('.btn-print');
    printButtons.forEach(btn => btn.remove());

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
            ${section.innerHTML}
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
