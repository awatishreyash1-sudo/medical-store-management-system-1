<?php
include '../connection.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM medicine_master WHERE id = $id");
header("Location: medicine_master_view.php");
