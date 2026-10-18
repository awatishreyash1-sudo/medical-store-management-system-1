<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "medical_shop";

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($conn, $_POST['query']);
    $query = "SELECT DISTINCT s_name FROM ss_bills WHERE s_name LIKE '%$search%' LIMIT 5";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<a href="#" class="list-group-item list-group-item-action suggestion-item">' . htmlspecialchars($row['s_name']) . '</a>';
        }
    } else {
        echo '<div class="list-group-item disabled">No supplier found</div>';
    }
}
?>
