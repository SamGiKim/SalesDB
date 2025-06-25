<?php
require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

// LF 업데이트
$updateLF = "UPDATE DEVICE D
           LEFT JOIN LICENSE L ON D.SN = L.SN
           SET D.LF = CASE WHEN L.SN IS NOT NULL THEN 1 ELSE 0 END";

if (!mysqli_query($dbconnect, $updateLF)) {
    die("LF 업데이트 실패: " . mysqli_error($dbconnect));
}

// SF 업데이트
$updateSF = "UPDATE DEVICE D
           LEFT JOIN SALES S ON D.ORDER_NO = S.ORDER_NO
           SET D.SF = CASE WHEN S.ORDER_NO IS NOT NULL THEN 1 ELSE 0 END";

if (!mysqli_query($dbconnect, $updateSF)) {
    die("SF 업데이트 실패: " . mysqli_error($dbconnect));
}

// echo "업데이트 성공";
?>
