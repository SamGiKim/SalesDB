<?php
// 초기화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

session_start();

$html_values = [];

//유입경로 1 : 일반적으로 들어올 때

// SALE_ID 체크
function saleIdExists($dbconnect, $saleId) {
    $query = "SELECT 1 FROM SALES WHERE SALE_ID = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}

// SALE_ID로 WARRANTY 가져오기
function getWarranty($dbconnect, $saleId) {
    $query = "SELECT WARRANTY FROM SALES WHERE SALE_ID = ?";  
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data['WARRANTY'] ?? null;
}

// 복합키값인 SALE_ID X SN 이 있는지 체크
function licenseExists($dbconnect, $saleId, $SN) {
    $query = "SELECT * FROM LICENSE WHERE SALE_ID = ? AND SN = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("ss", $saleId, $SN);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}

// INSERT
function insertLicense($dbconnect, $data) {
    $stmt = $dbconnect->prepare("INSERT INTO LICENSE (SALE_ID, SN, `TYPE`, MANAGER, PRICE, WARRANTY, S_DATE, D_DATE, INSPECTION, SUPPORT, REF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiisssss", $data['saleId'], $data['SN'], $data['type'], $data['manager'], $data['price'], $data['warranty'], $data['sDate'], $data['dDate'], $data['inspection'], $data['support'], $data['ref']);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

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

// Main Logic - 함수로 리팩토링해서 표현하기
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        'saleId' => isset($_POST["saleId"]) ? $_POST["saleId"] : '',
        'SN' => isset($_POST["SN"]) ? $_POST["SN"] : '',
        'type' => $_POST["type"] ? $_POST["type"] : '',
        'manager' => $_POST["manager"] ? $_POST["manager"] : '',
        'price' => isset($_POST["price"]) && $_POST["price"] !== "" ? $_POST["price"] : null,
        'sDate' => $_POST["sDate"] !== "" ? $_POST["sDate"] : null,
        'dDate' => $_POST["dDate"] !== "" ? $_POST["dDate"] : null,
        'ref' => $_POST["ref"] !== "" ? $_POST["ref"] : null,
        'inspection' => $_POST["inspection"] !== "" ? $_POST["inspection"] : null,
        'support' => $_POST["support"] !== "" ? $_POST["support"] : null
    ];
    
    // var_dump($data['saleId'], $data['SN']);

    if (!saleIdExists($dbconnect, $data['saleId'])) {
        $errors['saleId'] = "존재하지 않는 명세서 번호입니다. 거래명세서 확인 후 등록하시기 바랍니다.";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: licenseInsert.php");
        exit;
    }

    $data['warranty'] = getWarranty($dbconnect, $data['saleId']);

    if (licenseExists($dbconnect, $data['saleId'], $data['SN'])) {
        echo "<script>alert('중복된 SALE_ID, SN 키 값이 존재합니다.'); window.location.href='licenseMain.php';</script>";
        exit();
    } 

    if (!insertLicense($dbconnect, $data)) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    } else {
        // var_dump($_POST);
        echo "<script>
        alert('데이터가 성공적으로 입력되었습니다.');
        location.href = 'licenseMain.php';
        </script>";
    }
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
                            <input type="text" class="input" name="saleId" id="saleId" required>
                            <span class="error-message" id="error-saleId"></span>
                        </td>
                    </tr>
                    <tr> 
                    <td><label for="SN">시리얼번호<span style="color: red;">*</span></label></td>
                    <td>
                        <input type="text" class="input" name="SN" id="SN" required>
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
                        <td><label for="manager">담당 엔지니어</label></td>
                        <td>
                            <select class="input short selectstyle" name="manager" id="manager">
                                <option value="하진구" <?php echo isset($html_values['MANAGER']) && $html_values['MANAGER'] == '하진구' ? 'selected' : ''; ?>>하진구</option>
                                <option value="이재길" <?php echo isset($html_values['MANAGER']) && $html_values['MANAGER'] == '이재길' ? 'selected' : ''; ?>>이재길</option>
                                <option value="김두호" <?php echo isset($html_values['MANAGER']) && $html_values['MANAGER'] == '김두호' ? 'selected' : ''; ?>>김두호</option>
                                <option value="이시호" <?php echo isset($html_values['MANAGER']) && $html_values['MANAGER'] == '이시호' ? 'selected' : ''; ?>>이시호</option>
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
                                <input type="text" class="input short" name="warranty" id="warranty" value="<?php echo $html_values['WARRANTY'] ?? ''; ?>" style="text-align:right; padding-right: 30px; font-size:15px;"><span>개월</span>
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
                        <!-- 2024.03.20 변경 : T5: 지원없음, T3: 정기점검, T1:유지 보수-->
                        <td>
                            <select class="input short selectstyle" name="support" id="support">
                                <option value="" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T0:tbd' ? 'selected' : ''; ?>>선택안함</option>
                                <option value="T5:지원없음" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T5:지원없음' ? 'selected' : ''; ?>>T5:지원없음</option>
                                <option value="T3:정기점검" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T3:정기점검' ? 'selected' : ''; ?>>T3:정기점검</option>
                                <option value="T1:유지보수" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T1:유지보수' ? 'selected' : ''; ?>>T1:유지보수</option>
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


/* -------------------------------------------------------------------------- */
/*                          licenseInsert 페이지       유효성검사                      */
/* -------------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {
    debugger;
    console.log("licenseInsert JS 도달!");

    document.getElementById('lcsInsertForm').addEventListener('submit', function (event) {
        console.log("licenseInsert form 제출");
        event.preventDefault(); // 유효성 검사 후 조건 만족할 때만 폼 제출

        let SN = document.getElementById('SN').value;
        console.log('SN 값 : ', SN);
        let saleId = document.getElementById('saleId').value;
        console.log('saleId 값 : ', saleId);
        let price = document.getElementById('price').value;
        let type = document.getElementById('type').value;
        let errors = [];

        // 에러 메시지 초기화
        document.getElementById('error-saleId').textContent = '';
        document.getElementById('error-SN').textContent = '';
        document.getElementById('error-type').textContent = '';
        document.getElementById('error-price').textContent = '';

        if (!saleId) {
            console.log('saleId가 없음!');
            errors['saleId'] = "명세서번호(SALE_ID)를 입력해주세요.";
            document.getElementById('error-saleId').textContent = errors['saleId'];
        }

        if (type !== "무상" && !SN) {
            errors['SN'] = "유상일 경우 시리얼번호(SN)를 입력해주세요.";
            document.getElementById('error-SN').textContent = errors['SN'];
        }

        if (type === "유상" && price === "") {
            errors['price'] = "유상인 경우 금액을 입력해주세요.";
            document.getElementById('error-price').textContent = errors['price'];
        }

        if (Object.keys(errors).length) {
            console.log('에러 발생!', errors);
        } else {
            console.log("에러 없음");
            this.submit();
        }
    });

});
// 
    </script>
</body>
</html>