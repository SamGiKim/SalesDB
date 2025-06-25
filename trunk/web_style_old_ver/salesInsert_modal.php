<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

// Name으로 ID 값을 가져오는 함수들
function getIdFromVendor($dbconnect, $name) {
    return getIdFromName($dbconnect, "VENDOR", "V_ID", "NAME", $name);
}

function getIdFromCustomer($dbconnect, $name) {
    return getIdFromName($dbconnect, "CUSTOMER", "C_ID", "NAME", $name);
}

function getIdFromBusiness($dbconnect, $name) {
    return getIdFromName($dbconnect, "BUSINESS", "BIZ_ID", "NAME", $name);
}
//이름을 입력받아서 ID로 바꿔주기
function getIdFromName($dbconnect, $tableName, $idColumn, $nameColumn, $name) {
    $query = "SELECT $idColumn FROM $tableName WHERE $nameColumn = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row[$idColumn] : null;
}
//saleId는 PK값이므로 중복성 검사 해주기
function isSaleIdDuplicate($dbconnect, $saleId) {
    $query = "SELECT COUNT(*) AS count FROM SALES WHERE SALE_ID = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'] > 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // NAME 값을 바탕으로 각 테이블의 _ID 값을 가져온다. 
    $vId = getIdFromVendor($dbconnect, $_POST["vName"]);
    $cId = getIdFromCustomer($dbconnect, $_POST["cName"]);
    $cbizId = getIdFromBusiness($dbconnect, $_POST["cbizName"]);
    $bizId = getIdFromBusiness($dbconnect, $_POST["bizName"]);


    $totPrice = $_POST["totPrice"] !== "" ? $_POST["totPrice"] : null;
    $totPrice = $_POST["totPrice"] !== "" ? $_POST["totPrice"] : null;
    $dDate = $_POST["dDate"] !== "" ? $_POST["dDate"] : null;
    $SN = $_POST["SN"] !== "" ? $_POST["SN"] : null;

    $stmt = $dbconnect->prepare("INSERT INTO SALES (SALE_ID, V_ID, C_ID, CBIZ_ID, BIZ_ID, TOT_PRICE, D_DATE, SN)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssiss",
        $_POST["saleId"], $vId, $cId, $cbizId, $bizId, $totPrice, $dDate, $SN
    );

    if($stmt->execute()){
        echo "<script>
        alert('데이터가 성공적으로 입력되었습니다.');
        location.href = 'salesMain.php';
        </script>";
    } else {
        echo "<script>
        alert('입력이 실패하였습니다. 다시 시도해주세요.');
        location.href = 'salesInsert.php';
        </script>";
    }
    $stmt->close();
    mysqli_close($dbconnect);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>거래명세서 신규 입력</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="salesMain.css">
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="main">
        <div class="header-container">
            <header>신규 거래명세서 등록</header>
        </div>
        <div class="content">
            <div class="inputBox mx-auto shadow p-5 mt-4">
                <div class="btn-cancel position-relative top-0">
                    <button type="button" class="btn-close" aria-label="Close" onclick="redirectToSalesMain()"></button>
                </div>
                <form id="insertForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validationForSales()">
                    <table class="inputTbl">
                        <tr>
                            <td><label for="saleId">판매번호<span style="color: red;">*</span></label></td>
                            <td>
                                <input type="text" class="input" name="saleId" id="saleId" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="vId">납품처<span style="color:red;">*</span></label></td>
                            <td>
                                <input type="text" class="input" name="vName" id="vName" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr> 
                            <td><label for="cId">거래처<span style="color: red;">*</span></label></td>
                            <td>
                                <input type="text" class="input" name="cName" id="cName" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cbizId">거래처담당자<span style="color: red;">*</span></label></td>
                            <td>
                                <input type="text" class="input" name="cbizName" id="cbizName" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="bizId">담당자명<span style="color: red;">*</span></label></td>
                            <td>
                                <input type="text" class="input" name="bizName" id="bizName" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="totPrice">공급가액합계</label></td>
                            <td>
                                <input type="text" class="input short" name="totPrice" id="totPrice"><span>원</span>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="dDate">납품일자</label></td>
                            <td>
                                <input type="date" class="input short" name="dDate" id="dDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                            <td><label for="SN">SN</label></td>
                            <td>
                                <textarea class="txtarea" name="SN" id="SN" rows="4" cols="52" data-toggle="popover" data-trigger="focus" title="알림" placeholder="여러개의 값일 경우 ,(쉼표)로 구분지어 적어주세요."></textarea>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                    </table>
                    <div class="error-message-box" style="color: red; text-align: center;"></div>
                    <div class="btn-class">
                        <button type="submit" class="btn btn-primary insert wide-btn" data-validate="salesValidation">등록</button>
                    </div>
                </form>        
            </div>
        </div>
    </div>  
    <!-- Modal -->
    <div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="validationModalLabel">입력정보를 확인해주세요.</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
</body>
</html>