<?php
// 데이터베이스 연결
require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

$resultMessage = ""; // 결과 메시지를 저장할 변수

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lId = $_POST['lId']; 

    $query = "DELETE FROM LICENSE WHERE L_ID=?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $lId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $resultMessage = "성공적으로 삭제되었습니다.";
    } else {
        $resultMessage = "다시 시도해주세요.";
    }
    $stmt->close();
} else {
    header('Location: licenseMain.php'); // GET 요청인 경우 메인 페이지로 리디렉션
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php include "modal.html"; ?>

<?php if($resultMessage): ?>
    <script>
        // 페이지 로딩 후 모달 창으로 메시지 출력
        window.onload = function() {
            showModal("<?php echo $resultMessage; ?>");
        }
    </script>
<?php endif; ?>
</body>
</html>
