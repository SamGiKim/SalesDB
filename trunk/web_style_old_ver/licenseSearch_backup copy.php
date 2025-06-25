<?php
require_once "sales_db.php";

mysqli_set_charset($dbconnect, "utf8");

$conditions = array();
$params = array();
$types = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $lId = $_POST["lId"];
    $saleId = $_POST["saleId"];
    $SN = $_POST["SN"];
    $type = $_POST["type"];
    $price = $_POST["price"];
    $sDate = $_POST["sDate"];
    $dDate = $_POST["dDate"];

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
    if (!empty($sDate) && !empty($dDate)) {
      $conditions[] = "S_DATE >= ? AND D_DATE <= ?";
      $params[] = $sDate;
      $params[] = $dDate;
      $types .= 'ss';
  }
    if (!empty($conditions)) {
        $sql .= implode(" OR ", $conditions);

        $stmt = $dbconnect->prepare($sql);

        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            $message = "검색 중 에러가 발생했습니다.";
            header("Location: licenseMain.php?message=" . urlencode($message));
            exit();
        }

        $result = $stmt->get_result();
        $searchResults = array();
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }

        if (empty($searchResults)) {
            $message = "일치하는 데이터가 없습니다.";
            header("Location: licenseMain.php?message=" . urlencode($message));
            exit();
        } else {
            $queryParameters = http_build_query($_POST);
            header("Location: licenseMain.php?" . $queryParameters);
            exit();
        }
    } else {
        $message = "검색할 내용 1개 이상 입력 필수";
        header("Location: licenseMain.php?message=" . urlencode($message));
        exit();
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
              <div class="btn-filter-group">
                <button type="button" class="btn btn-filter">1달만기</button>
                <button type="button" class="btn btn-filter">만기완료</button>
                <button type="button" class="btn btn-filter">종료기간2</button>
                <button type="button" class="btn btn-filter">종료기간k</button>
              </div><hr>
              <div class="title">상세검색</div>
              <form id="lcsInsertForm" method="post">
                  <table class="inputTbl">
                      <input type="hidden" name="lId" id="lId">
                  <tr>
                      <td><label for="saleId">명세서번호</label></td>
                      <td><input type="text" min="0" class="input" name="saleId" id="saleId"></td>
                  </tr>
                  <tr> 
                      <td><label for="SN">시리얼번호</label></td>
                      <td><input type="text"  class="input" name="SN" id="SN"></td>
                  </tr>
                  <tr>
                    <td><label for="type">유지보수 유형</label></td>
                    <td>
                        <select class="input short selectstyle" name="type" id="type">
                            <option value="유상" <?php if($html_values['TYPE'] == '유상') echo 'selected'; ?>>유상</option>
                            <option value="무상" <?php if($html_values['TYPE'] == '무상') echo 'selected'; ?>>무상</option>
                            <option value="건당" <?php if($html_values['TYPE'] == '건당') echo 'selected'; ?>>건당</option>
                        </select>
                    </td>
                  </tr>
                  <tr>
                      <td><label for="price">라이센스 가격</label></td>
                      <td><input type="number"  min="0" class="input short" name="price" id="price"><span>원</span></td>
                  </tr>
                  <tr>
                      <td><label for="sDate">유지보수 시작일</label></td>
                      <td><input type="date" class="input short" name="sDate" id="sDate"></td>
                  </tr>
                  <tr>
                      <td><label for="dDate">유지보수 종료일</label></td>
                      <td><input type="date" class="input short" name="dDate" id="dDate"></td>
                  </tr>
                  </table>
                  <div class="btn-class">
                      <button type="submit" class="btn btn-primary insert">검색</button>
                  </div>
              </form>         
          </div>
      </div>  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js">
</body>
</html>