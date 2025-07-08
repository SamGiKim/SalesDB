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
$saleId = $_GET['saleId'] ?? null;
$sn = $_GET['SN'] ?? null;
$vendorName = $_GET['vendorName'] ?? null;
$type = $_GET['type'] ?? null;
$manager = $_GET['manager'] ?? null;
$sDateFrom = $_GET['sDateFrom'] ?? null;
$sDateTo = $_GET['sDateTo'] ?? null;
$dDateFrom = $_GET['dDateFrom'] ?? null;
$dDateTo = $_GET['dDateTo'] ?? null;
$ref = $_GET['ref'] ?? null;

$where = [];
// 검색 조회
if ($saleId || $sn || $vendorName || $type || $manager || $sDateFrom 
    || $sDateTo || $dDateFrom || $dDateTo || $ref) {
    if ($saleId) {
        $where[] = "L.SALE_ID LIKE '%" . mysqli_real_escape_string($dbconnect, $saleId) . "%'";
    }
    if ($sn) {
        $where[] = "L.SN LIKE '%" . mysqli_real_escape_string($dbconnect, $sn) . "%'";
    }
    if ($vendorName) {
        $where[] = "V.NAME LIKE '%" . mysqli_real_escape_string($dbconnect, $vendorName) . "%'";
    }
    if ($type) {
        $where[] = "L.TYPE LIKE '%" . mysqli_real_escape_string($dbconnect, $type) . "%'";
    }
    if ($manager) {
        $where[] = "L.MANAGER = '" . mysqli_real_escape_string($dbconnect, $manager) . "'";
    }
    if ($sDateFrom && $sDateTo) {
        $where[] = "L.S_DATE BETWEEN '" . mysqli_real_escape_string($dbconnect, $sDateFrom) . "' AND '" . mysqli_real_escape_string($dbconnect, $sDateTo) . "'";
    } elseif ($sDateFrom) {
        $where[] = "L.S_DATE >= '" . mysqli_real_escape_string($dbconnect, $sDateFrom) . "'";
    } elseif ($sDateTo) {
        $where[] = "L.S_DATE <= '" . mysqli_real_escape_string($dbconnect, $sDateTo) . "'";
    }
    if ($dDateFrom && $dDateTo) {
        $where[] = "L.D_DATE BETWEEN '" . mysqli_real_escape_string($dbconnect, $dDateFrom) . "' AND '" . mysqli_real_escape_string($dbconnect, $dDateTo) . "'";
    } elseif ($dDateFrom) {
        $where[] = "L.D_DATE >= '" . mysqli_real_escape_string($dbconnect, $dDateFrom) . "'";
    } elseif ($dDateTo) {
        $where[] = "L.D_DATE <= '" . mysqli_real_escape_string($dbconnect, $dDateTo) . "'";
    }
    if ($ref) {
        $where[] = "L.D_DATE LIKE '%" . mysqli_real_escape_string($dbconnect, $ref) . "%'";
    }
}

$where_sql = '';
if (count($where) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
SELECT 
    L.SALE_ID,
    L.SN,
    V.NAME AS VENDOR_NAME,
    L.TYPE,
    L.MANAGER,
    L.PRICE,
    L.S_DATE,
    L.D_DATE,
    L.WARRANTY,
    L.INSPECTION,
    L.SUPPORT,
    L.REF
FROM LICENSE L
LEFT JOIN SALES S ON L.SALE_ID = S.SALE_ID
LEFT JOIN VENDOR V ON S.V_ID = V.V_ID
$where_sql
ORDER BY L.SALE_ID DESC
";

$result = mysqli_query($dbconnect, $sql) or die('Query error: ' . mysqli_error($dbconnect));

// CSV 다운로드 헤더
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="filtered_license_list.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['명세서번호', 'SN', '납품처', '유형', '담당 엔지니어', '가격', '보증기간', '시작일', '종료일', '점검/파트너지원', '비고']);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['SALE_ID'] ?? '',
        $row['SN'] ?? '',
        $row['VENDOR_NAME'] ?? '',
        $row['TYPE'] ?? '',
        $row['MANAGER'] ?? '',
        $row['PRICE'] ?? '',
        $row['S_DATE'] ?? '',
        $row['D_DATE'] ?? '',
        $row['WARRANTY'] ?? '',
        ($row['INSPECTION'] ?? '') . "/" . ($row['SUPPORT'] ?? ''),
        $row['REF'] ?? ''
    ]);
}

fclose($output);
exit;
?>