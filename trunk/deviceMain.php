<?php
require_once "auth.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";
require 'deviceLFSF.php';
mysqli_set_charset($dbconnect, "utf8");

$SN = isset($_GET['SN']) ? $_GET['SN'] : '';
$message = "전체 : ";
// 한 페이지에 보여줄 아이템 수
$itemsPerPage = 50;

// 현재 페이지 번호 (GET 파라미터로 받음, 기본 1)
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// URL 매개변수로부터 검색 조건 가져오기
$searchConditions = [];
if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        if ($key === 'page') continue; // 페이지 번호는 검색 조건에서 제외
        if (!empty(trim($value))) {
            $searchConditions[$key] = $value;
        }
    }
}

$columnMap = [
    "devType" => "DEV_TYPE",
    "SN" => "SN",
];

// WHERE 조건 배열 생성
$conditions = [];
foreach ($searchConditions as $key => $value) {
    if (!isset($columnMap[$key])) {
        continue;
    }
    $column = $columnMap[$key];
    if ($key === "SN") {
        // 정확히 일치하는 SN만 조회
        $conditions[] = "D.$column = '" . mysqli_real_escape_string($dbconnect, $value) . "'";
    } elseif ($key === "FV") {
        $conditions[] = "D.$column LIKE '%" . mysqli_real_escape_string($dbconnect, $value) . "%'";
    } else {
        $conditions[] = "D.$column = '" . mysqli_real_escape_string($dbconnect, $value) . "'";
    }
}


// 총 데이터 건수 구하기 쿼리 (조건 반영)
$countQuery = "SELECT COUNT(*) AS total FROM DEVICE D
               LEFT JOIN SALES S ON D.ORDER_NO = S.ORDER_NO";

if (count($conditions) > 0) {
    $countQuery .= " WHERE " . implode(" AND ", $conditions);
}

$countResult = mysqli_query($dbconnect, $countQuery);
if ($countResult) {
    $countRow = $countResult->fetch_assoc();
    $totalCount = $countRow['total'];
} else {
    $totalCount = 0;
}

// 전체 페이지 수 계산
$totalPages = ceil($totalCount / $itemsPerPage);

// LIMIT, OFFSET 계산
$offset = ($page - 1) * $itemsPerPage;

// 데이터 조회 쿼리
$query = "SELECT D.*, S.SALE_ID FROM DEVICE D
          LEFT JOIN SALES S ON D.ORDER_NO = S.ORDER_NO";

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY D.WDATE DESC
            LIMIT $itemsPerPage OFFSET $offset";

$result = mysqli_query($dbconnect, $query);
if (!$result) {
    die("Query Failed: " . mysqli_error($dbconnect));
}
?>



<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>장비 메인</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="salesMain.css">
    <script src="https://unpkg.com/htmx.org@1.9.4"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous"></script>
    <style>
        .model {margin-right: 50px;} /* 모델명과 FV 사이의 간격 조절 */

    </style>
</head>

