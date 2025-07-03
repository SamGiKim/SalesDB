<?php
//deviceSearch.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php"; // 데이터베이스 연결 설정 파일
mysqli_set_charset($dbconnect, "utf8");

// 에러 로깅을 위한 함수
function log_error($message)
{
    error_log($message);
    echo "<script>alert('오류가 발생했습니다.');</script>";
}

// SQL 삽입 공격 방지를 위한 함수
function sanitize_input($input)
{
    global $dbconnect;
    return mysqli_real_escape_string($dbconnect, trim($input));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>장비 검색</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="salesMain.css">
    <script src="https://unpkg.com/htmx.org@1.9.4"></script>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="main">
        <div class="header-container">
            <header>장비 검색</header>
        </div>
        <div class="content">
            <div class="inputBox mx-auto shadow p-5 mt-4">
                <div class="btn-cancel position-relative top-0">
                    <button type="button" class="btn-close" aria-label="Close" onclick="goToDeviceMain(event)"></button>
                </div>
                <form id="deviceSearchForm" method="post">
                    <table class="inputTbl">
                        <tr>
                            <td><label for="SN">시리얼번호</label></td>
                            <td>
                                <input type="text" class="input" name="SN" id="SN">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="orderNo">주문번호</label></td>
                            <td>
                                <input type="text" class="input" name="orderNo" id="orderNo">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="model">모델명</label></td>
                            <td>
                                <select class="input short selectstyle" name="model" id="model">
                                    <option value="" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == '' ? 'selected' : ''; ?>>선택안함</option>
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
                                    <option value="NP500" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'NP500' ? 'selected' : ''; ?>>NP500</option>
                                    <option value="S100" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'S100' ? 'selected' : ''; ?>>S100</option>
                                    <option value="S200" <?php echo isset($html_values['MODEL']) && $html_values['MODEL'] == 'S200' ? 'selected' : ''; ?>>S200</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="FV">FV</label></td>
                            <td>
                            <input type="text" class="input" name="FV" id="FV">
                            <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="devType">장비유형</label></td>
                            <td>
                                <select class="input short selectstyle" name="devType" id="devType">
                                    <option value="" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '' ? 'selected' : ''; ?>>선택안함</option>
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
                                    <option value="" <?php echo isset($html_values['INTERFACE']) && $html_values['INTERFACE'] == '' ? 'selected' : ''; ?>>선택안함</option>
                                    <option value="1G" <?php echo isset($html_values['INTERFACE']) && $html_values['INTERFACE'] == '1G' ? 'selected' : ''; ?>>1G</option>
                                    <option value="10G" <?php echo isset($html_values['INTERFACE']) && $html_values['INTERFACE'] == '10G' ? 'selected' : ''; ?>>10G</option>
                                    <option value="1G/10G" <?php echo isset($html_values['INTERFACE']) && $html_values['INTERFACE'] == '1G/10G' ? 'selected' : ''; ?>>1G/10G</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="ikind">포트</label></td>
                            <td>
                                <select class="input short selectstyle" name="ikind" id="ikind">
                                    <option value="" <?php echo isset($html_values['IKIND']) && $html_values['IKIND'] == '' ? 'selected' : ''; ?>>선택안함</option>
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
                                <input type="text" class="input short" name="intNum" id="intNum">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="capacity">용량</label></td>
                            <td>
                                <input type="text" class="input short" name="capacity" id="capacity">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="HDD">디스크</label></td>
                            <td>
                                <select class="input short selectstyle" name="HDD" id="HDD">
                                    <option value="" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '' ? 'selected' : ''; ?>>선택안함</option>
                                    <option value="500G" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '500G' ? 'selected' : ''; ?>>500G</option>
                                    <option value="1TB" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '1T' ? 'selected' : ''; ?>>1T</option>
                                    <option value="2TB X 1(EA)" <?php echo isset($html_values['DEV_TYPE']) && $html_values['DEV_TYPE'] == '2TB X 1(EA)' ? 'selected' : ''; ?>>2TB X 1(EA)</option>
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
                                    <option value="" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '' ? 'selected' : ''; ?>>선택안함</option>
                                    <option value="4G" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '4G' ? 'selected' : ''; ?>>4G</option>
                                    <option value="8G" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '8G' ? 'selected' : ''; ?>>8G</option>
                                    <option value="16G" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '16G' ? 'selected' : ''; ?>>16G</option>
                                    <option value="32G" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '32G' ? 'selected' : ''; ?>>32G</option>
                                    <option value="64G" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '64G' ? 'selected' : ''; ?>>64G</option>
                                    <option value="128G" <?php echo isset($html_values['MEMORY']) && $html_values['MEMORY'] == '128G' ? 'selected' : ''; ?>>128G</option>
                                </select>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                    </table>
                    <div class="btn-class">
                        <button type="submit" class="btn btn-primary search wide-btn">검색</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
    <script>
        function goToDeviceMain(event) {
            event.preventDefault();
            window.location.href = "deviceMain.php";
        }

        document.getElementById('deviceSearchForm').addEventListener('submit', function(event) {
            event.preventDefault(); // 폼의 기본 제출 동작을 방지

            var form = this; // 현재 폼 요소를 변수 form에 할당
            var formData = new FormData(form); // formData 객체를 사용하여 폼 요소의 데이터를 가져온다. 이 객체로 HTML 폼을 통해 전송된 키-값 처리
            var queryString = new URLSearchParams(formData).toString(); // formData 객체를 URL 쿼리 문자열로 변환

            window.location.href = "deviceMain.php?" + queryString; // 쿼리 스트링을 포함하여 deviceMain.php로 리다이렉션(GET으로 요청됨)
        });
    </script>
</body>

</html>