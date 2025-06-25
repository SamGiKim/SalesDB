<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

function updateDevice($dbconnect, $type, $price, $sDate, $dDate, $ref, $warranty, $saleId, $SN) {
    $query = "UPDATE LICENSE SET `TYPE`=?, PRICE=?, S_DATE=?, D_DATE=?, REF=?, WARRANTY=?, INSPECTION=?, SUPPORT=? WHERE SALE_ID=? AND SN=?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("sisssissss", $type, $price, $sDate, $dDate, $ref, $warranty, $inspection, $support, $saleId, $SN);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}
function calculateDDate($sDate, $warranty) {
    $date = new DateTime($sDate);
    $date->modify("+$warranty months - 1 day");
    return $date->format('Y-m-d');
}

$html_values = [];

function validateInputs($dbconnect, $saleId, $SN) {
    // TODO: 입력값 검증 로직 추가
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 폼이 제출된 경우 처리
    $saleId = $_POST['saleId'] ?? '';
    $SN = $_POST['SN'] ?? '';
    $type = $_POST['type'] ?? '';
    $price = $_POST['price'] ?? '';
    $sDate = $_POST['sDate'] ?? '';
    $dDate = $_POST['dDate'] ?? '';
    $ref = $_POST['ref'] ?? '';
    $inspection = $_POST['inspection'] ?? '';
    $support = $_POST['support'] ?? '';
    $existingLicense = fetchLicense($dbconnect, $saleId, $SN);
    $warranty = $existingLicense['WARRANTY'];
    //post에서 직접 가져오는 대신, db의 fetchLicense 함수 사용하여 warranty 값 가져오면
    // $warranty = isset($_POST['warranty']) ? intval($_POST['warranty']) : 0;


    if (!validateInputs($dbconnect, $saleId, $SN)) {
        echo "<script>alert('유효하지 않은 입력!');</script>";
        exit();
    }

    $dDate = calculateDDate($sDate, $warranty);
    $affected = updateLicense($dbconnect, $type, $price, $sDate, $dDate, $ref, $warranty, $inspection, $support,  $saleId, $SN);

    if ($affected > 0) {
        echo "<script>alert('업데이트 성공!'); window.location.href='licenseMain.php';</script>";
    } else {
        echo "<script>alert('업데이트 실패!');</script>";
    }
    $html_values = fetchLicense($dbconnect, $saleId, $SN);
} else {
    //페이지가 로드되는 경우 처리
    $saleId = $_GET['saleId'] ?? null;
    $SN = $_GET['SN'] ?? null;
}
if ($html_values) {
    // 여기서 $html_values 사용
} else {
    echo "작업중";
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>장비 수정</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="salesMain.css">
    <script src="https://unpkg.com/htmx.org@1.9.4"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.27.0/dist/date-fns.umd.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="main">
        <div class="header-container">
            <header>장비 수정</header>
        </div>
        <div class="content">
            <div class="inputBox mx-auto shadow p-5 mt-4">
                <div class="btn-cancel position-relative top-0">
                    <button type="button" class="btn-close" aria-label="Close" onclick="redirectToDeviceMain()"></button>
                </div>
                <form id="updateForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <table class="inputTbl">
                    <tr>
                            <td><label for="SN">시리얼번호<span style="color: red;">*</span></label></td>
                            <td>
                                <input type="text" class="input" name="SN" id="SN" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="orderNo">주문번호<span style="color: red;">*</span></label></td>
                            <td>
                                <input type="text" class="input" name="orderNo" id="orderNo" required>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="model">모델명</label></td>
                            <td>
                                <select class="input short selectstyle" name="model" id="model">
                                    <option value="" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '' ? 'selected' : ''; ?>>선택</option>
                                    <option value="10000Q" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '10000Q' ? 'selected' : ''; ?>>10000Q</option>
                                    <option value="10002Q" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '10002Q' ? 'selected' : ''; ?>>10002Q</option>
                                    <option value="4500Q" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '4500Q' ? 'selected' : ''; ?>>4500Q</option>
                                    <option value="6502Q" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '6502Q' ? 'selected' : ''; ?>>6502Q</option>
                                    <option value="6552Q" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '6552Q' ? 'selected' : ''; ?>>6552Q</option>
                                    <option value="8500Q" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '8500Q' ? 'selected' : ''; ?>>8500Q</option>
                                    <option value="8502Q" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '8502Q' ? 'selected' : ''; ?>>8502Q</option>
                                    <option value="CMS SV" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'CMS SV' ? 'selected' : ''; ?>>CMS SV</option>
                                    <option value="LogSV" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'LogSV' ? 'selected' : ''; ?>>LogSV</option>
                                    <option value="N100" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'N100' ? 'selected' : ''; ?>>N100</option>
                                    <option value="N200" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'N200' ? 'selected' : ''; ?>>N200</option>
                                    <option value="N250" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'N250' ? 'selected' : ''; ?>>N250</option>
                                    <option value="N300" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'N300' ? 'selected' : ''; ?>>N300</option>
                                    <option value="N500" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'N500' ? 'selected' : ''; ?>>N500</option>
                                    <option value="NP1000" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'NP1000' ? 'selected' : ''; ?>>NP1000</option>
                                    <option value="NP300" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'NP300' ? 'selected' : ''; ?>>NP300</option>
                                    <option value="S100" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'S100' ? 'selected' : ''; ?>>S100</option>
                                    <option value="S200" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'S200' ? 'selected' : ''; ?>>S200</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr> 
                        <tr>
                            <td><label for="devType">장비유형</label></td>
                            <td>
                                <select class="input short selectstyle" name="devType" id="devType">
                                    <option value="" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '' ? 'selected' : ''; ?>>선택</option>
                                    <option value="J201" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == 'J201' ? 'selected' : ''; ?>>J201</option>
                                    <option value="D307" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == 'D307' ? 'selected' : ''; ?>>D307</option>
                                    <option value="2052" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '2052' ? 'selected' : ''; ?>>2052</option>
                                    <option value="2070" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '2070' ? 'selected' : ''; ?>>2070</option>
                                    <option value="3040" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '3040' ? 'selected' : ''; ?>>3040</option>
                                    <option value="4030" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '4030' ? 'selected' : ''; ?>>4030</option>
                                    <option value="5030" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '5030' ? 'selected' : ''; ?>>5030</option>
                                    <option value="3.5U" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '3.5U' ? 'selected' : ''; ?>>3.5U</option>
                                    <option value="5U" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '5U' ? 'selected' : ''; ?>>5U</option>
                                    <option value="A1411" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == 'A1411' ? 'selected' : ''; ?>>A1411</option>
                                    <option value="A2032" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == 'A2032' ? 'selected' : ''; ?>>A2032</option>
                                    <option value="D304" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == 'D304' ? 'selected' : ''; ?>>D304</option>
                                    <option value="N801" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == 'N801' ? 'selected' : ''; ?>>N801</option>
                                    <option value="N803" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == 'N803' ? 'selected' : ''; ?>>N803</option>
                                    <option value="NK1" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == 'NK1' ? 'selected' : ''; ?>>NK1</option>
                                    <option value="-" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '-' ? 'selected' : ''; ?>>-</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="interface">인터페이스</label></td>
                            <td>
                                <select class="input short selectstyle" name="interface" id="interface">
                                    <option value="" <?php echo isset($html_values['INTERFACE']) && $html_values['INTERFACE'] == '' ? 'selected' : ''; ?>>선택</option>
                                    <option value="1G" <?php echo isset($html_values['INTERFACE']) && $html_values['INTERFACE'] == '1G' ? 'selected' : ''; ?>>1G</option>
                                    <option value="10G" <?php echo isset($html_values['INTERFACE']) && $html_values['INTERFACE'] == '10G' ? 'selected' : ''; ?>>10G</option>
                                    <option value="1G/10G" <?php echo isset($html_values['INTERFACE']) && $html_values['INTERFACE'] == '1G/10G' ? 'selected' : ''; ?>>1G/10G</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="ikind">인터페이스 유형</label></td>
                            <td>
                                <select class="input short selectstyle" name="ikind" id="ikind">
                                    <option value="" <?php echo isset($html_values['IKIND']) && $html_values['IKIND'] == '' ? 'selected' : ''; ?>>선택</option>
                                    <option value="Copper" <?php echo isset($html_values['IKIND']) && $html_values['IKIND'] == 'Copper' ? 'selected' : ''; ?>>Copper</option>
                                    <option value="F/C" <?php echo isset($html_values['IKIND']) && $html_values['IKIND'] == 'F/C' ? 'selected' : ''; ?>>F/C</option>
                                    <option value="Fiber" <?php echo isset($html_values['IKIND']) && $html_values['IKIND'] == 'Fiber' ? 'selected' : ''; ?>>Fiber</option>
                                    <option value="-" <?php echo isset($html_values['IKIND']) && $html_values['IKIND'] == '-' ? 'selected' : ''; ?>>-</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="intNum">회선수</label></td>
                            <td>
                                <input type="text" class="input" name="intNum" id="intNum">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="wDate">제조일</label></td>
                            <td>
                                <input type="date" class="input short" name="wDate" id="wDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="capacity">대역폭</label></td>
                            <td>
                                <input type="text" class="input" name="capacity" id="capacity">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="HDD">디스크</label></td>
                            <td>
                                <select class="input short selectstyle" name="HDD" id="HDD">
                                    <option value="" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '' ? 'selected' : ''; ?>>선택</option>
                                    <option value="1TB" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '1T' ? 'selected' : ''; ?>>1T</option>
                                    <option value="2TB * 1(EA)" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '2TB X 1(EA)' ? 'selected' : ''; ?>>2TB X 1(EA)</option>
                                    <option value="2TB X 2(EA)" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '2TB X 2(EA)' ? 'selected' : ''; ?>>2TB X 2(EA)</option>
                                    <option value="8TB X 2(EA)" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '8TB X 2(EA)' ? 'selected' : ''; ?>>8TB X 2(EA)</option>
                                    <option value="8TB X 7(EA)" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '8TB X 7(EA)' ? 'selected' : ''; ?>>8TB X 7(EA)</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="memory">메모리</label></td>
                            <td>
                                <select class="input short selectstyle" name="memory" id="memory">
                                    <option value="" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '' ? 'selected' : ''; ?>>선택</option>
                                    <option value="32M" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '32M' ? 'selected' : ''; ?>>32M</option>
                                    <option value="64M" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '64M' ? 'selected' : ''; ?>>64M</option>
                                    <option value="128M" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '128M' ? 'selected' : ''; ?>>128M</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                    </table>
                    <div class="btn-class">
                        <button type="submit" class="btn btn-primary update wide-btn">수정</button>
                    </div>
                </form>        
            </div>
        </div>
    </div>
    <script>
        
     </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
</body>
</html> 