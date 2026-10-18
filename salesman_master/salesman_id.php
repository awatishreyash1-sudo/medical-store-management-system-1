<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "medical_shop";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-generate salesman code
$query = "SELECT COUNT(id) AS total FROM salesman";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_array($result);
$a = $row['total'];
$b = "SM" . str_pad($a + 1, 3, "0", STR_PAD_LEFT);  // SM001, SM002, etc.

// Handle form submission
if (isset($_POST['add'])) {
    $role = trim($_POST['role']);
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);
    $code = trim($_POST['code']); // for salesman only

    if ($role === 'admin') {
        // Check if an admin already exists
        $check = $conn->query("SELECT * FROM users WHERE role = 'admin'");
        if ($check->num_rows > 0) {
            echo "<script>alert('Admin already exists.');</script>";
        } else {
            // Insert only into users (admin has no code)
            $stmt = $conn->prepare("INSERT INTO users (name, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $password, $role);

            if ($stmt->execute()) {
                echo "<script>alert('Admin account added successfully.');</script>";
            } else {
                echo "<script>alert('Failed to add admin: " . addslashes($stmt->error) . "');</script>";
            }

            $stmt->close();
        }
    } elseif ($role === 'salesman') {
        $address = trim($_POST['address']);
        $mobile = trim($_POST['mobile']);

        // Insert into salesman table
        $stmt1 = $conn->prepare("INSERT INTO salesman (code, name, address, password, mobile) VALUES (?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssss", $code, $name, $address, $password, $mobile);

        if ($stmt1->execute()) {
            // Also insert into users (include code here)
            $stmt2 = $conn->prepare("INSERT INTO users (name, password, role, code) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("ssss", $name, $password, $role, $code);

            if ($stmt2->execute()) {
                echo "<script>alert('Salesman and user account added successfully.');</script>";
            } else {
                echo "<script>alert('Salesman saved but user account failed: " . addslashes($stmt2->error) . "');</script>";
            }

            $stmt2->close();
        } else {
            echo "<script>alert('Salesman not saved: " . addslashes($stmt1->error) . "');</script>";
        }

        $stmt1->close();
    } else {
        echo "<script>alert('Invalid role selected.');</script>";
    }
}

session_start(); // <-- REQUIRED to use $_SESSION

// Redirect to login if not logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salesman Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../nav.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="icon" type="image/jpeg" href="image/logo.jpeg">

    <style>
        body {
            background: #f4f6f9;
        }
        .card {
            max-width: 500px;
            width: 100%;
            margin: auto;
            margin-top: 50px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            border-radius: 15px 15px 0 0;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'navbar5.php'; ?>
        <div class="col-md-10 offset-md-1 d-flex justify-content-center align-items-center" style="min-height: 90vh;">
            <div class="card bg-white">
                <div class="card-header">
                    <h3 class="mb-0"><i class="bi bi-plus-circle-dotted"></i> Add Sales Man</h3>
                </div>
                <div class="card-body p-4">

               <form method="POST" onsubmit="return validateForm()">

    <!-- Role -->
    <div class="mb-3">
        <label for="role" class="form-label">
            Role <small>(admin / salesman)</small>
        </label>
        <select class="form-select"
                name="role"
                id="role"
                required>
            <option value="">-- Select Role --</option>
            <option value="admin">Admin</option>
            <option value="salesman">Salesman</option>
        </select>
    </div>

    <!-- Code (For Salesman) -->
    <div class="mb-3" id="codeField">
        <label for="code" class="form-label">
            Id (For Salesman)
        </label>
        <input type="text"
               class="form-control"
               name="code"
               id="code"
               value="<?= $b ?>"
               readonly>
    </div>

    <!-- Name -->
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text"
               class="form-control"
               name="name"
               id="name"
               required
               minlength="3"
               pattern="[A-Za-z ]+"
               title="Only letters allowed">
    </div>

    <!-- Address -->
    <div class="mb-3">
        <label for="address" class="form-label">
            Address (For Salesman)
        </label>
        <input type="text"
               class="form-control"
               name="address"
               id="address"
               minlength="5">
    </div>

    <!-- Mobile -->
    <div class="mb-3">
        <label for="mobile" class="form-label">
            Mobile (For Salesman)
        </label>
        <input type="text"
               class="form-control"
               name="mobile"
               id="mobile"
               maxlength="10"
               pattern="[0-9]{10}"
               title="Enter 10 digit mobile number">
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">
            Password
        </label>
        <input type="password"
               class="form-control"
               name="password"
               id="password"
               required
               pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}"
               title="Password must contain 8 characters, uppercase, lowercase, number and special character">
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="confirm_password" class="form-label">
            Confirm Password
        </label>
        <input type="password"
               class="form-control"
               name="confirm_password"
               id="confirm_password"
               required>
    </div>

    <div class="d-grid">
        <button type="submit"
                name="add"
                class="btn btn-primary">
            <i class="bi bi-check-circle"></i>
            Add User
        </button>
    </div>

</form>

<script>

function validateForm() {

    let role = document.getElementById("role").value;
    let name = document.getElementById("name").value;
    let address = document.getElementById("address").value;
    let mobile = document.getElementById("mobile").value;
    let password = document.getElementById("password").value;
    let confirmPassword =
        document.getElementById("confirm_password").value;

    // Role validation
    if (role === "") {
        alert("Please select role");
        return false;
    }

    // Name validation
    if (name.length < 3) {
        alert("Name must be at least 3 characters");
        return false;
    }

    // If role is salesman → address & mobile required
    if (role === "salesman") {

        if (address.length < 5) {
            alert("Address is required for salesman");
            return false;
        }

        if (mobile.length != 10) {
            alert("Mobile must be 10 digits");
            return false;
        }
    }

    // Strong password validation
    let strongPassword =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

    if (!strongPassword.test(password)) {
        alert(
        "Password must be at least 8 characters and include uppercase, lowercase, number and special character"
        );
        return false;
    }

    // Confirm password match
    if (password !== confirmPassword) {
        alert("Password and Confirm Password do not match");
        return false;
    }

    return true;
}

// Hide code field if role is admin
document.getElementById("role").addEventListener("change", function() {

    let role = this.value;
    let codeField = document.getElementById("codeField");

    if (role === "salesman") {
        codeField.style.display = "block";
    } else {
        codeField.style.display = "none";
    }

});

</script>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
