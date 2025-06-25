<?php
// 에러확인
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

$html_values = array();

require_once "sales_db.php";

mysqli_set_charset($dbconnect, "utf8");

//중복 코드 함수화(필요시 넣기)

//지원팀 요구사항: 1) 신규입력시 SALE_ID, SN 100% 있어야한다. 단, 무상일때는 SN이 없을 수 있다. 
//라이센스 신규 입력에서 필수 조건 2개(명세서번호가 SALES 테이블에 있는가? AND SN번호가 DEVICES 테이블에 있는가?). 이를 구현하기 위해서 DEVICES 테이블(SN 컬럼 1개)
// 유효성 검사 : SALE_ID, SN

//기본 값 설정
$html_values = [];


// 1) 유입경로 1 - 사용자가 빈 칸에 처음 넣을 때
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    error_log("유입경로 1로 들어왔을때");
    echo "유입경로 1";
    $saleId = $_POST["saleId"];
    $SN = $_POST["SN"];
    $type = $_POST["type"];
    //isset()으로 함수 변수가 설정되어있는지 여부 확인하고 null이 아니면 true. 빈 문자열이 아닌 경우에만 $price 값 할당. 
    //설정되지 않았거나 빈 문자열이라면 null할당.
    $price = isset($_POST["price"]) && $_POST["price"] !== "" ? $_POST["price"] : null;
    // $price = $_POST["price"] !== "" ? $_POST["price"] : null; 이 코드는 빈 문자열인지만 확인해서 PHP경고를 발생시킴
    $sDate = $_POST["sDate"] !== "" ? $_POST["sDate"] : null;
    $dDate = $_POST["dDate"] !== "" ? $_POST["dDate"] : null;
    $ref = $_POST["ref"] !== "" ? $_POST["ref"] : null;
    $inspection = $_POST["inspection"] !== "" ? $_POST["inspection"] : null;
    $support = $_POST["support"] !== "" ? $_POST["support"] : null;
    var_dump($saleId, $SN);

    $errors = array();    

    // SALE_ID SALES 테이블에 있는지 확인
    $query = "SELECT 1 FROM SALES WHERE SALE_ID = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $errors['saleId'] = "존재하지 않는 명세서 번호입니다. 거래명세서 확인 후 등록하시기 바랍니다.";
    }

    // SN DEVICE 테이블에 있는지 확인
    /*
    $query = "SELECT 1 FROM DEVICE WHERE SN = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $SN);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows===0){
        $errors['SN'] = "존재하지 않는 장비 SN입니다. 장비 확인 후 등록하시기 바랍니다.";
    }
    */

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: licenseInsert.php");
        exit;
    }

    $query = "SELECT WARRANTY FROM SALES WHERE SALE_ID = ?";  
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $html_values['WARRANTY'] = $row['WARRANTY'];
    }
    $warranty = isset($html_values['WARRANTY']) ? $html_values['WARRANTY'] : 0;

    // 중복된 saleId와 SN이 있는지 확인
    $query = "SELECT * FROM LICENSE WHERE SALE_ID = ? AND SN = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("ss", $saleId, $SN);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) { // 중복된 엔트리가 있을 경우
        echo "<script>alert('중복된 SALE_ID, SN 키 값이 존재합니다.'); window.location.href='licenseMain.php';</script>";
        exit();
    } else {
    //insert
    //각 SALE_ID와 SN에 대한 NO은 TRIGGER 걸려 있으므로 따로 안넣어준다.
    //중복 엔트리 없을 경우 INSERT
    $stmt = $dbconnect->prepare("INSERT INTO LICENSE (SALE_ID, SN, `TYPE`, PRICE, WARRANTY, S_DATE, D_DATE, INSPECTION, SUPPORT, REF)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?)");
    $stmt->bind_param(
        "sssiisssss",
        $saleId, $SN, $type, $price, $warranty, $sDate, $dDate, $inspection, $support, $ref
        );

    // 실행시키기
    if(!$stmt->execute()){
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    } else {
        var_dump($_POST);
        echo "<script>
        alert('데이터가 성공적으로 입력되었습니다.');
        location.href = 'licenseMain.php';
        </script>";
    }
    $stmt->close();
} 
}
// 2) 유입경로 2 - SN으로 받아왔을 때
elseif ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['SN'])) {
    error_log("유입경로 2로 들어왔을때");
    echo "=========유입경로 2 debugging===============";
    $SN = mysqli_real_escape_string($dbconnect, $_GET['SN']); // SQL Injection 방지를 위해 데이터 정제
    $SN = str_replace('%', '', $SN); // % 기호 제거
    /* 
    !!! 에러사항 및 해결 
    1) 와일드 카드 때문에 '$SN' 값에 %가 들어가 있었다. %를 제거해야 정제된 값을 얻어올 수 있다.
        정확한 SN 값을 찾아서 ORDER_NO를 매개변수로 해서 SALES 테이블의 값을 가져오려면, SN의 %를 지우는 게 맞다.
    2) $SN을 정제한 후 다시 오염시키는 if (isset($_GET['SN'])) { 블록을 지워야한다.
        이 부분에서 $SN은 다시 원래의 $_GET['SN'] 값으로 덮어씌워지고 있기 때문이다.
    */

    // 디버깅
    echo "SN값 확인 : ";
    var_dump($SN); // 여기서 $SN 값 확인
    
    $sql = "SELECT S.SALE_ID, S.WARRANTY, D.SN
            FROM SALES S
            INNER JOIN DEVICE D ON S.ORDER_NO = D.ORDER_NO
            WHERE D.SN = ?"; 
    
    $stmt = mysqli_prepare($dbconnect, $sql); // SQL 문 준비
    mysqli_stmt_bind_param($stmt, 's', $SN); // Placeholder에 변수 바인딩
    mysqli_stmt_execute($stmt); // SQL 문 실행
    $result = mysqli_stmt_get_result($stmt); // 결과 가져오기

    //디버깅
    if ($result) {
        var_dump(mysqli_num_rows($result)); // 여기서 결과 행 수 확인
    } else {
        echo mysqli_stmt_error($stmt); // 에러 메시지 출력
    }

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $html_values['SALE_ID'] = $row['SALE_ID'];
        $html_values['WARRANTY'] = $row['WARRANTY'];
        $html_values['SN'] = $row['SN']; // SN을 html_values 배열에 추가
    }else{
        $html_values['SALE_ID'] = ''; 
        $html_values['WARRANTY'] = ''; 
        $html_values['SN'] = ''; 
    }
    // TYPE의 기본값 설정
    $html_values['TYPE'] = '무상';
    //디버깅
    echo "===debugging : html_values : ====";
    var_dump($html_values);
}
// 3) 오류 처리
else {
}
if($dbconnect) {
    //mysqli_close 는 스크립트의 제일 마지막에만 와야한다. 그 외에는 $stmt->close()를 적는다.
    mysqli_close($dbconnect);
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
                    <div class="error"><?php echo $errors['saleId']; ?></div>
                <?php endif; ?>
                <?php if (isset($errors['SN'])): ?>
                    <div class="error"><?php echo $errors['SN']; ?></div>
                <?php endif; ?>
            </div>
              <form id="lcsInsertForm" method="post" action="licenseInsert.php">
                  <table class="inputTbl">
                    <tr>
                        <td><label for="saleId">명세서번호<span style="color: red;">*</span></label></td>
                        <td>
                            <input type="text" class="input" name="saleId" id="saleId" value="<?php echo $html_values['SALE_ID'] ?? ''; ?>" readonly>
                            <span class="error-message" id="error-saleId"></span>
                        </td>
                    </tr>
                    <tr> 
                        <td><label for="SN">시리얼번호<span style="color: red;">*</span></label></td>
                        <td>
                            <input type="text" class="input" name="SN" id="SN" value="<?php echo $html_values[' SN'] ?? ''; ?>" readonly>
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
                        <input type="date" class="input short" name="dDate" id="dDate"  value="<?php echo $html_values['D_DATE'] ?? ''; ?>" readonly>
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
    <script src="/.__/auto_complete.js"></script>
    <script>
        document.getElementById('saleId').addEventListener('click', function() {
            this.removeAttribute('readonly');
        });
    </script>
</body>
</html>