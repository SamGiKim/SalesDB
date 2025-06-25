<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// 데이터베이스 연결
require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

$html_values = [];

// --- 추가된 함수 시작 ---
function checkExists($dbconnect, $table, $column, $value, $sel_column)
{
    $query = "SELECT " . $sel_column . " FROM " . $table . " WHERE " . $column . " = ?";
    // echo $query . "\n";
    // echo $value;
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    // var_dump($row[$sel_column]);
    return $row[$sel_column]; // "" or "값"
}

//사용자가 DB에 저장된 WARRANTY를 가져와서 화면에 ~년 ~개월로 보여준다. 
function convertYearsAndMonthsToMonths($years, $months)
{
    return ($years * 12) + $months;
}

function convertToYearsAndMonths($warranty)
{
    $years = floor($warranty / 12);
    $months = $warranty % 12;
    return ['years' => $years, 'months' => $months];
}

// DB로부터 가져온 WARRANTY 값
$warranty_from_db = $html_values['WARRANTY'] ?? 0;

$years_and_months = convertToYearsAndMonths($warranty_from_db);
$html_values['WARRANTY_YEARS'] = $years_and_months['years'];
$html_values['WARRANTY_MONTHS'] = $years_and_months['months'];

// 사용자가 form을 통해 전송한 데이터
$warranty_years = $_POST['warranty_years'] ?? 0;
$warranty_months = $_POST['warranty_months'] ?? 0;

$warranty = convertYearsAndMonthsToMonths($warranty_years, $warranty_months);

//디버깅, OK
// echo $warranty_years;
// echo $warranty_months;
// echo $warranty;

// 판매번호가 있는지 확인하고 데이터를 업데이트 하는 함수
function updateSales($orderNo, $vId, $cId, $cbizId, $bizId, $totPrice, $deliverDate, $sDate, $warranty, $dDate, $ref, $saleId)
{
    $query = "UPDATE SALES SET ORDER_NO=?, V_ID=?, C_ID=?, CBIZ_ID=?, BIZ_ID=?, TOT_PRICE=?,DELIVER_DATE=?,S_DATE=?, WARRANTY=?, D_DATE=?, REF=? WHERE SALE_ID=?";
    return $query;
}


