<?php
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

    //버튼 필터
    if (isset($_POST['filters'])) {
      foreach ($_POST['filters'] as $filter) {
          switch ($filter) {
              case 'oneMonthExpiry':
                  $currentFormattedDate = $currentDate->format('Y-m-d'); // 오늘 날짜
                  $oneMonthLater = $currentDate->modify('+30 days')->format('Y-m-d'); // 30일 후 날짜
                  $conditions[] = "D_DATE BETWEEN ? AND ?";
                  $params[] = $currentFormattedDate;
                  $params[] = $oneMonthLater;
                  $types .= 'ss';  // 두 개의 문자열 값이므로 'ss'로 설정
                  break;
              case 'expired':
                  $conditions[] = "D_DATE < ?";
                  $params[] = $currentFormattedDate;
                  $types .= 's';
                  break;
              // 새롭게 추가되는 필터 조건들 여기로
          }
      }
  }
    //상세검색
    $lId = $_POST["lId"];
    $saleId = $_POST["saleId"];
    $SN = $_POST["SN"];
    $type = $_POST["type"];
    $sDateFrom = $_POST["sDateFrom"];
    $sDateTo = $_POST["sDateTo"];
    $dDateFrom = $_POST["dDateFrom"];
    $dDateTo = $_POST["dDateTo"];

    $sql = "SELECT * FROM LICENSE WHERE ";

    if (!empty($saleId)) {
        $conditions[] = "SALE_ID = ?";
        $params[] = $saleId;
        $types .= 's';
    }
    if (!empty($SN)) {
        $conditions[] = "SN = ?";
        $params[] = $SN;
        $types .= 's';
    }
    if (!empty($type)) {
        $conditions[] = "TYPE = ?";
        $params[] = $type;
        $types .= 's';
    }
    if (!empty($sDateFrom) && !empty($sDateTo)) {
      $conditions[] = "S_DATE BETWEEN ? AND ?";
      $params[] = $sDateFrom;
      $params[] = $sDateTo;
      $types .= 'ss';
  } elseif (!empty($sDateFrom)) {
      $conditions[] = "S_DATE >= ?";
      $params[] = $sDateFrom;
      $types .= 's';
  } elseif (!empty($sDateTo)) {
      $conditions[] = "S_DATE <= ?";
      $params[] = $sDateTo;
      $types .= 's';
  }
  
  if (!empty($dDateFrom) && !empty($dDateTo)) {
      $conditions[] = "D_DATE BETWEEN ? AND ?";
      $params[] = $dDateFrom;
      $params[] = $dDateTo;
      $types .= 'ss';
  } elseif (!empty($dDateFrom)) {
      $conditions[] = "D_DATE >= ?";
      $params[] = $dDateFrom;
      $types .= 's';
  } elseif (!empty($dDateTo)) {
      $conditions[] = "D_DATE <= ?";
      $params[] = $dDateTo;
      $types .= 's';
  }
  
  }
    if (!empty($conditions)) {
        $sql .= implode(" AND ", $conditions);

        $stmt = $dbconnect->prepare($sql);

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
              exit();
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
    <script src="https://unpkg.com/htmx.org@1.9.4"></script>
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
              <div class="btn-filter-group">
                <button type="button" class="btn btn-filter" value="oneMonthExpiry" onclick="addFilter('oneMonthExpiry')">한달만기</button>
                <button type="button" class="btn btn-filter" value="expired" onclick="addFilter('expired')">만기완료</button>
                <button type="button" class="btn btn-filter" value="expiryPeriodA" onclick="addFilter('expiryPeriodA')">종료기간A</button>
                <button type="button" class="btn btn-filter" value="expiryPeriodB" onclick="addFilter('expiryPeriodB')">종료기간B</button>
              </div><br><hr>
              <div class="title"><상세검색></div>
                  <table class="inputTbl">
                      <input type="hidden" name="lId" id="lId">
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
                        <input type="text"  class="input" name="SN" id="SN">
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
                          <option value="" <?php if(!isset($html_values['TYPE']) || empty($html_values['TYPE'])) echo 'selected'; ?>>선택안함</option>
                          <option value="유상" <?php if(isset($html_values['TYPE']) && $html_values['TYPE'] == '유상') echo 'selected'; ?>>유상</option>
                          <option value="무상" <?php if(isset($html_values['TYPE']) && $html_values['TYPE'] == '무상') echo 'selected'; ?>>무상</option>
                          <option value="건당" <?php if(isset($html_values['TYPE']) && $html_values['TYPE'] == '건당') echo 'selected'; ?>>건당</option>
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