<?php
include 'connection.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM sales_medicin WHERE id = $id");
header("Location: vsales_medi.php");
