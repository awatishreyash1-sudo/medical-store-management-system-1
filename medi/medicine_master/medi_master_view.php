<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "medical_shop";

// Database connection
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all medicines
$sql = "SELECT * FROM medicine_master";
$data = mysqli_query($conn, $sql);
$total = mysqli_num_rows($data);

// Prepare expiry alerts (0 to 5 days)
$today = date('Y-m-d');
$expiryCheck = mysqli_query($conn, "
    SELECT product, ex_date
    FROM s_medicin
    WHERE ex_date BETWEEN '$today' AND DATE_ADD('$today', INTERVAL 5 DAY)
");
$alertMedicines = [];
$expiringDates = [];
while ($row = mysqli_fetch_assoc($expiryCheck)) {
    $expiryDate = $row['ex_date'];
    $daysLeft = (strtotime($expiryDate) - strtotime($today)) / (60 * 60 * 24);
    $alertMedicines[] = "{$row['product']} expires on {$expiryDate} (in {$daysLeft} days)";
    $expiringDates[] = $expiryDate;
}

// Prepare stock alerts (stock ≤ 10)
$stockAlert = mysqli_query($conn, "SELECT medicine_name, stock FROM medicine_master WHERE stock <= 10");
$alertStocks = [];
while ($row = mysqli_fetch_assoc($stockAlert)) {
    $alertStocks[] = "{$row['medicine_name']} - Low stock: {$row['stock']}";
}
?>