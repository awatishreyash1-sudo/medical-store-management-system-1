
<?php
$conn = new mysqli('localhost', 'root', '', 'medical_shop');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
