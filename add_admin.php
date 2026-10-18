
<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$message = "";
$message_type = "info";

if (isset($_POST['add_admin'])) {

    $name = trim($_POST['name'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name === "" || $password === "") {

        $message = "Please enter username and password.";
        $message_type = "danger";

    } else {

        // Check whether an admin already exists
        $check = $conn->query(
            "SELECT id FROM users WHERE role = 'admin' LIMIT 1"
        );

        if (!$check) {

            $message = "Error checking admin: " . $conn->error;
            $message_type = "danger";

        } elseif ($check->num_rows > 0) {

            $message = "Admin already exists!";
            $message_type = "warning";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO users (name, password, role)
                 VALUES (?, ?, 'admin')"
            );

            if ($stmt) {

                $stmt->bind_param("ss", $name, $password);

                if ($stmt->execute()) {
                    $message = "Admin added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error adding admin: " . $stmt->error;
                    $message_type = "danger";
                }

                $stmt->close();

            } else {

                $message = "Database query error: " . $conn->error;
                $message_type = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: linear-gradient(
                to right,
                #0f2027,
                #203a43,
                #2c5364
            );
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .admin-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 20px;
            padding: 30px;
        }

        .admin-card h4 {
            font-weight: bold;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
        }

        .btn-admin {
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
        }

        .back-link {
            text-decoration: none;
            color: #0d6efd;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="card admin-card shadow-lg">

        <h4 class="text-center text-danger mb-4">
            Add Admin Account
        </h4>

        <?php if ($message !== ""): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label for="name" class="form-label">
                    Admin Username
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="Enter admin username"
                    required
                    maxlength="100"
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    Admin Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter admin password"
                    required
                >
            </div>

            <button
                type="submit"
                name="add_admin"
                class="btn btn-danger btn-admin w-100"
            >
                Add Admin
            </button>

        </form>

        <div class="text-center mt-4">
            <a href="login.php" class="back-link">
                &larr; Back to Login
            </a>
        </div>

    </div>

</body>

</html>
