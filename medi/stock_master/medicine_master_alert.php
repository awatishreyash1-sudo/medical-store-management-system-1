
<?php 
session_start(); // <-- REQUIRED to use $_SESSION

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}


$conn = new mysqli("localhost", "root", "", "medical_shop");
include '../medicine_master/medi_master_view.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medicine Stock & Alerts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">
    <style>
        .custom-card {
            left :200px;
            width: 85%;
            max-width: 1100px;
            margin: auto;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            background-color: #ffffff;
            transition: all 0.3s ease;
        }
        .card-header {
            background: linear-gradient(135deg, #17a2b8, #138496);
            padding: 20px;
            color: #fff;
            font-size: 1.6rem;
            border-radius: 20px 20px 0 0;
        }
        .table th {
            background-color: #343a40;
            color: white;
            font-size: 14px;
        }
        .table td {
            font-size: 14px;
        }
        .btn-secondary {
            background-color: #17a2b8;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #138496;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'navbar4.php'; ?>

        <div class="col-md-10 p-5">
            <div class="card shadow-sm custom-card">
                <div class="card-header text-center">
                    <h3><i class="bi bi-pencil-square"></i>Expiry Medicine</h3>
                </div>
                <div class="card-body">

                    <!-- Expiry Alert Table -->
                    <?php
                    $alerts = [];
                    if ($data === false) {
                        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
                    } elseif ($total > 0) {
                        while ($row = mysqli_fetch_assoc($data)) {
                            $stmt = $conn->prepare("SELECT ex_date, qty FROM s_medicin WHERE product = ?");
                            $stmt->bind_param("s", $row['medicine_name']);
                            $stmt->execute();
                            $res = $stmt->get_result();

                            while ($ex = $res->fetch_assoc()) {
                                $expiry_date = $ex['ex_date'];
                                $expiry_timestamp = strtotime($expiry_date);
                                $today = strtotime(date('Y-m-d'));
                                $diff = round(($expiry_timestamp - $today) / (60 * 60 * 24));

                                if ($diff <= 5 && $diff >= 0) {
                                    $alerts[] = "Medicine: " . $row['medicine_name'] . " - Expiry on: " . $expiry_date . " (" . $diff . " days left)";
                                }
                            }
                            $stmt->close();
                        }
                        mysqli_data_seek($data, 0); // Reset pointer
                    }

                    if (!empty($alerts)): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5><i class="bi bi-exclamation-triangle"></i> Expiry Alerts</h5>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm m-0">
                                        <thead class="table-dark">
                                        <tr>
                                            <th>Medicine Name</th>
                                            <th>Supplier</th>
                                            <th>Batch No</th>
                                            <th>Expiry Date</th>
                                            <th>Purchase Date</th>
                                            <th>Days Left</th>
                                            
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($alerts as $alert):
                                            preg_match('/Medicine: (.*?) - Expiry on: (.*?) \((\d+) days left\)/', $alert, $matches);
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($matches[1] ?? ''); ?></td>
                                                <td></td>
                                                <td></td>
                                                <td><?php echo htmlspecialchars($matches[2] ?? ''); ?></td>
                                               
                                                <td></td>
                                                <td class="fw-bold text-danger"><?php echo htmlspecialchars($matches[3] ?? ''); ?> Days</td>
                                                
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                                        </div>
                    <?php endif; ?>

                    <!-- Medicine Master Table -->
                     <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5> Expiry medicine</h5>
                            </div>

                     <div class="card-body p-0">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Company</th>
                                <th>Stock</th>
                                <th>Expiry Dates</th>
                                <th>Qty (Per Batch)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if ($total > 0) {
                                while ($row = mysqli_fetch_assoc($data)) {
                                    $stockClass = ($row['stock'] <= 10) ? 'text-danger fw-bold' : '';
                                    echo "<tr>";
                                    echo "<td>".htmlspecialchars($row['medicine_name'])."</td>";
                                    echo "<td>".htmlspecialchars($row['company_name'])."</td>";
                                    echo "<td class='{$stockClass}'>".htmlspecialchars($row['stock'])."</td>";

                                    $stmt = $conn->prepare("SELECT ex_date, qty FROM s_medicin WHERE product = ?");
                                    $stmt->bind_param("s", $row['medicine_name']);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    $expiries = [];
                                    $quantities = [];
                                    while ($ex = $res->fetch_assoc()) {
                                        $expiry_date = $ex['ex_date'];
                                        $expiry_timestamp = strtotime($expiry_date);
                                        $today = strtotime(date('Y-m-d'));
                                        $diff = round(($expiry_timestamp - $today) / (60 * 60 * 24));

                                        $expiryStyle = ($diff <= 5 && $diff >= 0) ? "text-danger fw-bold" : "";

                                        $expiries[] = "<div class='{$expiryStyle}'>" . htmlspecialchars($expiry_date) . "</div>";
                                        $quantities[] = htmlspecialchars($ex['qty']);
                                    }
                                    $stmt->close();

                                    echo "<td>" . (!empty($expiries) ? implode("", $expiries) : "<span class='text-muted'>No expiry</span>") . "</td>";
                                    echo "<td>" . (!empty($quantities) ? implode("<br>", $quantities) : "<span class='text-muted'>-</span>") . "</td>";

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No records found</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" onclick="printRecords()" class="btn btn-secondary px-4">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printRecords() {
    const header = `<div style="text-align:center;margin-bottom:20px;">
                        <h2 style="color:#2eb8b8;">Medicine Master Report</h2>
                    </div>`;
    const content = document.querySelector('.table-responsive').innerHTML;
    const printWindow = window.open('', '', 'width=1000,height=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(header);
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

// Popup Alert
<?php
if (!empty($alerts)) {
    $alertMsg = implode("\\n", $alerts);
    echo "window.onload = function() { alert('Expiry Alerts:\\n$alertMsg'); };";
}
?>
</script>

</body>
</html>
