<?php
// licenseSearch.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";

mysqli_set_charset($dbconnect, "utf8");

$conditions = array();
$params = array();
$types = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $currentDate = new DateTime();
  $currentFormattedDate = $currentDate->format('Y-m-d');
  $saleId = $_POST["saleId"];
  $SN = $_POST["SN"];
  $type = $_POST["type"];
  $sDateFrom = $_POST["sDateFrom"];
  $sDateTo = $_POST["sDateTo"];
  $dDateFrom = $_POST["dDateFrom"];
  $dDateTo = $_POST["dDateTo"];
  $ref = $_POST["ref"];
  $inspection = $_POST["inspection"];
  $support = $_POST["support"];
  // $vendorName = $_POST["vendorName"]; // 이렇게 하면 오류가 남. 
   // VENDOR_NAME에 대한 처리 추가
   if (!empty($_REQUEST['vendorName'])) {
    $vendorName = mysqli_real_escape_string($dbconnect, $_REQUEST['vendorName']);
    $conditions[] = "V.NAME LIKE '%" . $vendorName . "%'";
}


$sql = "SELECT LICENSE.*, VENDOR.NAME AS VENDOR_NAME FROM LICENSE 
INNER JOIN SALES ON LICENSE.SALE_ID = SALES.SALE_ID 
INNER JOIN VENDOR ON SALES.V_ID = VENDOR.V_ID 
WHERE 1=1";

  if (!empty($saleId)) {
      $conditions[] = "LICENSE.SALE_ID LIKE ?";
      $params[] = "%" . $saleId . "%";
      $types .= 's';
  }
  if (!empty($SN)) {
      $conditions[] = "LICENSE.SN LIKE ?";
      $params[] = "%" . $SN . "%";
      $types .= 's';
  }
  if (!empty($type)) {
      $conditions[] = "LICENSE.TYPE LIKE ?";
      $params[] = "%" . $type . "%";
      $types .= 's';
  }
  if (!empty($sDateFrom) && !empty($sDateTo)) {
      $conditions[] = "LICENSE.S_DATE BETWEEN ? AND ?";
      $params[] = $sDateFrom;
      $params[] = $sDateTo;
      $types .= 'ss';
  } elseif (!empty($sDateFrom)) {
      $conditions[] = "LICENSE.S_DATE >= ?";
      $params[] = $sDateFrom;
      $types .= 's';
  } elseif (!empty($sDateTo)) {
      $conditions[] = "LICENSE.S_DATE <= ?";
      $params[] = $sDateTo;
      $types .= 's';
  }

  if (!empty($dDateFrom) && !empty($dDateTo)) {
      $conditions[] = "LICENSE.D_DATE BETWEEN ? AND ?";
      $params[] = $dDateFrom;
      $params[] = $dDateTo;
      $types .= 'ss';
  } elseif (!empty($dDateFrom)) {
      $conditions[] = "LICENSE.D_DATE >= ?";
      $params[] = $dDateFrom;
      $types .= 's';
  } elseif (!empty($dDateTo)) {
      $conditions[] = "LICENSE.D_DATE <= ?";
      $params[] = $dDateTo;
      $types .= 's';
  }

  if (!empty($inspection)) {
      $conditions[] = "LICENSE.INSPECTION LIKE ?";
      $params[] = "%" . $inspection . "%";
      $types .= 's';
  }
  if (!empty($support)) {
      $conditions[] = "LICENSE.SUPPORT LIKE ?";
      $params[] = "%" . $support . "%";
      $types .= 's';
  }

  if (!empty($ref)) {
      $conditions[] = "LICENSE.REF LIKE ?";
      $params[] = "%" . $ref . "%";
      $types .= 's';
  }

  if (!empty($vendorName)) { // 추가된 부분: 거래처 이름
      $conditions[] = "VENDOR.NAME LIKE ?"; // where 절에서는 DB 테이블의 실제 컬럼 이름 사용. 별칭 사용 X
      $params[] = "%" . $vendorName . "%";
      $types .= 's';
  }

  // 조건이 있을 때만 WHERE 절 추가
  if (!empty($conditions)) {
      $sql .= " AND " . implode(" AND ", $conditions);
  }

  // 쿼리, params, types 출력
  echo "Sale ID: " . $_POST['saleId'] . "<br>";
  echo "SN: " . $_POST['SN'] . "<br>";
  echo "Vendor Name: " . $_POST['vendorName'] . "<br>"; // 추가된 부분: 거래처 이름
  echo "SQL Query: " . $sql . "<br>";
  echo "Params: ";
  print_r($params);
  echo "<br>";
  echo "Types: " . $types . "<br>";

  $stmt = $dbconnect->prepare($sql);

  // SQL 에러 메시지 출력
  if (!$stmt) {
      die("SQL Statement Failed: " . $dbconnect->error);
  }

  $stmt->bind_param($types, ...$params);

  if (!$stmt->execute()) {
      $message = "검색 중 에러가 발생했습니다.";
  } else {
      $result = $stmt->get_result();
      $searchResults = array();
      while ($row = $result->fetch_assoc()) {
          $searchResults[] = $row;
      }
      // 검색 결과가 없는 경우의 리다이렉션을 제거
      if (empty($searchResults)) {
          $message = "일치하는 데이터가 없습니다.";
      } else {
          // 검색 결과가 있는 경우에만 리다이렉션
          $queryParameters = http_build_query($_POST);
          header("Location: licenseMain.php?" . $queryParameters);
          exit();
      }
  }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>라이센스 검색</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="salesMain.css">
  <!-- <script src="https://unpkg.com/htmx.org@1.9.4"></script> -->
