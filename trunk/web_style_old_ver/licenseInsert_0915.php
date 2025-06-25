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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $saleId = $_POST["saleId"];
    $SN = $_POST["SN"] !== "" ? $_POST["SN"] : null; 
    $type = $_POST["type"];
    $price = $_POST["price"] !== "" ? $_POST["price"] : null;
    $sDate = $_POST["sDate"] !== "" ? $_POST["sDate"] : null;
    $dDate = $_POST["dDate"] !== "" ? $_POST["dDate"] : null;
    $ref = $_POST["ref"] !== "" ? $_POST["ref"] : null;

        //지원팀 요구사항: 1) 신규입력시 SALE_ID, SN 100% 있어야한다. 단, 무상일때는 SN이 없을 수 있다. 
    //라이센스 신규 입력에서 필수 조건 2개(명세서번호가 SALES 테이블에 있는가? AND SN번호가 DEVICES 테이블에 있는가?) 입니다. 이를 구현하기 위해서 DEVICES 테이블(SN 컬럼 1개)
    // 유효성 검사
    // 판매번호 (Sales Number) and SN

    // var_dump($saleId, $SN);
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
    $stmt->close();


    // SN DEVICE 테이블에 있는지 확인
    $query = "SELECT 1 FROM DEVICE WHERE SN = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $SN);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows===0){
        $errors['SN'] = "존재하지 않는 장비 SN입니다. 장비 확인 후 등록하시기 바랍니다.";
    }
    $stmt->close();

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: licenseInsert.php");
        exit;
    }

    $query = "SELECT WARRANTY FROM SALES WHERE SALE_ID = ?";  // 적절한 쿼리를 사용해주세요
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $html_values['WARRANTY'] = $row['WARRANTY'];
    }
    $stmt->close();
    
    $warranty_years = 0;
    $warranty_months = 0;
    $warranty = isset($html_values['WARRANTY']) ? $html_values['WARRANTY'] : 0;
    $warranty_years = floor($warranty / 12);
    $warranty_months = $warranty % 12;


    //insert
    $stmt = $dbconnect->prepare("INSERT INTO LICENSE (SALE_ID, SN, `TYPE`, PRICE, S_DATE, D_DATE, REF)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssisss",
        $saleId, $SN, $type, $price, $sDate, $dDate, $ref
        );
    // Execute the prepared statement
    if($stmt->execute()){
        echo "<script>
        alert('데이터가 성공적으로 입력되었습니다.');
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
                    <!-- L_ID는 AUTO-INCREMENT 되는 PK로 따로 INSERT해주지 않아도 데이터가 값을 할당하기에 삭제함 -->
                    <tr>
                        <td><label for="saleId">명세서번호<span style="color: red;">*</span></label></td>
                        <td>
                            <input type="text" class="input" name="saleId" id="saleId" value="<?php echo $html_values['SALE_ID'] ?? ''; ?>">
                            <span class="error-message" id="error-saleId"></span>
                        </td>
                    </tr>
                    <tr> 
                        <td><label for="SN">시리얼번호</label></td>
                        <td>
                            <input type="text" class="input" name="SN" id="SN" value="<?php echo $html_values['SN'] ?? ''; ?>">
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
                        <td><label for="type">보증기간</label></td>
                        <td>
                            <select class="input short selectstyle" name="warranty_years" id="warranty_years" disabled>
                                <?php
                                for ($i = 0; $i <= 5; $i++) {
                                    $selected = $i == $warranty_years ? "selected" : "";
                                    echo "<option value=\"$i\" $selected>{$i} 년</option>";
                                }
                                ?>
                            </select>

                            <select class="input short selectstyle" name="warranty_months" id="warranty_months" disabled>
                                <?php
                                for ($i = 0; $i <= 11; $i++) {
                                    $selected = $i == $warranty_months ? "selected" : "";
                                    echo "<option value=\"$i\" $selected>{$i} 개월</option>";
                                }
                                ?>
                            </select>
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
                    <?php
                    ?>
                    </div>
                  <div class="btn-class">
                      <button type="submit" class="btn btn-primary wide-btn insert">등록</button>
                  </div>
              </form>         
          </div>
      </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
</body>
</html>