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

// Check saleId 기능 추가
if (isset($_GET['action']) && $_GET['action'] == 'check_sale_id' && isset($_POST['saleId'])) {
    $saleId = $_POST['saleId'];

    $stmt = $dbconnect->prepare("SELECT * FROM SALES WHERE SALE_ID = ?");
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<input type='text' min='0' class='input' name='saleId' id='saleId' hx-post='licenseInsert.php?action=check_sale_id' hx-trigger='blur' hx-swap='outerHTML' hx-indicator='#loadingIndicator' onfocus='alertNotExist()'>";
    } else {
        echo "<input type='text' min='0' class='input' name='saleId' id='saleId' hx-post='licenseInsert.php?action=check_sale_id' hx-trigger='blur' hx-swap='outerHTML' hx-indicator='#loadingIndicator'>";
    }
    $stmt->close();
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $saleId = $_POST["saleId"];
    $SN = $_POST["SN"] !== "" ? $_POST["SN"] : null;
    $type = $_POST["type"];
    $price = $_POST["price"] !== "" ? $_POST["price"] : null;
    $sDate = $_POST["sDate"] !== "" ? $_POST["sDate"] : null;
    $dDate = $_POST["dDate"] !== "" ? $_POST["dDate"] : null;

    //지원팀 요구사항: 1) 신규입력시 SALE_ID, SN 100% 있어야한다. 단, 무상일때는 SN이 없을 수 있다. 
    // 유효성 검사
    $errors = array();

    // Add validation for 판매번호 (Sales Number) and SN
    if(empty($saleId)) {
        $errors[] = "명세서번호(SALE_ID)를 입력해주세요.";
    }

    if($type !== "무상" && empty($SN)) {
        $errors[] = "유상일 경우 시리얼번호(SN)를 입력해주세요.";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: licenseInsert.php");
        exit;
    }

    //insert
    $stmt = $dbconnect->prepare("INSERT INTO LICENSE (SALE_ID, SN, `TYPE`, PRICE, S_DATE, D_DATE)
                                VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssiss",
        $saleId, $SN, $type, $price, $sDate, $dDate
        );
    // Execute the prepared statement
    if($stmt->execute()){
        echo "<script>
        alert('데이터가 잘 입력되었습니다.');
        location.href = 'licenseMain.php';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
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
  <?php 
    if (isset($_SESSION['errors'])) {
        echo implode("<br>", $_SESSION['errors']);
        // 출력 후 에러 메시지 세션을 제거합니다.
        unset($_SESSION['errors']);
    }
   ?>
      <div class="main">
        <div class="header-container">
          <header>라이센스 신규입력</header>
        </div>
        <div class="content">
          <div class="inputBox mx-auto shadow p-5 mt-4">
              <div class="btn-cancel position-relative top-0">
                  <button type="button" class="btn-close" aria-label="Close" onclick="redirectToLicenseMain()"></button>
              </div>
              <form id="lcsInsertForm" method="post" action="licenseInsert.php">
                  <table class="inputTbl">
                    <!-- L_ID는 AUTO-INCREMENT 되는 PK로 따로 INSERT해주지 않아도 데이터가 값을 할당하기에 삭제함 -->
                  <tr>
                      <td><label for="saleId">명세서번호</label></td>
                      <!-- htmx를 사용하여 SALE_ID 확인 -->
                      <td><input type="text" min="0" class="input" name="saleId" id="saleId" hx-post="licenseInsert.php?action=check_sale_id" hx-trigger="blur" hx-swap="outerHTML" hx-indicator="#loadingIndicator"></td>
                    </tr>
                    <tr> 
                      <td><label for="SN">시리얼번호</label></td>
                      <td><input type="text" class="input" name="SN" id="SN" value="<?php echo $html_values['SN'] ?? ''; ?>"></td>
                  </tr>
                  <tr>
                    <td><label for="type">유지보수 유형</label></td>
                    <td>
                        <select class="input short selectstyle" name="type" id="type">
                            <option value="유상" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '유상' ? 'selected' : ''; ?>>유상</option>
                            <option value="무상" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '무상' ? 'selected' : ''; ?>>무상</option>
                            <option value="건당" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '건당' ? 'selected' : ''; ?>>건당</option>
                        </select>
                    </td>
                  </tr>
                  <tr>
                      <td><label for="price">라이센스 가격</label></td>
                      <td><input type="number"  min="0" class="input short" name="price" id="price" value="<?php echo $html_values['PRICE'] ?? ''; ?>"><span>원</span></td>
                  </tr>
                  <tr>
                      <td><label for="sDate">유지보수 시작일</label></td>
                      <td><input type="date" class="input short" name="sDate" id="sDate" value="<?php echo $html_values['S_DATE'] ?? ''; ?>"></td>
                  </tr>
                  <tr>
                      <td><label for="dDate">유지보수 종료일</label></td>
                      <td><input type="date" class="input short" name="dDate" id="dDate"  value="<?php echo $html_values['D_DATE'] ?? ''; ?>"></td>
                  </table>
                  <div class="btn-class">
                      <button type="submit" class="btn btn-primary insert">등록</button>
                  </div>
              </form>         
          </div>
      </div>  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js">
</body>
</html>