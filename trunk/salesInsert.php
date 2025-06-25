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

// 이름을 입력받아서 ID로 바꿔주기
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

// SALE_ID는 PK값이므로 중복성 검사 해주기
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
    // SALE_ID의 중복을 체크
    if (isSaleIdDuplicate($dbconnect, $_POST["saleId"])) {
        echo "<script>
        alert('입력한 SALE_ID가 이미 존재합니다. 다른 값을 입력해주세요.');
        location.href = 'salesInsert.php';
        </script>";
        exit; // 중복이면 이후의 코드를 실행하지 않고 종료
    }
    $saleId = $_POST["saleId"];
    $orderNo = $_POST["orderNo"];

    // NAME 값을 바탕으로 각 테이블의 _ID 값을 가져온다.
    $vId = getIdFromVendor($dbconnect, $_POST["vName"]);
    $cId = getIdFromCustomer($dbconnect, $_POST["cName"]);
    $cbizId = getIdFromBusiness($dbconnect, $_POST["cbizName"]);
    $bizId = getIdFromBusiness($dbconnect, $_POST["bizName"]);

    if (empty($_POST["saleId"]) || empty($_POST['orderNo'])|| is_null($vId) || 
    is_null($cId) || is_null($cbizId) || is_null($bizId)) {
        echo "<script>
        alert('필수 항목(*)을 올바르게 기재해주세요.');
        window.history.back();
        </script>";
        exit;
    }
    $totPrice = $_POST["totPrice"] !== "" ? $_POST["totPrice"] : null;
    $deliverDate = $_POST["deliverDate"] !== "" ? $_POST["deliverDate"] : null;
    $sDate = $_POST["sDate"] !== "" ? $_POST["sDate"] : null;
    $dDate = $_POST["dDate"] !== "" ? $_POST["dDate"] : null;
    $orderNo = $_POST["orderNo"] !== "" ? $_POST["orderNo"] : null;
    $orderNo = trim($orderNo);

    // 사용자가 warranty_years, warranty_months를 입력하면, 개월수로 계산해서 warranty라는 DB의 컬럼값으로 만든다.
    function changeToMonths($warranty_years, $warranty_months) {
        return ($warranty_years * 12) + $warranty_months;
    }
    $warranty_years = intval($_POST['warranty_years'] ?? 0);
    $warranty_months = intval($_POST['warranty_months'] ?? 0);
    $warranty = changeToMonths($warranty_years, $warranty_months);

    $ref = isset($_POST["ref"]) && $_POST["ref"] !== "" ? $_POST["ref"] : null;

   // 1. SALES INSERT
    $stmt = $dbconnect->prepare("INSERT INTO SALES (SALE_ID, V_ID, C_ID, CBIZ_ID, BIZ_ID, TOT_PRICE, DELIVER_DATE, S_DATE, D_DATE, WARRANTY, ORDER_NO, REF)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
    die("Statement preparation failed: " . $dbconnect->error);
    }

    $stmt->bind_param(
    "sssssisssiss",
    $_POST["saleId"], $vId, $cId, $cbizId, $bizId, $totPrice, $deliverDate, $sDate, $dDate, $warranty, $orderNo, $ref
    );

    if ($stmt->execute()) {
    $stmt->close(); // 첫 번째 명령문 닫기

    // 2. DEVICE 테이블에서 ORDER_NO가 일치하는 레코드를 찾아 SALE_ID 업데이트
    $query_device = "UPDATE DEVICE SET SALE_ID = ? WHERE ORDER_NO = ?";
    $stmt_device = $dbconnect->prepare($query_device);
    $stmt_device->bind_param("ss", $_POST["saleId"], $orderNo);
    $stmt_device->execute();
    $stmt_device->close(); // 두 번째 명령문 닫기

    echo "<script>alert('데이터가 성공적으로 입력되었습니다.'); location.href='salesMain.php';</script>";
    } else {
    echo "<script>alert('입력이 실패하였습니다. 다시 시도해주세요.'); location.href='salesInsert.php';</script>";
    }
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
                <form id="insertForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <table class="inputTbl">
                        <tr>
                            <td><label for="saleId">판매번호<span style="color: red;">*</span><span style="font-size:12px;">(SALE_ID)<span></label></td>
                            <td>
                                <input type="text" class="input" name="saleId" id="saleId" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="orderNo">주문번호<span style="color: red;">*</span><span style="font-size:12px;">(ORDER_NO)</span></label></label></td>
                            <td>
                                <input type="text" class="input" name="orderNo" id="orderNo">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="vId">납품처<span style="font-size:12px;">(VENDOR_NAME)</span></label></td>
                            <td>
                                <input type="text" class="input" name="vName" id="vName" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr> 
                            <td><label for="cId">거래처<span style="font-size:12px;">(CUSTOMER_NAME)<span></label></td>
                            <td>
                                <input type="text" class="input" name="cName" id="cName" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cbizId">거래처담당자<span style="font-size:12px;">(BUSINESS_NAME)</span></label></td>
                            <td>
                                <input type="text" class="input" name="cbizName" id="cbizName">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="bizId">담당자명<span style="font-size:12px;">(BUSINESS_NAME)</span></label></td>
                            <td>
                                <input type="text" class="input" name="bizName" id="bizName">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="totPrice">공급가액합계<span style="font-size:12px;">(TOT_PRICE)</span></label></td>
                            <td>
                                <input type="text" class="input short" name="totPrice" id="totPrice"><span>원</span>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="deliverDate">납품일<span style="font-size:12px;">(DELIVER_DATE)</span></label></td>
                            <td>
                                <input type="date" class="input short" name="deliverDate" id="deliverDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="sDate">유지보수시작일<span style="font-size:12px;">(S_DATE)</span></label></td>
                            <td>
                                <input type="date" class="input short" name="sDate" id="sDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="warranty">보증기간<span style="font-size:12px;">(WARRANTY)</span></label></td>
                            <td>
                                <select class="input short selectstyle" name="warranty_years" id="warranty_years">
                                    <?php
                                    for ($i = 0; $i <= 5; $i++) {
                                        echo "<option value=\"$i\">{$i} 년</option>";
                                    }
                                    ?>
                                </select>

                                <select class="input short selectstyle" name="warranty_months" id="warranty_months">
                                    <?php
                                    for ($i = 0; $i <= 11; $i++) {
                                        echo "<option value=\"$i\">{$i} 개월</option>";
                                    }
                                    ?>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="dDate">유지보수종료일<span style="font-size:12px;">(D_DATE)</span></label></td>
                            <td>
                                <input type="date" class="input short" name="dDate" id="dDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="ref">비고<span style="font-size:12px;">(REF)</span></label></td>
                            <td><textarea class="txtarea" name="ref" id="ref" rows="4" cols="52" data-toggle="popover" data-trigger="focus" title="알림"><?php echo isset($html_values['SN']) ? $html_values['SN'] : ''; ?></textarea></td>
                        </tr>
                    </table>
                    <div class="error-message-box" style="color: red; text-align: center;"></div>
                    <div class="btn-class">
                        <button type="submit" class="btn btn-primary insert wide-btn">등록</button>
                    </div>
                </form>        
            </div>
        </div>
    </div>  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const sDateInput = document.getElementById('sDate');
    const warrantyYearsSelect = document.getElementById('warranty_years');
    const warrantyMonthsSelect = document.getElementById('warranty_months');
    const dDateInput = document.getElementById('dDate');

    // 함수: 날짜와 보증 기간(년/월)을 받아 유지보수 종료일을 반환
    function calculateEndDate(startDate, years, months) {
        const endDate = new Date(startDate);
        endDate.setFullYear(endDate.getFullYear() + years);
        endDate.setMonth(endDate.getMonth() + months);
        endDate.setDate(endDate.getDate() - 1);  // 하루를 빼는 부분
        return endDate.toISOString().split('T')[0];  // 날짜 형식 (YYYY-MM-DD)으로 반환
    }

    // 이벤트 핸들러 설정
    sDateInput.addEventListener('change', updateEndDate);
    warrantyYearsSelect.addEventListener('change', updateEndDate);
    warrantyMonthsSelect.addEventListener('change', updateEndDate);

    // 유지보수 종료일 업데이트
    function updateEndDate() {
        if (sDateInput.value) {
            const years = parseInt(warrantyYearsSelect.value, 10);
            const months = parseInt(warrantyMonthsSelect.value, 10);
            dDateInput.value = calculateEndDate(new Date(sDateInput.value), years, months);
        }
    }
});
</script>

</body>
</html>