
<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medical_shop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Patient Medicine Records</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .hidden {
            display: none;
        }

        .container {
            max-width: 900px;
        }

        .topbar {
            bottom: 30px;
        }
    </style>
</head>

<body>

    <?php include 'index_navbar.php'; ?>

</body>

</html>

<?php
$conn->close();
?>