<body>
    <?php
    include 'navbar.php';
    ?>
    <div class="container-fluid mt-5 main-screen">
        <div class="row">
            <div class="col-12 text-center">
                <header>장비</header>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-start main-top-btn">
                <button type="button" class="btn btn-primary insert mr-2" onclick="goToDeviceInsert()">신규</button>
                <button type="button" class="btn btn-primary search">검색</button>
            </div>
            <div class="total-number" style="text-align:left; margin-left:2%; font-size: 1.2em; font-weight:bold;">
                <?= $message ?><span> </span><?= $totalCount ?> 건
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-wrapper table-responsive">
                    <table class="table main-tbl">
                        <thead>
                            <tr>
                                <th scope="col" class="col-1">SN</th>
                                <th scope="col" class="col-1">
                                    <span class="model">모델명</span>
                                    <span class="fv">FV</span>
                                </th>
                                <th scope="col" class="col-1">장비유형</th>
                                <th scope="col" class="col-1">I/F&유형</th>
                                <th scope="col" class="col-1">포트수</th>
                                <th scope="col" class="col-1">제조일</th>
                                <th scope="col" class="col-1">용량</th>
                                <th scope="col" class="col-1">디스크</th>
                                <th scope="col" class="col-1">메모리</th>
                                <th scope="col" class="col-1">주문번호</th>
                                <th scope="col" class="col-1">명세서번호</th>
                                <th scope="col" class="col-1">확인</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr data-sn="<?php echo $row['SN']; ?>">
                                    <td class="col-1">
                                        <a class="custom-link" href="salesMain.php?SN=<?php echo $row['SN']; ?>">
                                            <?php echo $row['SN']; ?>
                                        </a>
                                    </td>
                                    <td class="col-1">
                                        <span class="model"><?php echo $row['MODEL']; ?></span>
                                        <span class="fv"><?php echo $row['FV']; ?></span>
                                    </td>
                                    <td class="col-1"><?php echo $row['DEV_TYPE']; ?></td>
                                    <td class="col-1"><?php echo $row['INTERFACE']; ?></td>
                                    <td class="col-1"><?php echo $row['IKIND']; ?><?php echo $row['INTNUM']; ?></td>
                                    <td class="col-1"><?php echo $row['WDATE']; ?></td>
                                    <td class="col-1"><?php echo $row['CAPACITY']; ?></td>
                                    <td class="col-1"><?php echo $row['HDD']; ?></td>
                                    <td class="col-1"><?php echo $row['MEMORY']; ?></td>
                                    <td class="col-1">
                                        <a class="custom-link" href="salesMain.php?ORDER_NO=<?php echo $row['ORDER_NO']; ?>">
                                            <?php echo $row['ORDER_NO']; ?>
                                        </a>
                                    </td>
                                    <td class="col-1">
                                        <a class="custom-link" href="salesMain.php?SALE_ID=<?php echo urlencode($row['SALE_ID']); ?>">
                                            <?php echo $row['SALE_ID']; ?>
                                        </a>
                                    </td>
                                    <td class="col-1">
                                        <?php
                                        $lf = $row['LF'];
                                        $sf = $row['SF'];
                                        if ($lf == 0 && $sf == 1) {
                                            // 경로1: LF:SF 장비메인으로 들어갈때 ; POST로 데이터 보냄 
                                            echo "<form action='licenseInsertViaDevice.php' method='post'>";
                                            echo "<input type='hidden' name='saleId' value='{$row['SALE_ID']}'>";
                                            echo "<input type='hidden' name='SN' value='{$row['SN']}'>";
                                            echo "<button type='submit' class='btn btn-primary' style='font-size:11px;'>라이센스 생성</button>";
                                            echo "</form>";
                                        } else if ($lf == 1 && $sf == 1) {
                                            echo "<button type='submit' class='btn btn-primary' style='font-size:11px; background-color: lightgray; color:black;' disabled>라이센스 등록완료</button>";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="pagination-container text-center mt-3 mb-4">
                        <?php
                            // 현재 GET 파라미터 가져오기
                            $queryParams = $_GET;

                            if ($page > 1) {
                                $prevPage = $page - 1;
                                $queryParams['page'] = $prevPage;
                                $prevUrl = '?' . http_build_query($queryParams);
                                echo "<a href='" . htmlspecialchars($prevUrl) . "' class='btn btn-outline-primary me-2'>이전</a>";
                            }
                            echo " $page / $totalPages ";

                            if ($page < $totalPages) {
                                $nextPage = $page + 1;
                                $queryParams['page'] = $nextPage;
                                $nextUrl = '?' . http_build_query($queryParams);
                                echo "<a href='" . htmlspecialchars($nextUrl) . "' class='btn btn-outline-primary ms-2'>다음</a>";
                            }
                        ?>
                    </div>
                    <?php
                    if (!empty($SN)) { // SN 값이 비어있지 않은 경우에만 전체 블럭을 출력
                    ?>
                        <div class="licenseInfoBox" style="border: 1px solid #ccc; padding: 10px; margin-top: 20px;">
                            <h3>라이센스 정보</h3>
                            <!-- 라이센스 정보 내용 -->
                            <div class="licenseContent">
                                <?php
                                if (!empty($SN)) { // SN 값이 비어있지 않은 경우에만 쿼리 실행
                                    $licenseQuery = "SELECT * FROM LICENSE WHERE SN = '$SN'";
                                    // echo "실행할 쿼리: " . $licenseQuery;
                                    $licenseResult = mysqli_query($dbconnect, $licenseQuery);
                                    // if (!$licenseResult) {
                                    //     echo "쿼리 실행 실패: " . mysqli_error($dbconnect);
                                    // } else {
                                    //     echo "쿼리 실행 성공";
                                    // }

                                    if ($licenseResult && mysqli_num_rows($licenseResult) > 0) {
                                        while ($licenseRow = mysqli_fetch_assoc($licenseResult)) {
                                            echo "<div>";
                                            echo ": 명세서 번호 " . $licenseRow['SALE_ID'];
                                            echo " | SN: " . $licenseRow['SN'];
                                            echo " | 유형: " . $licenseRow['TYPE'];
                                            echo " | 가격: " . $licenseRow['PRICE'];
                                            echo " | 보증기간: " . $licenseRow['WARRANTY'] . "개월";
                                            echo " | 시작일: " . $licenseRow['S_DATE'];
                                            echo " | 종료일: " . $licenseRow['D_DATE'];
                                            echo " | 파트너지원: " . $licenseRow['INSPECTION'];
                                            echo " | 점검: " . $licenseRow['SUPPORT'];
                                            echo " | 비고: " . $licenseRow['REF'];
                                            // '수정' 버튼 추가
                                            echo "<a href='licenseUpdate.php?saleId=" . urlencode($licenseRow['SALE_ID']) . "&SN=" . urldecode($licenseRow['SN']) . "'class='btn btn-primary' style='font-size:0.9em; height: 40px;'>수정</a>";
                                            echo "</div>";
                                        }
                                    } else {
                                        if (!empty($saleId)) {
                                            // 경로1: 거래명세서에서 SN 클릭해서 들어갈 때 ;GET 으로 데이터 전송
                                            echo "해당 장비에 대한 라이센스 정보가 없습니다.";
                                            echo "<a href='licenseInsertViaDevice.php?SN=$SN&saleId=$saleId' class='btn btn-primary' 
                                        style='width: 120px; font-size: 0.8em; line-height: 40px; margin-left: 10px; align-items: center; 
                                        justify-content: center;'>라이센스 생성</a>";
                                        } else {
                                            echo "해당 장비에 대한 라이센스 정보가 없습니다.";
                                        }
                                    }
                                } else {
                                    if (!empty($saleId)) {
                                        // 경로2: 거래명세서 SN 클릭해서 들어갈 때
                                        // JOIN 문으로 가져온 SALE_ID 값을 활용
                                        echo "<form action='licenseInsertViaDevice.php' method='post'>";
                                        echo "<input type='hidden' name='saleId' value='$saleId'>";
                                        echo "<input type='hidden' name='SN' value='{$row['SN']}'>";
                                        echo "<button type='submit' class='btn btn-primary' style='font-size:11px;'>라이센스 생성</button>";
                                        echo "</form>";
                                    } else {
                                        // 어떤 처리도 하지 않음
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php
                    } // if (!empty($SN)) 종료
                    ?>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
            <script src="jquery-3.6.4.min.js"></script>
            <script src="salesMain.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var searchButton = document.querySelector('.btn.btn-primary.search');
                    if (searchButton) {
                        searchButton.addEventListener('click', goToDeviceSearch);
                    }
                });

                function goToDeviceSearch(event) {
                    event.preventDefault();
                    window.location.href = "deviceSearch.php";
                }
            </script>

</body>
</html>