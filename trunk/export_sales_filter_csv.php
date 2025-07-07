<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "auth.php";
require_once "sales_db.php";

// UTF-8 인코딩 설정
mysqli_set_charset($dbconnect, "utf8");
// Excel 한글 깨짐 방지
echo "\xEF\xBB\xBF";

// GET 파라미터 받기
$deviceSaleId = $_GET['SALE_ID'] ?? null; // deviceMain에서 넘어온 SALE_ID
$orderNo = $_GET['ORDER_NO'] ?? null;
$sn = $_GET['SN'] ?? null;
$action = $_GET['action'] ?? null;
$searchSaleId = $_GET['saleId'] ?? null; // salesSearch에서 넘어온 SALE_ID
$vName = $_GET['vName'] ?? null;
$cName = $_GET['cName'] ?? null;
$cbizName = $_GET['cbizName'] ?? null;
$bizName = $_GET['bizName'] ?? null;
$deliverDate = $_GET['deliverDate']?? null;
$sDate = $_GET['sDate'] ?? null;
$dDate = $_GET['dDate'] ?? null;
$ref = $_GET['ref'] ?? null;

$where = [];

// 단건 조회
if ($deviceSaleId) {
    $where[] = "s.SALE_ID = '" . mysqli_real_escape_string($dbconnect, $deviceSaleId) . "'";
}
if ($orderNo) {
    $where[] = "s.ORDER_NO = '" . mysqli_real_escape_string($dbconnect, $orderNo) . "'";
}
if ($sn) {
    $where[] = "s.SN = '" . mysqli_real_escape_string($dbconnect, $sn) . "'";
}

// 검색 조회
if ($action === 'search') {
    if ($searchSaleId) {
        $where[] = "s.SALE_ID LIKE '%" . mysqli_real_escape_string($dbconnect, $searchSaleId) . "%'";
    }
    if ($vName) {
        $where[] = "v.NAME LIKE '%" . mysqli_real_escape_string($dbconnect, $vName) . "%'";
    }
    if ($cName) {
        $where[] = "c.NAME LIKE '%" . mysqli_real_escape_string($dbconnect, $cName) . "%'";
    }
    if ($cbizName) {
        $where[] = "cbiz.NAME LIKE '%" . mysqli_real_escape_string($dbconnect, $cbizName) . "%'";
    }
    if ($bizName) {
        $where[] = "b.NAME LIKE '%" . mysqli_real_escape_string($dbconnect, $bizName) . "%'";
    }
    if ($deliverDate) {
        $where[] = "s.DELIVER_DATE = '" . mysqli_real_escape_string($dbconnect, $deliverDate) . "'";
    }
    if ($sDate) {
        $where[] = "s.S_DATE = '" . mysqli_real_escape_string($dbconnect, $sDate) . "'";
    }
    if ($dDate) {
        $where[] = "s.D_DATE = '" . mysqli_real_escape_string($dbconnect, $dDate) . "'";
    }
    if ($ref) {
        $where[] = "s.REF LIKE '%" . mysqli_real_escape_string($dbconnect, $ref) . "%'";
    }
}

$where_sql = '';
if (count($where) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
SELECT 
    s.SALE_ID,
    v.NAME AS V_NAME,
    c.NAME AS C_NAME,
    cbiz.NAME AS CBIZ_NAME,
    b.NAME AS BIZ_NAME,
    s.TOT_PRICE,
    s.DELIVER_DATE,
    s.S_DATE,
    s.D_DATE,
    s.ORDER_NO,
    s.WARRANTY
FROM SALES s
LEFT JOIN VENDOR v ON s.V_ID = v.V_ID
LEFT JOIN CUSTOMER c ON s.C_ID = c.C_ID
LEFT JOIN BUSINESS cbiz ON s.CBIZ_ID = cbiz.C_ID
LEFT JOIN BUSINESS b ON s.BIZ_ID = b.BIZ_ID
$where_sql
ORDER BY s.SALE_ID DESC
";

$result = mysqli_query($dbconnect, $sql) or die('Query error: ' . mysqli_error($dbconnect));

// CSV 다운로드 헤더
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="filtered_sales_list.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['판매번호', '납품처', '거래처', '거래처영업', '담당자명', '공급가액합계', '납품일', '유지보수시작일', '유지보수종료일', '주문번호', '보증기간']);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['SALE_ID'] ?? '',
        $row['V_NAME'] ?? '',
        $row['C_NAME'] ?? '',
        $row['CBIZ_NAME'] ?? '',
        $row['BIZ_NAME'] ?? '',
        $row['TOT_PRICE'] ?? '',
        $row['DELIVER_DATE'] ?? '',
        $row['S_DATE'] ?? '',
        $row['D_DATE'] ?? '',
        $row['ORDER_NO'] ?? '',
        $row['WARRANTY'] ?? ''
    ]);
}

fclose($output);
exit;
?>