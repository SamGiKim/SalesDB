<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php"; // 데이터베이스 연결 설정 파일

mysqli_set_charset($dbconnect, "utf8");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 사용자가 입력한 값을 받아옵니다.
    $SN = isset($_POST["SN"]) ? trim($_POST["SN"]) : '';
    $orderNo = isset($_POST["orderNo"]) ? trim($_POST["orderNo"]) : '';
    $model = isset($_POST["model"]) ? $_POST["model"] : '';
    $devType = isset($_POST["devType"]) ? $_POST["devType"] : '';
    $interface = isset($_POST["interface"]) ? $_POST["interface"] : '';
    $ikind = isset($_POST["ikind"]) ? $_POST["ikind"] : '';
    $intNum = isset($_POST["intNum"]) ? $_POST["intNum"] : '';
    $capacity = isset($_POST["capacity"]) ? $_POST["capacity"] : '';
    $HDD = isset($_POST["HDD"]) ? $_POST["HDD"] : '';
    $memory = isset($_POST["memory"]) ? $_POST["memory"] : '';

    // 사용자 입력에서 공백 제거 및 와일드카드 추가
    $SN = "%" . $SN . "%";
    $orderNo = "%" . $orderNo . "%";
    // 나머지 필드에 대해서도 동일하게 처리

    // 입력된 값 확인을 위한 echo 문 추가
    echo "SN: " . $SN . "<br>";
    echo "Order No: " . $orderNo . "<br>";
    echo "Model: " . $model . "<br>";
    echo "Device Type: " . $devType . "<br>";
    echo "Interface: " . $interface . "<br>";
    echo "Kind: " . $ikind . "<br>";
    echo "Interface Number: " . $intNum . "<br>";
    echo "Capacity: " . $capacity . "<br>";
    echo "HDD: " . $HDD . "<br>";
    echo "Memory: " . $memory . "<br>";

    // 쿼리 준비 및 실행 코드는 여기 이후에 위치
}
?>

/*
| DEVICE | CREATE TABLE `DEVICE` (
  `SN` varchar(50) NOT NULL,
  `MODEL` varchar(50) NOT NULL,
  `DEV_TYPE` varchar(50) DEFAULT NULL,
  `INTERFACE` varchar(50) DEFAULT NULL,
  `IKIND` varchar(100) DEFAULT NULL,
  `INTNUM` int(10) DEFAULT NULL,
  `WDATE` date DEFAULT NULL,
  `CAPACITY` varchar(10) DEFAULT NULL,
  `HDD` varchar(20) DEFAULT NULL,
  `MEMORY` varchar(20) DEFAULT NULL,
  `ORDER_NO` varchar(50) NOT NULL,
  `LF` int(1) DEFAULT 0,
  `SF` int(1) DEFAULT 0,
  `SALE_ID` varchar(50) DEFAULT '0',
  `FV` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`SN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci |
*/