</head>

<body>
  <?php include 'navbar.php'; ?>
  <div class="main">
    <div class="header-container">
      <header>라이센스 검색</header>
    </div>
    <div class="content">
      <div class="inputBox mx-auto shadow p-5 mt-4">
        <div class="btn-cancel position-relative top-0">
          <button type="button" class="btn-close" aria-label="Close" onclick="redirectToLicenseMain()"></button>
        </div>
        <form id="lcsSearchForm" method="post" action="licenseMain.php">
          <table class="inputTbl">
            <tr>
              <td><label for="saleId">명세서번호</label></td>
              <td>
                <input type="text" min="0" class="input" name="saleId" id="saleId">
                <span class="error-message">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <td><label for="SN">시리얼번호</label></td>
              <td>
                <input type="text" class="input" name="SN" id="SN">
                <span class="error-message">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <!-- VENDOR 테이블의 NAME 컬럼인  '납품처'   -->
              <td><label for="vendorName">납품처</label></td>
              <td>
                <input type="text" class="input" name="vendorName" id="vendorName">
                <span class="error-message">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <td><label for="type">유지보수 유형</label></td>
              <td>
                <select class="input short selectstyle" name="type" id="type">
                  <!-- 기본값으로 '유상'이 선택되어 있어서 type 파라미터가 항상 $_GET에 포함되어 있기 때문. 
                        사용자가 선택하지 않았을 때 이 값을 전송하지 않으려면 "유상", "무상", "건당" 중 
                        하나를 선택하도록 하는 것 외에, 선택하지 않은 경우를 나타내는 추가적인 <option>을 만드는 것이 좋다. -->
                  <option value="" <?php if (!isset($html_values['TYPE']) || empty($html_values['TYPE'])) echo 'selected'; ?>>선택안함</option>
                  <option value="유상" <?php if (isset($html_values['TYPE']) && $html_values['TYPE'] == '유상') echo 'selected'; ?>>유상</option>
                  <option value="무상" <?php if (isset($html_values['TYPE']) && $html_values['TYPE'] == '무상') echo 'selected'; ?>>무상</option>
                </select>
                <span class="error-message">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <td><label for="sDate">유지보수 시작일</label></td>
              <td>
                <input type="date" class="input short" name="sDateFrom" id="sDateFrom">
                <span>~</span>
                <input type="date" class="input short" name="sDateTo" id="sDateTo">
                <span class="error-message">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <td><label for="dDate">유지보수 종료일</label></td>
              <td>
                <input type="date" class="input short" name="dDateFrom" id="dDateFrom">
                <span>~</span>
                <input type="date" class="input short" name="dDateTo" id="dDateTo">
                <span class="error-message">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <td><label for="inspection">점검</label></td>
              <td>
                <select class="input short selectstyle" name="inspection" id="inspection">
                  <!-- 선택하지 않은 경우를 나타내는 추가적인 <option>을 만드는 것이 좋다. -->
                  <!--건별, 월 방문, 월 원격, 분기 방문, 분기 원격, 반기 방문, 반기 원격, 비고참조-->
                  <option value="" <?php if (!isset($html_values['INSPECTION']) || empty($html_values['INSPECTION'])) echo 'selected'; ?>>선택안함</option>
                  <option value="건별" <?php if (isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '건별') echo 'selected'; ?>>건별</option>
                  <option value="월방문" <?php if (isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '월방문') echo 'selected'; ?>>월방문</option>
                  <option value="월원격" <?php if (isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '월원격') echo 'selected'; ?>>월원격</option>
                  <option value="분기방문" <?php if (isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '분기방문') echo 'selected'; ?>>분기방문</option>
                  <option value="분기원격" <?php if (isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '분기원격') echo 'selected'; ?>>분기원격</option>
                  <option value="반기방문" <?php if (isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '반기방문') echo 'selected'; ?>>반기방문</option>
                  <option value="반기원격" <?php if (isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '반기원격') echo 'selected'; ?>>반기원격</option>
                  <option value="비고참조" <?php if (isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '비고참조') echo 'selected'; ?>>비고참조</option>
                </select>
                <span class="error-message">&nbsp;</span>
              </td>
            </tr>
            <tr>
              <td><label for="support">파트너지원</label></td>
              <td>
                <select class="input short selectstyle" name="support" id="support">
                  <!-- T0: 지원없음, T1:정기점검, T2:유지보수-->
                  <!-- 2024.03.20 변경 : T5: 지원없음, T3: 정기점검, T1:유지 보수-->
                  <option value="" <?php if (!isset($html_values['SUPPORT']) || empty($html_values['SUPPORT'])) echo 'selected'; ?>>선택안함</option>
                  <option value="T5:지원없음" <?php if (isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T5:지원없음') echo 'selected'; ?>>T5:지원없음</option>
                  <option value="T3:정기점검" <?php if (isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T3:정기점검') echo 'selected'; ?>>T3:정기점검</option>
                  <option value="T1:유지보수" <?php if (isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T1:유지보수') echo 'selected'; ?>>T1:유지보수</option>
                </select>
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
          <div class="btn-class">
            <button type="submit" class="btn btn-primary search wide-btn">검색</button>
          </div>
        </form>
      </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
</body>

</html>