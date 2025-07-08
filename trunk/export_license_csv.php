<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "auth.php";
require_once "sales_db.php";

// UTF-8 인코딩 설정
mysqli_set_charset($dbconnect, "utf8");
// Excel 한글 깨짐 방지
echo "\xEF\xBB\xBF";

// DB 조회
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
ORDER BY L.SALE_ID DESC
";

$result = mysqli_query($dbconnect, $sql) or die('Query error: ' . mysqli_error($dbconnect));

// 다운로드용 헤더 설정
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="license_list.csv"');

// 출력 버퍼에 파일 핸들 연결
$output = fopen('php://output', 'w');

if ($output === false) {
    die('Failed to open php://output');
}

// CSV 헤더 라인
fputcsv($output, ['명세서번호', 'SN', '납품처', '유형', '담당 엔지니어', '가격', '보증기간', '시작일', '종료일', '점검/파트너지원', '비고']);

// 데이터 행 작성
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
