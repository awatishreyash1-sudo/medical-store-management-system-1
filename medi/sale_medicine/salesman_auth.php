<?php
function getSalesmanName($conn, $password) {
    $stmt = $conn->prepare("SELECT name FROM salesman WHERE password = ?");
    $stmt->bind_param("s", $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $salesman = $result->fetch_assoc();
    $stmt->close();

    return $salesman ? $salesman['name'] : false;
}
?>
