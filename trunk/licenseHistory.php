<?php
require_once "sales_db.php";
$saleId = $_GET['SALE_ID'];
$sn = $_GET['SN'];

$query = "SELECT * FROM LICENSE_HISTORY WHERE SALE_ID = ? AND SN = ?";
$stmt = $dbconnect->prepare($query);
$stmt->bind_param("ss", $saleId, $sn);
$stmt->execute();
$result = $stmt->get_result();

$html = "<div class='license-history'>";

while ($row = $result->fetch_assoc()) {
    $html .= "<div class='license-history-item'>";
    $html .= "<p><strong>유형:</strong> " . htmlspecialchars($row['TYPE']) . "</p>";
    $html .= "<p><strong>가격:</strong> " . htmlspecialchars(number_format($row['PRICE'])) . "</p>";
    $html .= "<p><strong>시작 날짜:</strong> " . htmlspecialchars($row['S_DATE']) . "</p>";
    $html .= "<p><strong>종료 날짜:</strong> " . htmlspecialchars($row['D_DATE']) . "</p>";
    $html .= "<p><strong>참조:</strong> " . htmlspecialchars($row['REF']) . "</p>";
    $html .= "<p><strong>보증기간:</strong> " . htmlspecialchars($row['WARRANTY']) . " 개월</p>";
    $html .= "<p><strong>점검:</strong> " . htmlspecialchars($row['INSPECTION']) . "</p>";
    $html .= "<p><strong>지원:</strong> " . htmlspecialchars($row['SUPPORT']) . "</p>";
    $html .= "<p><strong>라이센스 등록 날짜:</strong> " . htmlspecialchars($row['LICENSE_INSERTED_DATE']) . "</p>";
    $html .= "</div>"; // End of .license-history-item
}

$html .= "</div>"; // End of .license-history

echo $html;
?>