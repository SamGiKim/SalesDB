<?php
// 데이터베이스 연결
require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

function delete_sales($saleId, &$query = "") {
    global $dbconnect;
    $query = "DELETE FROM SALES WHERE SALE_ID=?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    return $stmt;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saleId = $_POST['saleId']; 

    $stmt = delete_sales($saleId);

    if ($stmt->affected_rows > 0) {
        echo "<span>성공적으로 삭제되었습니다.</span>";
        echo "<script>setTimeout(function(){ window.location.href='salesMain.php'; }, 500);</script>"; 
        // echo "<a href='salesMain.php'>거래명세서 메인</a>";
    } else {
        echo "<span>다시 시도해주세요.</span>";
        echo "<script>setTimeout(function(){ window.location.href='salesMain.php'; }, 500);</script>"; 
        // echo "<a href='javascript:history.back();'>거래명세서 메인</a>";
    }
    $stmt->close();
} else {
    header('Location: salesMain.php'); // GET 요청인 경우 메인 페이지로 리디렉션
}
?>
