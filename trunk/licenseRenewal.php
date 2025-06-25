<?php
// echo "licenseRenewal.php 도착";

error_reporting(0);
@ini_set('display_errors', 0);

require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

$saleId = $_POST['saleId'];
$SN = $_POST['SN'];
$type = $_POST['type'];
$price = $_POST['price'];
$warranty = $_POST['warranty'];
$sDate = $_POST['sDate'];
$dDate = $_POST['dDate'];
$inspection = $_POST['inspection'];
$support = $_POST['support'];
$ref = $_POST['ref'];

// SALE_ID와 SN에 해당하는 최대 NO 값을 조회(SALE_ID, SN, NO가 PK)
$query = "SELECT IFNULL(MAX(NO), 0) AS max_no FROM LICENSE_HISTORY WHERE SALE_ID = ? AND SN = ?";
$stmt = $dbconnect->prepare($query);
$stmt->bind_param("ss", $saleId, $SN); 
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$newNo = $row['max_no'] + 1;


// 트랜잭션 시작
$dbconnect->begin_transaction();

try {
    // STEP 1. 기존 데이터를 LICENSE_HISTORY에 넣는다.
    $insertToHistoryQuery = "INSERT INTO LICENSE_HISTORY (SALE_ID, SN, NO, TYPE, PRICE, S_DATE, D_DATE, REF, WARRANTY, INSPECTION, SUPPORT, LICENSE_INSERTED_DATE) 
    SELECT ?, ?, ?, TYPE, PRICE, S_DATE, D_DATE, REF, WARRANTY, INSPECTION, SUPPORT, NOW() FROM LICENSE WHERE SALE_ID = ? AND SN = ?";
    
    $historyStmt = $dbconnect->prepare($insertToHistoryQuery);
    $historyStmt->bind_param("ssiss", $saleId, $SN, $newNo, $saleId, $SN);

    if (!$historyStmt->execute()) {
        throw new Exception("Insert to LICENSE_HISTORY failed." . $historyStmt->error);
    }

    // STEP 2. 기존 데이터를 LICENSE에서 삭제한다.
    $deleteStmt = $dbconnect->prepare("DELETE FROM LICENSE WHERE SALE_ID = ? AND SN = ?");
    $deleteStmt->bind_param("ss", $saleId, $SN);

    if (!$deleteStmt->execute()) {
        throw new Exception("Delete from LICENSE failed: " . $deleteStmt->error);
    }

    // STEP 2.1. 중복 데이터 검사 (LICENSE 테이블)
    $duplicateCheckQuery = "SELECT 1 FROM LICENSE WHERE SALE_ID = ? AND SN = ?";
    $dupStmt = $dbconnect->prepare($duplicateCheckQuery);
    $dupStmt->bind_param("ss", $saleId, $SN);
    $dupStmt->execute();
    $dupResult = $dupStmt->get_result();

    if ($dupResult->num_rows > 0) {
        throw new Exception("중복된 데이터가 이미 LICENSE에 존재합니다.");
    }

    // STEP 2.2. S_DATE  새로 입력한 S_DATE 유효성 검사
    $dateCheckQuery = "SELECT D_DATE FROM LICENSE_HISTORY WHERE SALE_ID = ? AND SN = ? ORDER BY D_DATE DESC LIMIT 1";
    $dateStmt = $dbconnect->prepare($dateCheckQuery);
    $dateStmt->bind_param("ss", $saleId, $SN);
    $dateStmt->execute();
    $dateResult = $dateStmt->get_result();
    $dateRow = $dateResult->fetch_assoc();

    $previousDDate = $dateRow['D_DATE'];

    // echo "sDate: " . $sDate . ", previousDDate: " . $previousDDate;

    // S_DATE나 D_DATE가 NULL이 아닐 때만 검사해서
    // 새로운시작일이 이전의 종료일보다 이전이거나 같으면 true를 반환하여 예외를 발생기킨다.
    if (!is_null($previousDDate) && !is_null($sDate) && strtotime($sDate) <= strtotime($previousDDate)) {
        throw new Exception("갱신 시작일 등 입력값을 확인해주세요.");
    }

    // STEP 3. 새로운 데이터를 LICENSE에 추가한다.
    $insertToLicenseStmt = $dbconnect->prepare("INSERT INTO LICENSE (SALE_ID, SN, `TYPE`, PRICE, WARRANTY, S_DATE, D_DATE, INSPECTION, SUPPORT, REF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertToLicenseStmt->bind_param("sssiisssss", $saleId, $SN, $type, $price, $warranty, $sDate, $dDate, $inspection, $support, $ref);
    
    if (!$insertToLicenseStmt->execute()) {
        throw new Exception("Insert to LICENSE failed: " . $insertToLicenseStmt->error);
    }

    // 모든 작업이 성공적으로 완료되면 커밋
    $dbconnect->commit();
    echo json_encode(['success' => true, 'message' => '라이센스가 갱신되었습니다.']);

} catch (mysqli_sql_exception $e) {
    $dbconnect->rollback();
    echo json_encode(['success' => false, 'message' => 'MySQLi error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $dbconnect->rollback();
    echo json_encode(['success' => false, 'message' => '(확인요망!) ' . $e->getMessage()]);
}

?>
