
<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'medical_shop');

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $admin_name = trim($_POST['admin_name'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if ($admin_name === '' || $new_password === '') {
        $error = "Please fill in all fields.";
    } else {
        $check = $conn->prepare(
            "SELECT id FROM users WHERE name = ? AND role = 'admin'"
        );
        $check->bind_param("s", $admin_name);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            // Existing project login logic uses plain-text passwords.
            $update = $conn->prepare(
                "UPDATE users SET password = ? WHERE name = ? AND role = 'admin'"
            );
            $update->bind_param("ss", $new_password, $admin_name);

            if ($update->execute() && $update->affected_rows > 0) {
                $success = "Admin password updated successfully!";
            } else {
                $error = "Password was not changed. Please try again.";
            }

            $update->close();

        } else {
            $error = "Admin not found!";
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset</title>

    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            position: relative;
            width: 100%;
            max-width: 420px;
            background-color: #ffffffee;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .login-card::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background-image: url('image/moon.png');
            background-size: cover;
            background-position: center;
            border-radius: 50%;
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }

        .login-card h3 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: bold;
            font-size: 24px;
            color: #b8860b;
            position: relative;
            z-index: 1;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            position: relative;
            z-index: 1;
        }

        .btn-reset {
            background: #ffc107;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px;
            position: relative;
            z-index: 1;
        }

        .btn-reset:hover {
            background: #e0a800;
        }

        .login-card .alert,
        .login-card form,
        .login-card .back-link {
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>

<div class="login-card">

    <h3>
        <i class="bi bi-key-fill me-2"></i>
        Admin Password Reset
    </h3>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <input
                type="text"
                name="admin_name"
                class="form-control"
                placeholder="Enter Admin Username"
                required
            >
        </div>

        <div class="mb-3">
            <input
                type="password"
                name="new_password"
                class="form-control"
                placeholder="Enter New Password"
                required
            >
        </div>

        <div class="d-grid mt-3">
            <button type="submit" class="btn btn-reset btn-lg">
                <i class="bi bi-arrow-repeat me-1"></i>
                Reset Password
            </button>
        </div>
    </form>

    <div class="text-center mt-3 back-link">
        <a href="login.php" style="color: #17a2b8; text-decoration: none;">
            Back to Login
        </a>
    </div>

</div>

</body>
</html>
