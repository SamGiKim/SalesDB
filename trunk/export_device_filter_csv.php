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
$sn = $_GET['SN'] ?? null;
$orderNo = $_GET['orderNo'] ?? null;
$model = $_GET['model'] ?? null;
$fv = $_GET['FV'] ?? null;
$devType = $_GET['devType'] ?? null;
$interface = $_GET['interface'] ?? null;
$ikind = $_GET['ikind'] ?? null;
$intNum = $_GET['intNum'] ?? null;
$capacity = $_GET['capacity'] ?? null;
$hdd = $_GET['HDD'] ?? null;
$memory = $_GET['memory'] ?? null;


$where = [];
// 검색 조회
if ($sn || $orderNo || $model || $fv || $devType || $interface 
    || $ikind || $intNum || $capacity || $hdd || $memory) {
    if ($sn) {
        $where[] = "D.SN LIKE '%" . mysqli_real_escape_string($dbconnect, $sn) . "%'";
    }
    if ($orderNo) {
        $where[] = "D.ORDER_NO LIKE '%" . mysqli_real_escape_string($dbconnect, $orderNo) . "%'";
    }
    if ($model) {
        $where[] = "D.MODEL = '" . mysqli_real_escape_string($dbconnect, $model) . "'";
    }
    if ($fv) {
        $where[] = "D.FV LIKE '%" . mysqli_real_escape_string($dbconnect, $fv) . "%'";
    }
    if ($devType) {
        $where[] = "D.DEV_TYPE = '" . mysqli_real_escape_string($dbconnect, $devType) . "'";
    }
    if ($interface) {
        $where[] = "D.INTERFACE = '" . mysqli_real_escape_string($dbconnect, $interface) . "'";
    }
    if ($ikind) {
        $where[] = "D.IKIND = '" . mysqli_real_escape_string($dbconnect, $ikind) . "'";
    } 
    if ($intNum) {
        $where[] = "D.INTNUM = '" . mysqli_real_escape_string($dbconnect, $intNum) . "'";
    }
    if ($capacity) {
        $where[] = "D.CAPACITY LIKE '%" . mysqli_real_escape_string($dbconnect, $capacity) . "%'";
    }
    if ($hdd) {
        $where[] = "D.HDD = '" . mysqli_real_escape_string($dbconnect, $hdd) . "'";
    }
    if ($memory) {
        $where[] = "D.MEMORY = '" . mysqli_real_escape_string($dbconnect, $memory) . "'";
    }
}

$where_sql = '';
if (count($where) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
SELECT 
    D.SN,
    D.MODEL,
    D.FV,
    D.DEV_TYPE,
    D.INTERFACE,
    D.IKIND,
    D.INTNUM,
    D.WDATE,
    D.CAPACITY,
    D.HDD,
    D.MEMORY,
    D.ORDER_NO,
    S.SALE_ID
FROM DEVICE D
LEFT JOIN SALES S ON D.ORDER_NO = S.ORDER_NO
$where_sql
ORDER BY D.WDATE DESC
";

$result = mysqli_query($dbconnect, $sql) or die('Query error: ' . mysqli_error($dbconnect));

// CSV 다운로드 헤더
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="filtered_device_list.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['SN', '모델명', 'FV', '장비유형', '인터페이스유형', '포트수', '제조일', '용량', '디스크', '메모리', '주문번호', '명세서번호']);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['SN'] ?? '',
        $row['MODEL'] ?? '',
        $row['FV'] ?? '',
        $row['DEV_TYPE'] ?? '',
        $row['INTERFACE'] ?? '',
        ($row['IKIND'] ?? '') . ($row['INTNUM'] ?? ''),
        $row['WDATE'] ?? '',
        $row['CAPACITY'] ?? '',
        $row['HDD'] ?? '',
        $row['MEMORY'] ?? '',
        $row['ORDER_NO'] ?? '',
        $row['SALE_ID'] ?? ''
    ]);
}

fclose($output);
exit;
?>