
<?php
session_start();

if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "medical_shop");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$current_name = $_SESSION['name'];
$current_role = $_SESSION['role'];
$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_name = trim($_POST['new_name'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $target_user = ($current_role === 'admin')
        ? trim($_POST['target_user'] ?? '')
        : $current_name;

    if ($new_name === '' || $new_password === '' || $confirm_password === '') {
        $message = "Please fill in all required fields.";

    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirm password do not match.";

    } else {

        $conn->begin_transaction();

        try {
            // Verify target user exists
            $stmt = $conn->prepare(
                "SELECT name FROM users WHERE name = ?"
            );
            $stmt->bind_param("s", $target_user);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Target user was not found.");
            }

            $stmt->close();

            // Salesman must verify current password
            if ($current_role !== 'admin') {

                $current_password = $_POST['current_password'] ?? '';

                $stmt = $conn->prepare(
                    "SELECT id FROM users
                     WHERE name = ? AND password = ?"
                );
                $stmt->bind_param(
                    "ss",
                    $current_name,
                    $current_password
                );
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception("Current password is incorrect.");
                }

                $stmt->close();
            }

            // Update user credentials
            $stmt = $conn->prepare(
                "UPDATE users SET name = ?, password = ?
                 WHERE name = ?"
            );
            $stmt->bind_param(
                "sss",
                $new_name,
                $new_password,
                $target_user
            );
            $stmt->execute();
            $stmt->close();

            // Update matching salesman record, if present
            $stmt = $conn->prepare(
                "UPDATE salesman SET name = ?, password = ?
                 WHERE name = ?"
            );
            $stmt->bind_param(
                "sss",
                $new_name,
                $new_password,
                $target_user
            );
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            if (
                $current_role !== 'admin' ||
                $target_user === $current_name
            ) {
                $_SESSION['name'] = $new_name;
            }

            $message = "Credentials updated successfully!";
            $success = true;

        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Change Username and Password</title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Eczar">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Eczar", sans-serif;
            background: linear-gradient(135deg, #dfe9f3, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-group {
            position: relative;
            margin-bottom: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 40px 12px 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        .form-group i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }

        button {
            width: 100%;
            padding: 11px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            background: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .back-button {
            background: #6c757d;
        }

        .back-button:hover {
            background: #5a6268;
        }

        .message {
            text-align: center;
            color: green;
        }

        .error {
            text-align: center;
            color: red;
        }
    </style>
</head>

<body>

<div class="form-container">

    <h2>
        <i class="bi bi-shield-lock-fill"></i>
        Change Credentials
    </h2>

    <?php if ($message !== ''): ?>
        <p class="<?= $success ? 'message' : 'error' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <?php if ($current_role === 'admin'): ?>
            <div class="form-group">
                <input
                    type="text"
                    name="target_user"
                    placeholder="Existing Username"
                    required
                >
                <i class="bi bi-person-fill"></i>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <input
                type="text"
                name="new_name"
                placeholder="New Username"
                required
            >
            <i class="bi bi-person-fill"></i>
        </div>

        <?php if ($current_role !== 'admin'): ?>
            <div class="form-group">
                <input
                    type="password"
                    name="current_password"
                    placeholder="Current Password"
                    required
                >
                <i class="bi bi-lock-fill"></i>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <input
                type="password"
                name="new_password"
                placeholder="New Password"
                required
            >
            <i class="bi bi-shield-lock"></i>
        </div>

        <div class="form-group">
            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm New Password"
                required
            >
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <button type="submit">
            <i class="bi bi-arrow-repeat"></i>
            Update Credentials
        </button>

    </form>

    <form action="index.php" method="get">
        <button type="submit" class="back-button">
            <i class="bi bi-arrow-left-circle-fill"></i>
            Back
        </button>
    </form>

</div>

</body>
</html>
