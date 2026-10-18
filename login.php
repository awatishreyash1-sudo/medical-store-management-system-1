    <?php
    session_start();
    $conn = new mysqli('localhost', 'root', '', 'medical_shop');
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    $error = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST['name']);
        $password = trim($_POST['password']);

        $sql = "SELECT * FROM users WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($password === $row['password']) {
                $_SESSION['name'] = $row['name'];
                $_SESSION['role'] = $row['role'];
                header("Location: " . ($row['role'] == 'admin' ? "index.php" : "index.php"));
                exit;
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "User not found!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login - Medical Store</title>
        <link rel="icon" type="image/jpeg" href="image/logo.jpeg">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            body {
                background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
                height: 100vh;
                margin: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .login-card {
                position: relative;
                z-index: 1; /* 🔥 FIX */
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
                opacity: 0.28;
                z-index: 0;
                pointer-events: none; /* 🔥 VERY IMPORTANT FIX */
            }

            .login-card h3 {
                text-align: center;
                margin-bottom: 25px;
                font-weight: bold;
                font-size: 24px;
                color: #17a2b8;
                position: relative;
                z-index: 1;
            }

            .form-label, .form-control {
                position: relative;
                z-index: 1;
            }

            .form-control {
                border-radius: 10px;
            }

            .btn-login {
                background: #17a2b8;
                border: none;
                border-radius: 10px;
                font-weight: 600;
                position: relative;
                z-index: 1;
            }

            .btn-login:hover {
                background: #138496;
            }

            .error-box {
                background: #f8d7da;
                color: #842029;
                padding: 10px;
                border-radius: 10px;
                font-size: 14px;
                margin-bottom: 15px;
                position: relative;
                z-index: 1;
            }
        </style>
    </head>
    <body>

    <div class="login-card">
        <h3><i class="bi bi-shield-lock-fill me-2"></i>Medical Store Login</h3>

        <?php if (!empty($error)): ?>
            <div class="error-box">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="name" class="form-control" required placeholder="Enter your username">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter your password">
            </div>
            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-login btn-lg">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
    <a href="forgot_password.php" style="color:#17a2b8; font-size:14px; text-decoration:none;">
        Forgot Admin Password?
    </a>
</div>

<!-- 🔥 NEW LINK -->
<div class="text-center mt-2">
    <a href="add_admin.php" style="color:#dc3545; font-size:14px; text-decoration:none;">
        Add Admin Account
    </a>
</div>
    </div>

    </body>
    </html>