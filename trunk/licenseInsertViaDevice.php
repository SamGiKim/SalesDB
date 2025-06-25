<?php
// 초기화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$html_values = [];
require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

// 장비의 ORDER_NO로 SALES의 SALE_ID, WARRANTY, 불러오기
function fetchBySN($dbconnect, $SN) {
    $sql = "SELECT S.SALE_ID, S.WARRANTY, D.SN
            FROM DEVICE D
            INNER JOIN SALES S ON S.ORDER_NO = D.ORDER_NO
            WHERE D.SN = ?"; 
    $stmt = mysqli_prepare($dbconnect, $sql);
    mysqli_stmt_bind_param($stmt, 's', $SN);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $data;
}

//GET 요청 처리(거래명세서 SN 클릭해서 들어올 때)
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['SN'])) {
    $SN = $_GET['SN'] ?? '';
    $saleId = $_GET['saleId'] ?? '';

    if (!empty($SN)) {
        // SN 값이 존재하면 라이센스 정보 조회
        $data = fetchBySN($dbconnect, $SN);

        if ($data) {
            $html_values['SALE_ID'] = $data['SALE_ID'];
            $html_values['WARRANTY'] = $data['WARRANTY'];
            $html_values['SN'] = $data['SN'];
        } else {
            $html_values['SALE_ID'] = ''; 
            $html_values['WARRANTY'] = ''; 
            $html_values['SN'] = ''; 
        }
    } else {
        // SN 값이 없을 때의 처리 (예: 오류 처리)
    }
    $html_values['TYPE'] = '무상';
    // var_dump($html_values);

    // insertLicense 함수를 호출하여 라이센스 생성

}

// POST 요청 처리(LF:SF로 보냈을 때)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['SN']) && isset($_POST['saleId'])) {
    $SN = $_POST['SN'];
    $saleId = $_POST['saleId'];

    $query = "SELECT WARRANTY, TOT_PRICE, S_DATE, D_DATE FROM SALES WHERE SALE_ID = ?";
    $stmt = mysqli_prepare($dbconnect, $query);
    mysqli_stmt_bind_param($stmt, "s", $saleId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    $html_values['WARRANTY'] = $row['WARRANTY'];
    $html_values['PRICE'] = 0;  // 가격을 0으로 설정
    $html_values['TYPE'] = '무상';  // 유형을 '무상'으로 설정
    $html_values['S_DATE'] = $row['S_DATE'];
    $html_values['D_DATE'] = $row['D_DATE'];

    mysqli_stmt_close($stmt);

    // 폼 데이터 처리 및 라이센스 생성
}


?>



