<?php
session_start();

if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'medical_shop');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_name = $_SESSION['name'];
$current_role = $_SESSION['role'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_name = $conn->real_escape_string($_POST['new_name']);
    $new_password = $conn->real_escape_string($_POST['new_password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    // Admin target user select karu shakto
    if ($current_role === 'admin') {
        $target_user = $conn->real_escape_string($_POST['target_user']);
    } else {
        $target_user = $current_name;
    }

    // ================= ADMIN LOGIC =================
    if ($current_role === 'admin') {

        if ($new_password === $confirm_password) {

            $update_users = "UPDATE users SET name='$new_name', password='$new_password' WHERE name='$target_user'";
            if ($conn->query($update_users)) {

                $conn->query("UPDATE salesman SET name='$new_name', password='$new_password' WHERE name='$target_user'");

                if ($target_user == $current_name) {
                    $_SESSION['name'] = $new_name;
                }

                $message = "Password updated successfully by Admin!";
            } else {
                $message = "Error updating: " . $conn->error;
            }

        } else {
            $message = "New password and confirm password do not match.";
        }
    }

    // ================= SALESMAN LOGIC =================
    else {

        $current_password = $conn->real_escape_string($_POST['current_password']);

        $check = "SELECT * FROM users WHERE name='$current_name' AND password='$current_password'";
        $result = $conn->query($check);

        if ($result && $result->num_rows > 0) {

            if ($new_password === $confirm_password) {

                $conn->query("UPDATE users SET name='$new_name', password='$new_password' WHERE name='$current_name'");
                $conn->query("UPDATE salesman SET name='$new_name', password='$new_password' WHERE name='$current_name'");

                $_SESSION['name'] = $new_name;
                $message = "Password updated successfully!";
            } else {
                $message = "New password and confirm password do not match.";
            }

        } else {
            $message = "Current password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Username and Password</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Eczar">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: "Eczar", sans-serif;
            background: linear-gradient(135deg, #dfe9f3, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            width: 380px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .form-container input {
            width: 85%;
            padding: 10px 40px 10px 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .form-group {
            position: relative;
        }
        .form-group i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            border: none;
            border-radius: 8px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .back-button {
            background-color: #6c757d !important;
        }
        .back-button:hover {
            background-color: #5a6268 !important;
        }
        .message { text-align: center; color: green; }
        .error { text-align: center; color: red; }
    </style>
</head>
<body>

<div class="form-container">
    <h2><i class="bi bi-shield-lock-fill text-primary"></i> Change Credentials</h2>

    <?php if (!empty($message)) : ?>
        <p class="<?= strpos($message, 'successfully') !== false ? 'message' : 'error' ?>">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <?php if($_SESSION['role'] == 'admin'): ?>
            <div class="form-group">
                <input type="text" name="target_user" placeholder="Enter Username (Admin or Salesman)" required>
                <i class="bi bi-person-fill"></i>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <input type="text" name="new_name" placeholder="New Username" required>
            <i class="bi bi-person-fill"></i>
        </div>

        <?php if($_SESSION['role'] != 'admin'): ?>
            <div class="form-group">
                <input type="password" name="current_password" placeholder="Current Password" required>
                <i class="bi bi-lock-fill"></i>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <input type="password" name="new_password" placeholder="New Password" required>
            <i class="bi bi-shield-lock"></i>
        </div>

        <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <button type="submit">
            <i class="bi bi-arrow-repeat"></i> Update
        </button>
    </form>

    <form action="index.php" method="get">
        <button type="submit" class="back-button">
            <i class="bi bi-arrow-left-circle-fill"></i> Back
        </button>
    </form>

</div>
</body>
</html>