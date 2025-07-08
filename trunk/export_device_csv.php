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
ORDER BY D.WDATE DESC
";

$result = mysqli_query($dbconnect, $sql) or die('Query error: ' . mysqli_error($dbconnect));

// 다운로드용 헤더 설정
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="device_list.csv"');

// 출력 버퍼에 파일 핸들 연결
$output = fopen('php://output', 'w');

if ($output === false) {
    die('Failed to open php://output');
}

// CSV 헤더 라인
fputcsv($output, ['SN', '모델명', 'FV', '장비유형', '인터페이스유형', '포트수', '제조일', '용량', '디스크', '메모리', '주문번호', '명세서번호', '비고']);

// 데이터 행 작성
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
