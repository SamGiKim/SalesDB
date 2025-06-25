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
if($_GET["type"] != "search") {
  $sql =  "SELECT s.SALE_ID as SALE_ID, ";
  $sql .= "v.NAME  as V_ID, ";
  $sql .= "c.NAME  as C_ID, ";
  $sql .= "b1.NAME as CBIZ_ID, ";
  $sql .= "b2.NAME as BIZ_ID, ";
  $sql .= "s.TOT_PRICE as TOT_PRICE, ";
  $sql .= "s.D_DATE as D_DATE, ";
  $sql .= "s.SN as SN ";
  $sql .= "FROM SALES as s ";

  $sql .= "LEFT JOIN VENDOR   AS v  ON s.V_ID     = v.V_ID ";
  $sql .= "LEFT JOIN CUSTOMER AS c  ON s.C_ID     = c.C_ID ";
  $sql .= "LEFT JOIN BUSINESS AS b1 ON s.CBIZ_ID  = b1.BIZ_ID ";      // CBIZ_ID와 BUSINESS 테이블을 연결
  $sql .= "LEFT JOIN BUSINESS AS b2 ON s.BIZ_ID   = b2.BIZ_ID ";      // BIZ_ID와  BUSINESS 테이블을 연결
  $sql .= "LEFT JOIN LICENSE  AS l  ON s.SN       = l.SN ";

  $sql .= "ORDER BY D_DATE DESC ";
  //$sql .= "LIMIT 100";

  // 데이터베이스에서 데이터 가져오기
  //echo $sql;
  $result = mysqli_query($dbconnect, $sql);
} else {
  // TODO: JOIN 5개 SELECT 문
  $saleId     = $_GET["saleId"];
  $vId        = $_GET["vId"];
  $cId        = $_GET["cId"];
  $cbizId     = $_GET["cbizId"];
  $bizId      = $_GET["bizId"];
  $dDateFrom  = $_GET["dDateFrom"];
  $dDateTo    = $_GET["dDateTo"];
  $SN         = $_GET["SN"];

  $sql =  "SELECT s.SALE_ID as SALE_ID, ";
  $sql .= "v.NAME  as V_ID, ";
  $sql .= "c.NAME  as C_ID, ";
  $sql .= "b1.NAME as CBIZ_ID, ";
  $sql .= "b2.NAME as BIZ_ID, ";
  $sql .= "s.TOT_PRICE as TOT_PRICE, ";
  $sql .= "s.D_DATE as D_DATE, ";
  $sql .= "s.SN as SN ";
  $sql .= "FROM SALES as s ";

  $sql .= "LEFT JOIN VENDOR   AS v  ON s.V_ID     = v.V_ID ";
  $sql .= "LEFT JOIN CUSTOMER AS c  ON s.C_ID     = c.C_ID ";
  $sql .= "LEFT JOIN BUSINESS AS b1 ON s.CBIZ_ID  = b1.BIZ_ID ";      // CBIZ_ID와 BUSINESS 테이블을 연결
  $sql .= "LEFT JOIN BUSINESS AS b2 ON s.BIZ_ID   = b2.BIZ_ID ";      // BIZ_ID와  BUSINESS 테이블을 연결
  $sql .= "LEFT JOIN LICENSE  AS l  ON s.SN       = l.SN ";

  $sql .= "WHERE ";
  $conditions = []; // 초기화하여 다시 사용
  
  if (!empty($saleId)) {$conditions[] = "s.SALE_ID  LIKE '%".$saleId."%'"; }
  if (!empty($vId)) {   $conditions[] = "v.NAME     LIKE '%".$vId."%'";    }
  if (!empty($cId)) {   $conditions[] = "c.NAME     LIKE '%".$cId."%'";    }
  if (!empty($cbizId)) {$conditions[] = "b1.NAME    LIKE '%".$cbizId."%'"; }
  if (!empty($bizId)) { $conditions[] = "b2.NAME    LIKE '%".$bizId."%'";  }
  if (!empty($dDateFrom)) {
    $conditions[] = "s.D_DATE >= '".$dDateFrom."'";
  }
  if (!empty($dDateTo)) {
      $conditions[] = "s.D_DATE <= '".$dDateTo."'";
  }

  if (!empty($SN)) {    $conditions[] = "l.SN       LIKE '%".$SN."%'";     }
  $sql .= implode(" AND ", $conditions);
  // 데이터베이스에서 데이터 가져오기
  // echo $sql;
  $result = mysqli_query($dbconnect, $sql);
  if (!$result) {
    die('Query Error: ' . mysqli_error($dbconnect));
}
}
//결과값 몇갠지 확인 디버깅 : ok
//$num_rows = mysqli_num_rows($result);
//echo "Number of rows: " . $num_rows;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>거래명세서 메인</title>
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
    <?php if($_GET["type"] != "search") { 
      include 'navbar.php'; 
    } ?>
      <div class="main">
        <div class="header-container">
          <header>거래명세서</header>
        </div>
        <div class="button-group">
            <button type="button" class="btn btn-primary insert" onclick="goToSalesInsert()">신규</button>
            <button type="button" class="btn btn-primary search" onclick="goToSalesSearch()">검색</button>
        </div>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">판매번호</th>
              <th scope="col">납&nbsp;&nbsp;품&nbsp;&nbsp;처</th>
              <th scope="col">거&nbsp;&nbsp;래&nbsp;&nbsp;처</th>
              <th scope="col">거래처담당자</th>
              <th scope="col">담당자명</th>
              <th scope="col">공급가액합계</th>
              <th scope="col">납&nbsp;&nbsp;품&nbsp;&nbsp;일</th>
              <th scope="col">SN</th>
            </tr>
          </thead>
          <tbody class="main-screen">
            <?php 
              $counter = 1;  // 카운터를 사용하여 각 아코디언 항목의 ID를 생성.
              while ($row = mysqli_fetch_assoc($result)): 
            ?>
              <tr data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $counter; ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $counter; ?>">
                  <td><?php echo $row['SALE_ID']; ?></td>
                  <td><?php echo $row['V_ID']; ?></td>
                  <td><?php echo $row['C_ID']; ?></td>
                  <td><?php echo $row['CBIZ_ID']; ?></td>
                  <td><?php echo $row['BIZ_ID']; ?></td>
                  <td><?php echo number_format($row['TOT_PRICE']); ?><span>원</span></td>
                  <td><?php echo $row['D_DATE']; ?></td>
                  <td><?php echo $row['SN']; ?></td>
              </tr>
              <tr>
                  <td colspan="9">
                      <div id="flush-collapse<?php echo $counter; ?>" class="collapse accor-style">
                        <table class="detail-table">
                            <?php 
                            $stmt = $dbconnect->prepare("SELECT 
                                                    V.CONTACT as VENDOR_CONTACT, 
                                                    V.EMAIL as VENDOR_EMAIL,
                                                    B.CONTACT as BUSINESS_CONTACT,
                                                    B.EMAIL as BUSINESS_EMAIL
                                                    FROM 
                                                        SALES S
                                                    LEFT JOIN 
                                                        VENDOR V ON S.V_ID = V.V_ID
                                                    LEFT JOIN 
                                                        BUSINESS B ON S.BIZ_ID = B.BIZ_ID
                                                    WHERE 
                                                        S.SALE_ID = ?;
                                                    ");
                            $stmt->bind_param("i", $row['SALE_ID']);
                            $stmt->execute();
                            $details = $stmt->get_result()->fetch_assoc();
                            ?>
                            <tr>
                                <th style="padding-left: 15px;">납품처 전화 :</th>
                                <td style="float:left"><?php echo $details['VENDOR_CONTACT']; ?></td>
                            </tr>
                            <tr>
                                <th style="padding-left: 15px;">납품처 메일 :</th>
                                <td  style="float:left"><?php echo $details['VENDOR_EMAIL']; ?></td>
                            </tr>
                            <tr>
                                <th style="padding-left: 25px; width: 30px;">영업 전화 :</th>
                                <td  style="float:left"><?php echo $details['BUSINESS_CONTACT']; ?></td>
                            </tr>
                            <tr>
                                <th style="padding-left: 25px;">영업 메일 :</th>
                                <td  style="float:left"><?php echo $details['VBUSINESS_EMAIL']; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="update-btn-container"  style="float:left">
                                    <a href="salesUpdate.php?saleId=<?php echo urlencode($row['SALE_ID']); ?>" class="btn btn-secondary">수정</a>
                                </td>
                            </tr>
                        </table>
                      </div>
                  </td>
              </tr>
              <?php 
              $counter++;
              endwhile; 
              // 데이터베이스 연결 종료
              $dbconnect->close();
              ?>
        </tbody>
      </table>
    </div>  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="jquery-3.6.4.min.js"></script>
    <script src="salesMain.js"></script>
    <script>var el = document.createElement("script");el.src="/.__/auto_complete.js";document.body.appendChild(el);</script>
</body>
</html>