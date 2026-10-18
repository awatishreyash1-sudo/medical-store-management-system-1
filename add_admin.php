<<<<<<< HEAD
<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

if (isset($_POST['add_admin'])) {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);

    // Check if admin already exists
    $check = $conn->query("SELECT * FROM users WHERE role = 'admin'");

    if ($check->num_rows > 0) {
        $message = "Admin already exists!";
    } else {

        $stmt = $conn->prepare("INSERT INTO users (name, password, role) VALUES (?, ?, 'admin')");
        $stmt->bind_param("ss", $name, $password);

        if ($stmt->execute()) {
            $message = "Admin added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:linear-gradient(to right,#0f2027,#203a43,#2c5364);height:100vh;display:flex;align-items:center;justify-content:center;">

<div class="card p-4 shadow" style="width:400px;border-radius:20px;">
    
    <h4 class="text-center text-danger mb-3">Add Admin Account</h4>

    <?php if($message != ""): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <input type="text" name="name" class="form-control" placeholder="Admin Username" required>
        </div>

        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Admin Password" required>
        </div>

        <button type="submit" name="add_admin" class="btn btn-danger w-100">
            Add Admin
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="login.php" style="text-decoration:none;">Back to Login</a>
    </div>

</div>

</body>
=======
<?php
$conn = new mysqli("localhost", "root", "", "medical_shop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

if (isset($_POST['add_admin'])) {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);

    // Check if admin already exists
    $check = $conn->query("SELECT * FROM users WHERE role = 'admin'");

    if ($check->num_rows > 0) {
        $message = "Admin already exists!";
    } else {

        $stmt = $conn->prepare("INSERT INTO users (name, password, role) VALUES (?, ?, 'admin')");
        $stmt->bind_param("ss", $name, $password);

        if ($stmt->execute()) {
            $message = "Admin added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:linear-gradient(to right,#0f2027,#203a43,#2c5364);height:100vh;display:flex;align-items:center;justify-content:center;">

<div class="card p-4 shadow" style="width:400px;border-radius:20px;">
    
    <h4 class="text-center text-danger mb-3">Add Admin Account</h4>

    <?php if($message != ""): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <input type="text" name="name" class="form-control" placeholder="Admin Username" required>
        </div>

        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Admin Password" required>
        </div>

        <button type="submit" name="add_admin" class="btn btn-danger w-100">
            Add Admin
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="login.php" style="text-decoration:none;">Back to Login</a>
    </div>

</div>

</body>
>>>>>>> b7c4f8c090d039df274d0df3f0007f15341c6d9f
</html>