function validateInputs($dbconnect, &$vId, &$cId, &$cbizId, &$bizId)
{
    // TODO: html의 해당 input 입력 텍스트가 없으면, 대상 테이블을 체크를 하지 않는다.

    if (trim($vId) != "") {
        $vId    = checkExists($dbconnect, "VENDOR", "NAME",   $vId, "V_ID");
        if ($vId == "") {
            echo "V_ID가 VENDOR 테이블에 존재하지 않습니다.";
            exit;
        }
    }

    if (trim($cId) != "") {
        $cId    = checkExists($dbconnect, "CUSTOMER", "NAME", $cId, "C_ID");
        if ($cId == "") {
            echo "C_ID가 CUSTOMER 테이블에 존재하지 않습니다.";
            exit;
        }
    }

    if (trim($cbizId) != "") {
        $cbizId = checkExists($dbconnect, "BUSINESS", "NAME", $cbizId, "BIZ_ID");
        if ($cbizId == "") {
            echo "CBIZ_ID가 BUSINESS 테이블의 BIZ_ID에 존재하지 않습니다.";
            exit;
        }
    }

    if (trim($bizId) != "") {
        $bizId  = checkExists($dbconnect, "BUSINESS", "NAME", $bizId, "BIZ_ID");
        if ($bizId == "") {
            echo "BIZ_ID가 BUSINESS 테이블의 BIZ_ID에 존재하지 않습니다.";
            exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 폼이 제출된 경우 처리
    $saleId = $_POST['saleId'] ?? '';
    $orderNo = $_POST['orderNo']??'';
    $vId = $_POST['vId']??'';
    $cId = $_POST['cId']??'';
    $cbizId = $_POST['cbizId']??'';
    $bizId = $_POST['bizId']??'';
    $totPrice = str_replace(',', '', $_POST['totPrice']);
    $deliverDate = isset($_POST['deliverDate']) && trim($_POST['deliverDate']) !== "" ? $_POST['deliverDate'] : null;
    $sDate = isset($_POST['sDate']) && trim($_POST['sDate']) !== "" ? $_POST['sDate'] : null;

    //이렇게 바로 $_POST['warranty']로 가져오는게 문제이다. 사용자는 입력을 warranty_years, warranty_months로 하기 때문이다.
    // $warranty = isset($_POST['warranty']) && trim($_POST['warranty']) !== "" ? $_POST['warranty'] : null;
    // -->수정 사용자가 form을 통해 전송한 데이터
    $warranty_years = $_POST['warranty_years'] ?? 0;
    $warranty_months = $_POST['warranty_months'] ?? 0;
    $warranty = convertYearsAndMonthsToMonths($warranty_years, $warranty_months);
    $dDate = isset($_POST['dDate']) && trim($_POST['dDate']) !== "" ? $_POST['dDate'] : null;
    $ref = isset($_POST["ref"]) && $_POST["ref"] !== "" ? $_POST["ref"] : null;

    // 입력값 검증
    validateInputs($dbconnect, $vId, $cId, $cbizId, $bizId, $dDate, $sDate);

    // $query = updateSales($saleId, $vId, $cId, $cbizId, $bizId, $totPrice, $deliverDate, $sDate, $dDate, $orderNo, $warranty, $ref);
    $query = updateSales($orderNo, $vId, $cId, $cbizId, $bizId, $totPrice, $deliverDate, $sDate, $warranty, $dDate, $ref, $saleId);
    $stmt = $dbconnect->prepare($query);

    // var_dump($warranty);
    // exit;

    $stmt->bind_param("sssssississs", $orderNo, $vId, $cId, $cbizId, $bizId, $totPrice, $deliverDate, $sDate, $warranty, $dDate, $ref, $saleId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('업데이트 성공!'); window.location.href='salesMain.php';</script>";

        // SALES 테이블 업데이트가 성공했으므로 이제 LICENSE 테이블을 업데이트 혹은 INSERT
        // SALES 테이블의 ORDER_NO를 이용하여 DEVICE 테이블의 SN을 찾는다.
        $query_device = "SELECT SN FROM DEVICE WHERE ORDER_NO = ?";
        $stmt_device = $dbconnect->prepare($query_device);
        $stmt_device->bind_param("s", $orderNo); // $orderNo는 SALES 테이블에서 가져온 ORDER_NO입니다.
        $stmt_device->execute();
        $stmt_device->bind_result($SN);

        if ($stmt_device->fetch()) { // DEVICE 테이블에서 SN을 성공적으로 찾았다면

            //duplicate key :             
            $query_license = "INSERT INTO LICENSE (SALE_ID, SN, WARRANTY) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE WARRANTY = VALUES(WARRANTY)";
            $stmt_license = $dbconnect->prepare($query_license);
            $stmt_license->bind_param("ssi", $saleId, $SN, $warranty);
            $stmt_license->execute();

            if ($stmt_license->affected_rows > 0) {
                echo "<script>alert('LICENSE 업데이트/삽입 성공!'); window.location.href='salesMain.php';</script>";
            } else {
                echo "<script>alert('LICENSE 업데이트/삽입 실패!');</script>";
            }

            $stmt_license->close();
        } else {
            echo "<script>alert('DEVICE 테이블에서 SN을 찾지 못했습니다.');</script>";
        }
        $stmt_device->close();
    } else {
        echo "<script>alert('업데이트 실패!');</script>";
    }
    $stmt->close();
} else {
    // 페이지가 로드되는 경우 처리
    // sale_id = ? 인 것을 업데이트
    function elseSalesUpdate($saleId, $safeSaleId)
    {
        // $saleId = $_GET['saleId']; // URL에서 saleId 값을 가져옴.
        $saleId = isset($_GET['saleId']) ? urldecode($_GET['saleId']) : null;
        $safeSaleId = urlencode($saleId); //URL에 넣을 수 있는 문자열 인코딩.. 슬래쉬때매 오류난 것일수도 있어서.
        $query =  "SELECT s.SALE_ID as SALE_ID, ";
        $query .= "s.ORDER_NO as ORDER_NO, ";
        $query .= "v.NAME as V_ID, ";
        $query .= "c.NAME as C_ID, ";
        $query .= "b1.NAME as CBIZ_ID, ";
        $query .= "b2.NAME as BIZ_ID, ";
        $query .= "s.TOT_PRICE as TOT_PRICE, ";
        $query .= "s.DELIVER_DATE as DELIVER_DATE, ";
        $query .= "s.S_DATE as S_DATE, ";
        $query .= "s.WARRANTY as WARRANTY, ";
        $query .= "s.D_DATE as D_DATE, ";
        $query .= "s.REF as REF ";
        $query .= "FROM SALES as s ";
        $query .= "LEFT JOIN VENDOR   AS v  ON s.V_ID     = v.V_ID ";
        $query .= "LEFT JOIN CUSTOMER AS c  ON s.C_ID     = c.C_ID ";
        $query .= "LEFT JOIN BUSINESS AS b1 ON s.CBIZ_ID  = b1.BIZ_ID ";      // CBIZ_ID와 BUSINESS 테이블을 연결
        $query .= "LEFT JOIN BUSINESS AS b2 ON s.BIZ_ID   = b2.BIZ_ID ";      // BIZ_ID와  BUSINESS 테이블을 연결
        $query .= "WHERE s.SALE_ID = ? ";
        return $query;
    }

    // $query = elseSalesUpdate($saleId, $safeSaleId);
    $saleId = $_GET['saleId'] ?? null; // Use null coalescing operator to set a default value
    $safeSaleId = urlencode($saleId);
    $query = elseSalesUpdate($saleId, $safeSaleId);


    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $html_values = $result->fetch_assoc();

    // DB로부터 가져온 warranty 값 처리
    if ($html_values) {
        $warranty_from_db = $html_values['WARRANTY'];
        $warranty_values = convertToYearsAndMonths($warranty_from_db);
        $html_values['WARRANTY_YEARS'] = $warranty_values['years'];
        $html_values['WARRANTY_MONTHS'] = $warranty_values['months'];
    } else {
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>거래명세서 수정</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="salesMain.css">
    <script src="https://unpkg.com/htmx.org@1.9.4"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous"></script>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="main">
        <div class="header-container">
            <header>거래명세서 수정</header>
        </div>
        <div class="content">
            <div class="inputBox mx-auto shadow p-5 mt-4">
                <div class="btn-cancel position-relative top-0">
                    <button type="button" class="btn-close" aria-label="Close" onclick="redirectToSalesMain()"></button>
                </div>
                <form id="updateForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <table class="inputTbl">
                        <tr>
                            <td><label for="saleId">판매번호</label></td>
                            <td><input type="text" class="input" name="saleId" id="saleId" value="<?php echo $html_values['SALE_ID']; ?>" readonly></td>
                        </tr>
                        <tr>
                            <td><label for="orderNo">주문번호</label></td>
                            <td><input type="text" class="input" name="orderNo" id="orderNo" value="<?php echo $html_values['ORDER_NO'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="vId">납품처</label></td>
                            <td><input type="text" class="input" name="vId" id="vId" value="<?php echo $html_values['V_ID'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="cId">거래처</label></td>
                            <td><input type="text" class="input" name="cId" id="cId" value="<?php echo $html_values['C_ID'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="cbizId">거래처영업</label></td>
                            <td><input type="text" class="input" name="cbizId" id="cbizId" value="<?php echo $html_values['CBIZ_ID'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="bizId">담당자명</label></td>
                            <td><input type="text" class="input" name="bizId" id="bizId" value="<?php echo $html_values['BIZ_ID'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="totPrice">공급가액합계</label></td>
                            <td>
                                <input type="text" class="input short" name="totPrice" id="totPrice" value="<?php
                                                                                                            echo number_format($html_values['TOT_PRICE'] ?? 0);
                                                                                                            ?>"><span>원</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="deliverDate">납품일</label></td>
                            <td><input type="deliverDate" class="input short" name="deliverDate" id="deliverDate" value="<?php echo $html_values['DELIVER_DATE'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="sDate">유지보수시작일</label></td>
                            <td><input type="date" class="input short" name="sDate" id="sDate" value="<?php echo $html_values['S_DATE'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="warranty">보증기간</label></td>
                            <td>
                                <select class="input short selectstyle" name="warranty_years" id="warranty_years">
                                    <?php
                                    for ($i = 0; $i <= 5; $i++) {
                                        echo "<option value=\"$i\" " . (isset($html_values['WARRANTY_YEARS']) && $html_values['WARRANTY_YEARS'] == $i ? 'selected' : '') . ">{$i} 년</option>";
                                    }
                                    ?>
                                </select>
                                <select class="input short selectstyle" name="warranty_months" id="warranty_months">
                                    <?php
                                    for ($i = 0; $i <= 11; $i++) {
                                        echo "<option value=\"$i\" " . (isset($html_values['WARRANTY_MONTHS']) && $html_values['WARRANTY_MONTHS'] == $i ? 'selected' : '') . ">{$i} 개월</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="dDate">유지보수종료일</label></td>
                            <td><input type="date" class="input short" name="dDate" id="dDate" value="<?php echo $html_values['D_DATE'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="ref">비고</label></td>
                            <td><textarea class="txtarea" name="ref" id="ref" rows="4" cols="52"><?php echo isset($html_values['REF']) ? $html_values['REF'] : ''; ?></textarea></td>
                        </tr>
                    </table>
                    <div class="btn-class">
                        <button type="submit" class="btn btn-primary update wide-btn">수정</button>
                        <button class="btn btn-primary delete wide-btn" hx-post="salesDelete.php?saleId=<?php echo $html_values['SALE_ID']; ?>" hx-confirm="정말 삭제하시겠습니까?" hx-swap="outerHTML" hx-redirect="salesMain.php" hx-target="#updateForm">삭제</button>
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
                endDate.setDate(endDate.getDate() - 1); // 하루를 빼는 부분
                return endDate.toISOString().split('T')[0]; // 날짜 형식 (YYYY-MM-DD)으로 반환
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

            // 수정시 TOT_PRICE에서 ,000이 사라지는 문제점 
            /* 문제 :  number_format 함수. number_format 함수는 숫자를 천 단위로 포맷팅하여 쉼표(,)를 포함한 문자열로 반환합니다. 예를 들면, number_format(1000)은 1,000을 반환합니다.
            폼을 제출할 때 이러한 포맷의 값을 직접 데이터베이스에 저장하려고 하면 문제가 발생할 수 있습니다. 데이터베이스에 저장될 때는 숫자만 저장해야 합니다. 쉼표를 포함한 문자열을 숫자로 변환하려고 할 때 PHP는 그 값을 0으로 처리합니다.
            
            해결책:
            폼을 제출할 때 totPrice 값을 숫자만으로 제출해야 합니다.
            사용자가 폼을 작성할 때는 쉼표가 포함된 포맷으로 값을 보여주고, 폼 제출 전에 JavaScript를 사용하여 쉼표를 제거한 후 제출합니다.*/
            document.getElementById('updateForm').addEventListener('submit', function() {
                // 디버깅
                // event.preventDefault(); // 폼 제출을 일시적으로 막습니다.
                // alert('Form is being submitted!');
                var totPriceInput = document.getElementById('totPrice');
                totPriceInput.value = totPriceInput.value.replace(/,/g, ''); // 쉼표 제거
                // console.log(totPriceInput.value);  // totPriceInput의 값을 콘솔에 출력합니다.

            });
        });
    </script>
</body>

</html>