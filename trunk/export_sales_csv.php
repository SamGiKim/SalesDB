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
    S.SALE_ID,
    V.NAME AS V_NAME,
    C.NAME AS C_NAME,
    CBIZ.NAME AS CBIZ_NAME,
    B.NAME AS BIZ_NAME,
    S.TOT_PRICE,
    S.DELIVER_DATE,
    S.S_DATE,
    S.D_DATE,
    S.ORDER_NO,
    S.WARRANTY
FROM SALES S
LEFT JOIN VENDOR V ON S.V_ID = V.V_ID
LEFT JOIN CUSTOMER C ON S.C_ID = C.C_ID
LEFT JOIN CUSTOMER CBIZ ON S.CBIZ_ID = CBIZ.C_ID
LEFT JOIN BUSINESS B ON S.BIZ_ID = B.BIZ_ID
ORDER BY S.SALE_ID DESC
";

$result = mysqli_query($dbconnect, $sql) or die('Query error: ' . mysqli_error($dbconnect));

// 다운로드용 헤더 설정
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="sales_list.csv"');

// 출력 버퍼에 파일 핸들 연결
$output = fopen('php://output', 'w');

if ($output === false) {
    die('Failed to open php://output');
}

// CSV 헤더 라인
fputcsv($output, ['판매번호', '납품처', '거래처', '거래처영업', '담당자명', '공급가액합계', '납품일', '유지보수시작일', '유지보수종료일', '주문번호', '보증기간']);

// 데이터 행 작성
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
