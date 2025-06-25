<?php
require_once "sales_db.php";

$saleId = $_POST['saleId'];
$SN = $_POST['SN'];

$query = "SELECT WARRANTY FROM SALES WHERE SALE_ID = ? AND SN = ?";
$stmt = $dbconnect->prepare($query);
$stmt->bind_param("ss", $saleId, $SN);
$stmt->execute();
$result = $stmt->get_result();
$data = array();
if ($row = $result->fetch_assoc()) {
    $data['warranty'] = $row['WARRANTY'];
} else {
    $data['warranty'] = "Warranty not found";
}
$stmt->close();
echo json_encode($data);
?>