<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>라이센스 신규 입력</title>
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
          <header>신규 라이센스 등록</header>
        </div>
        <div class="content">
          <div class="inputBox mx-auto shadow p-5 mt-4">
              <div class="btn-cancel position-relative top-0">
                  <button type="button" class="btn-close" aria-label="Close" onclick="redirectToLicenseMain()"></button>
              </div>
              <div class="error-messages">
              <?php if (isset($errors['saleId'])): ?>
                <div class="error-message" id="error-saleId"><?php echo $errors['saleId']; ?></div>
            <?php endif; ?>
            <?php if (isset($errors['SN'])): ?>
                <div class="error-message" id="error-SN"><?php echo $errors['SN']; ?></div>
            <?php endif; ?>
            </div>
              <form id="lcsInsertForm" method="post" action="licenseInsert.php">
                  <table class="inputTbl">
                  <tr>
                        <td><label for="saleId">명세서번호<span style="color: red;">*</span></label></td>
                        <td>
                            <input type="text" class="input" name="saleId" id="saleId" value="<?php echo $saleId; ?>">
                            <span class="error-message" id="error-saleId"></span>
                        </td>
                    </tr>
                    <tr> 
                    <td><label for="SN">시리얼번호<span style="color: red;">*</span></label></td>
                    <td>
                        <input type="text" class="input" name="SN" id="SN" value="<?php echo $SN; ?>">
                        <span class="error-message" id="error-SN"></span>
                    </td>
                  </tr>
                    <tr>
                        <td><label for="type">유지보수 유형</label></td>
                        <td>
                            <select class="input short selectstyle" name="type" id="type">
                                <option value="유상" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '유상' ? 'selected' : ''; ?>>유상</option>
                                <option value="무상" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '무상' ? 'selected' : ''; ?>>무상</option>
                                <option value="건당" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '건당' ? 'selected' : ''; ?>>건당</option>
                            </select>
                            <span class="error-message" id="error-type"></span>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="price">라이센스 가격</label></td>
                        <td>
                            <input type="number" min="0" class="input short" name="price" id="price" value="<?php echo $html_values['PRICE'] ?? ''; ?>">
                            <span>원</span>
                            <span id="error-price" class="error-message"></span> 
                        </td>
                    </tr>
                    <tr>
                            <td><label for="warranty">보증기간</label></td>
                            <td>
                                <input type="text" class="input short" name="warranty" id="warranty" value="<?php echo $html_values['WARRANTY'] ?? ''; ?>" style="text-align:right; padding-right: 30px; font-size:15px;" placeholder="자동 입력"><span>개월</span>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                    <tr>
                      <td><label for="sDate">유지보수 시작일</label></td>
                      <td>
                        <input type="date" class="input short" name="sDate" id="sDate" value="<?php echo $html_values['S_DATE'] ?? ''; ?>">
                        <span class="error-message">&nbsp;</span>
                      </td>
                    </tr>
                    <tr>
                      <td><label for="dDate">유지보수 종료일</label></td>
                      <td>
                        <input type="date" class="input short" name="dDate" id="dDate"  value="<?php echo $html_values['D_DATE'] ?? ''; ?>">
                        <span class="error-message">&nbsp;</span>
                     </td>
                    </tr>
                    <tr>
                        <td><label for="inspection">점검</label></td>
                        <!--건별, 월 방문, 월 원격, 분기 방문, 분기 원격, 반기 방문, 반기 원격, 비고참조-->
                        <td>
                            <select class="input short selectstyle" name="inspection" id="inspection">
                                <option value="" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '건별' ? 'selected' : ''; ?>>선택안함</option>
                                <option value="건별" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '건별' ? 'selected' : ''; ?>>건별</option>
                                <option value="월방문" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '월방문' ? 'selected' : ''; ?>>월방문</option>
                                <option value="월원격" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '월원격' ? 'selected' : ''; ?>>월원격</option>
                                <option value="분기방문" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '분기방문' ? 'selected' : ''; ?>>분기방문</option>
                                <option value="분기원격" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '분기원격' ? 'selected' : ''; ?>>분기원격</option>
                                <option value="반기방문" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '반기방문' ? 'selected' : ''; ?>>반기방문</option>
                                <option value="반기원격" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '반기원격' ? 'selected' : ''; ?>>반기원격</option>
                                <option value="비고참조" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '비고참조' ? 'selected' : ''; ?>>비고참조</option>
                            </select>
                            <span class="error-message" id="error-type"></span>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="support">파트너지원</label></td>
                        <!-- T0: 지원없음, T1: 정기점검, T2:유지 보수-->
                        <td>
                            <select class="input short selectstyle" name="support" id="support">
                                <option value="" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T0:tbd' ? 'selected' : ''; ?>>선택안함</option>
                                <option value="T0:지원없음" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T0:지원없음' ? 'selected' : ''; ?>>T0:지원없음</option>
                                <option value="T1:정기점검" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T1:정기점검' ? 'selected' : ''; ?>>T1:정기점검</option>
                                <option value="T2:유지보수" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T2:유지보수' ? 'selected' : ''; ?>>T2:유지보수</option>
                            </select>
                            <span class="error-message" id="error-type"></span>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="ref">비고</label></td>
                        <td>
                            <textarea class="txtarea" name="ref" id="ref" rows="2" cols="52"></textarea>
                        </td>
                    </tr>
                  </table>
                  <div class="error-message-box" style="color: red; text-align: center;">
                  <?php 
                        if (isset($_SESSION['errors'])) {
                            echo implode("<br>", $_SESSION['errors']);
                            // 출력 후 에러 메시지 세션을 제거
                            unset($_SESSION['errors']);
                        }
                    ?>
                    </div>
                  <div class="btn-class">
                      <button type="submit" class="btn btn-primary wide-btn insert">등록</button>
                  </div>
              </form>         
          </div>
      </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script>
        document.getElementById('saleId').addEventListener('click', function() {
            this.removeAttribute('readonly');
        });

        //type이 무상일 때, price 0원으로 비활성화
        document.addEventListener('DOMContentLoaded', function() {
            // 유지보수 유형 select 요소를 가져옵니다.
            let typeSelect = document.getElementById('type');
            // 라이센스 가격 input 요소를 가져옵니다.
            let priceInput = document.getElementById('price');

            // 유지보수 유형 select 요소에 변경 이벤트 리스너를 추가합니다.
            typeSelect.addEventListener('change', function() {
                // 선택된 값이 '무상'인 경우
                if (this.value === '무상') {
                    // 라이센스 가격을 0으로 설정하고 비활성화합니다.
                    priceInput.value = 0;
                    priceInput.setAttribute('disabled', true);
                } else {
                    // 그렇지 않은 경우, 입력란을 활성화합니다.
                    priceInput.removeAttribute('disabled');
                }
            });

            // 페이지 로드 시 초기 설정을 위한 함수 호출
            typeSelect.dispatchEvent(new Event('change'));
            console.log('typeSelect 변경 이벤트 발생');
        });
    </script>
</body>
</html>