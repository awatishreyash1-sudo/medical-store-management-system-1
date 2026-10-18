<?php
// session_start();
$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'User';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

$role_display = ($role === 'admin') ? 'Admin' : (($role === 'salesman') ? 'Salesman' : 'User');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Shop Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #1f1c2c, #928dab);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
        }

        .navbar {
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(6px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            color: #fff;
            transition: 0.3s ease-in-out;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            backdrop-filter: blur(12px);
            position: relative;
        }

        .glass-card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 25px rgba(0,255,255,0.5);
            border-color: rgba(0,255,255,0.6);
        }

        .card-icon {
            font-size: 42px;
            color: #00ffe5;
        }

        .card-title {
            margin-top: 15px;
            font-weight: bold;
            font-size: 20px;
        }

        .dashboard-heading {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 40px;
            color: #00ffd5;
            text-shadow: 0 0 10px rgba(0,255,255,0.7);
        }

        a.text-decoration-none {
            text-decoration: none !important;
        }

        /* LOCKED card styles */
        .locked {
            pointer-events: none;
            opacity: 0.5;
        }

        .lock-icon-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #ff6b6b;
            font-size: 24px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand text-light" href="#">Medical Shop Admin</a>
        <div>
            <a href="logout.php" class="btn btn-outline-info">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-5">
    <h3 class="dashboard-heading text-center">Welcome <?= $role_display . ', ' . $name ?></h3>

    <div class="row g-4 justify-content-center">
        <?php
        // Cards: [Title, Link, Icon, Role ('admin' = locked for salesman)]
        $cards = [
            ['Dashboard', '#', 'bi-speedometer2'],
            ['Medicines', 'medicine_master/add_medicine_master.php', 'bi-capsule'],
            ['Purchases', 'purchase_medicine/sell.php', 'bi-cart-plus'],
            ['Sales Medicine', 'sale_medicine/sell_medicine.php', 'bi-bag-plus'],
            ['Customers', '#', 'bi-people'],
            ['Suppliers', 'supplier_master/add_supplier.php', 'bi-truck'],
            ['Salesman', 'salesman_master/salesman_id.php', 'bi-person-badge', 'admin'],  // Locked for salesman
            ['Stock', 'stock_master/medicine_master_alert.php', 'bi-box-seam'],
            ['Billing', 'bill_master/add_allmedi_view.php', 'bi-receipt'],
            ['Reports', 'report_master/purchase_report.php', 'bi-graph-up'],
            ['Profit/Loss', 'profit_losse/total_bill.php', 'bi-graph-up'],
            ['Settings', 'change_user_credentials.php', 'bi-gear'] // Shown to all
        ];

        foreach ($cards as $card) {
            $title = $card[0];
            $link = $card[1];
            $icon = $card[2];
            $restricted = $card[3] ?? null;

            // Locked = visible but disabled for salesman
            $is_locked = ($restricted === 'admin' && $role !== 'admin');
            //  $is_locked = ($restricted === 'salesman' && $role !== 'salesman');

            echo '
            <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                ' . ($is_locked ? '<div class="glass-card locked">' : '<a href="' . $link . '" class="text-decoration-none"><div class="glass-card">') . '
                    <i class="bi ' . $icon . ' card-icon"></i>
                    <h5 class="card-title">' . $title . '</h5>'
                    . ($is_locked ? '<i class="bi bi-lock-fill lock-icon-overlay"></i>' : '') . '
                </div>' . ($is_locked ? '' : '</a>') . '
            </div>';
        }
        ?>
    </div>
</div>

<div class="mt-4">
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
