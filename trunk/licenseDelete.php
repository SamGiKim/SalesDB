<?php
// 데이터베이스 연결
require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

$resultMessage = ""; // 결과 메시지를 저장할 변수

function delete_license($saleId, $SN, &$query = "") {
    global $dbconnect;
    $query = "DELETE FROM LICENSE WHERE SALE_ID=? AND SN=?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("ss", $saleId, $SN);
    $stmt->execute();
    return $stmt;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saleId = $_POST['saleId'];
    $SN = $_POST['SN'];

    $stmt = delete_license($saleId, $SN);

    if ($stmt->affected_rows > 0) {
        // $resultMessage = "성공적으로 삭제되었습니다.";
        // echo "<script>alert('성공적으로 삭제되었습니다.'); window.location.href='licenseMain.php';</script>";
        echo "<span>성공적으로 삭제되었습니다.</span>";
        echo "<script>setTimeout(function(){ window.location.href='licenseMain.php'; }, 500);</script>"; 
        // echo "<a href='javascript:history.back();'>라이센스 메인</a>";
    } else {
        // $resultMessage = "다시 시도해주세요.";
        // echo "<script>alert('다시 시도해주세요.');  window.history.back();</script>";
        echo "<span>다시 시도해주세요.</span>";
        echo "<script>setTimeout(function(){ window.location.href='licenseMain.php'; }, 500);</script>"; 
        // echo "<a href='javascript:history.back();'>라이센스 메인</a>";
    }
    $stmt->close();

} else {
    header('Location: licenseMain.php'); // GET 요청인 경우 메인 페이지로 리디렉션
}
?>
