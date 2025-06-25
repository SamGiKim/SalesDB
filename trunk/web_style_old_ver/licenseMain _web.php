<?php
// Include the database connection file
require_once "sales_db.php";

// UTF-8 인코딩 설정
mysqli_set_charset($dbconnect, "utf8");

// 검색 조건 받아오기
$searchCondition = $_GET['searchCondition'];

// 검색 조건에 따른 SQL 쿼리 작성
// 기본적으로는 전체 목록을 보여주지만, 검색 조건이 salesSearch로 부터 전달될 경우 
// 해당 조건에 맞는 데이터만 필터링하여 보여줌.
$query = "SELECT L.L_ID, L.SALE_ID, L.SN, L.C_ID, B.NAME, L.TYPE, L.PRICE, L.S_DATE, L.D_DATE
          FROM LICENSE AS L
          LEFT JOIN BUSINESS AS B ON L.C_ID = B.C_ID";
if (!empty($searchCondition)) {
    $query .= " WHERE " . $searchCondition;
}
$query .= " ORDER BY L.D_DATE DESC";


// 데이터베이스에서 데이터 가져오기
$result = mysqli_query($dbconnect, $query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>라이센스 메인</title>
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
          <header>라이센스</header>
        </div>
        <div class="button-group">
            <button type="button" class="btn btn-primary insert" onclick="goToLicenseInsert()">신규</button>
            <button type="button" class="btn btn-primary search" onclick="goToLicenseSearch()">검색</button>
          </div>
        <!--각 데이터의 행이 나오면, 행의 어느 구간이든 클릭하면 수정 페이지(salesUpdate.php)로 가도록-->
        <table class="table">
          <thead>
            <tr>
              <th scope="col">계약번호</th>
              <th scope="col">명세서번호</th>
              <th scope="col">시리얼번호</th>
              <th scope="col">거래처</th>
              <th scope="col">거래처담당자</th>
              <th scope="col">유지보수 유형</th>
              <th scope="col">라이센스 가격</th>
              <th scope="col">유지보수 시작일</th>
              <th scope="col">유지보수 종료일</th>
            </tr>
          </thead>
          <tbody class="main-screen">
            <?php 
              $counter = 1;  // 카운터를 사용하여 각 아코디언 항목의 ID를 생성.
              while ($row = mysqli_fetch_assoc($result)): 
              ?>
              <tr data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $counter; ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $counter; ?>">
                  <td><?php echo $row['L_ID']; ?></td>
                  <td><?php echo $row['SALE_ID']; ?></td>
                  <td><?php echo $row['SN']; ?></td>
                  <td><?php echo $row['C_ID']; ?></td>
                  <td><?php echo $row['NAME']; ?></td>
                  <td><?php echo $row['TYPE']; ?></td>
                  <td><?php echo number_format($row['PRICE']); ?><span>원</span></td>
                  <td><?php echo $row['S_DATE']; ?></td>
                  <td><?php echo $row['D_DATE']; ?></td>
              </tr>
              <tr>
                  <td colspan="9">
                      <div id="flush-collapse<?php echo $counter; ?>" class="collapse accor-style">
                        <table class="detail-table">
                          <tr>
                              <th style="float:left; padding-left: 15px; ">장비 모델명: (미정)</th>
                              <td style="float:left"><?php echo $details2['MODEL']; ?></td>
                          </tr>
                          <tr>
                              <th style="float:left; padding-left: 30px;">장비유형:  (미정)</th>
                              <td style="float:left"><?php echo $details2['DEV_TYPE']; ?></td>
                          </tr>
                        </table>
                      </div>
                  </td>
              </tr>
              <?php 
              $counter++;
              endwhile; 
              ?>
          </tbody>
      </table>
    </div>  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js">
</body>
</html>