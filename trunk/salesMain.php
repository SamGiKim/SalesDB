<?php
// sainsMain.php
require_once "auth.php";

// AJAX 요청인지 확인
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

if ($isAjax) {
    header('Content-Type: application/json');  // JSON 응답 설정
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require_once "sales_db.php";

// UTF-8 인코딩 설정
mysqli_set_charset($dbconnect, "utf8");


// 가격 볼수 있는 권한 제한(LOGIN 테이블의 permission열이 admin이나 sales 이고 IP가 내부일때만  TOT_PRICE 볼 수 있다.)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 사용자 권한 확인
$userPermission = ''; //기본값 설정
$isExternalAccess = false; // 기본적으로 내부 액세스로 설정

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $permissionQuery = "SELECT permission FROM LOGIN WHERE user_id=?";
    $stmt = $dbconnect->prepare($permissionQuery);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userPermission = $row['permission'];
    }
    $stmt->close();
}

// 사용자의 IP 주소가 '192'로 시작하지 않는 경우, 외부 접속으로 간주
if (substr($_SERVER['REMOTE_ADDR'], 0, 3) !== '192') {
    $isExternalAccess = true;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

$today = date("Y-m-d");
$message = "전체 : ";
$sortBy = 'SALE_ID'; // 기본 정렬 기준
$receivedSN = null; // 또는 적절한 값
$receivedSaleID = null; // 또는 적절한 값
$receivedOrderNo = null; // 또는 적절한 값

// ------------------------------Pagination------------------------------

// // 페이지네이션 관련 변수 설정
// $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // 현재 페이지 번호
// $limit = 50;  // 한 페이지에 보여줄 데이터 수
// $offset = max(0, ($page - 1) * $limit); // OFFSET 값 계산

// // 데이터베이스에서 해당 페이지에 맞는 데이터를 가져오는 쿼리
// $query = "SELECT * FROM SALES LIMIT ?, ?";
// $stmt = $dbconnect->prepare($query);
// $stmt->bind_param("ii", $offset, $limit);
// $stmt->execute();
// $result = $stmt->get_result();

// // 총 데이터 수를 구하는 쿼리
// $countQuery = "SELECT COUNT(*) FROM SALES";
// $countResult = $dbconnect->query($countQuery);
// if (!$countResult) {
//     die("Error in COUNT query: " . $dbconnect->error);
// }
// $totalCount = $countResult->fetch_row()[0];

// // 전체 페이지 수 계산
// $totalPage = ceil($totalCount / $limit);

// ------------------------------Pagination------------------------------


// selectSales 함수를 사용하여 필요한 조건을 적용한 SQL 쿼리를 생성
$sql = selectSales($sortBy, $receivedSN, $receivedSaleID, $receivedOrderNo);

//dashboard_search_result.php 에서 받은 sale_ids
if (isset($_GET['sale_ids'])) {
    $saleIds = explode(",", $_GET['sale_ids']);
} else {
    $saleIds = [];
}

$action = $_GET['action'] ?? "";  // PHP 7 이후의 null coalescing operator를 사용



//dashboard_search_result.php 에서 받은 sale_ids
if (isset($_GET['sale_ids'])) {
    $saleIds = explode(",", $_GET['sale_ids']);  // 문자열을 배열로 변환
} else {
    $saleIds = [];
}

//deviceMain.php, licenseMain.php에서 받은SN, SALE_ID, ORDER_NO를 링크로(GET) 클릭해서 받아와서 td에 넣어주기 
$receivedSN = isset($_GET['SN']) ? $_GET['SN'] : null;
$receivedSaleID = isset($_GET['SALE_ID']) ? $_GET['SALE_ID'] : null;
$receivedOrderNo = isset($_GET['ORDER_NO']) ? $_GET['ORDER_NO'] : null;


function selectSales($sortBy, $receivedSN = null, $receivedSaleID = null, $receivedOrderNo = null)
{
    $sql =  "SELECT s.SALE_ID as SALE_ID, ";
    $sql .= "v.NAME as V_NAME, ";
    $sql .= "c.NAME as C_NAME, ";
    $sql .= "cbiz.NAME as CBIZ_NAME, ";
    $sql .= "b.NAME as BIZ_NAME, ";
    $sql .= "s.TOT_PRICE as TOT_PRICE, ";
    $sql .= "s.DELIVER_DATE as DELIVER_DATE, ";
    $sql .= "s.S_DATE as S_DATE, ";
    $sql .= "s.D_DATE as D_DATE, ";
    $sql .= "s.WARRANTY as WARRANTY, ";
    $sql .= "s.ORDER_NO as ORDER_NO, ";
    $sql .= "s.REF as REF, ";
    $sql .= "GROUP_CONCAT(d.SN) as SN_LIST ";
    $sql .= "FROM SALES as s ";

    $sql .= "LEFT JOIN VENDOR AS v ON s.V_ID = v.V_ID ";
    $sql .= "LEFT JOIN CUSTOMER AS c ON s.C_ID = c.C_ID ";
    $sql .= "LEFT JOIN CUSTOMER AS cbiz ON s.CBIZ_ID = cbiz.C_ID ";
    $sql .= "LEFT JOIN BUSINESS AS b ON s.BIZ_ID = b.BIZ_ID ";
    $sql .= "LEFT JOIN DEVICE AS d ON s.ORDER_NO = d.ORDER_NO ";  // 주석 해제

    $conditions = [];

    if (!is_null($receivedSN) && !is_array($receivedSN)) {
        $receivedSN = [$receivedSN];
    }
    if (!is_null($receivedSaleID) && !is_array($receivedSaleID)) {
        $receivedSaleID = [$receivedSaleID];
    }

    if (!is_null($receivedSN) && is_array($receivedSN)) {
        $snValues = implode(',', array_map(function ($value) {
            return "'$value'";
        }, $receivedSN));
        $conditions[] = "d.SN IN ($snValues)";
    }

    if (!is_null($receivedSaleID) && is_array($receivedSaleID)) {
        $saleIdValues = implode(',', array_map(function ($value) {
            return "'$value'";
        }, $receivedSaleID));
        $conditions[] = "s.SALE_ID IN ($saleIdValues)";
    }

    if (!is_null($receivedOrderNo)) {
        $conditions[] = "s.ORDER_NO = '$receivedOrderNo'";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " GROUP BY s.SALE_ID ";  // SALE_ID 기준으로 그룹화
    $sql .= " ORDER BY $sortBy DESC";

    return $sql;
}



//salesSearch.php로 들어왔을 때(검색기능)
function searchFunction(
    $saleId,
    $vId = "",
    $cId = "",
    $cbizId = "",
    $bizId = "",
    $deliverDateFrom = "",
    $deliverDateTo = "",
    $sDateFrom = "",
    $sDateTo = "",
    $dDateFrom = "",
    $dDateTo = "",
    $warranty = "",
    $orderNo = "",
    $ref = ""
) {
    $sql =  "SELECT s.SALE_ID as SALE_ID, ";
    $sql .= "v.NAME  as V_NAME, ";
    $sql .= "c.NAME  as C_NAME, ";
    $sql .= "cbiz.NAME as CBIZ_NAME, ";
    $sql .= "b.NAME as BIZ_NAME, ";
    $sql .= "s.TOT_PRICE as TOT_PRICE, ";
    $sql .= "s.DELIVER_DATE as DELIVER_DATE, ";
    $sql .= "s.S_DATE as S_DATE, ";
    $sql .= "s.D_DATE as D_DATE, ";
    $sql .= "s.WARRANTY as WARRANTY, ";
    $sql .= "s.ORDER_NO as ORDER_NO, ";
    $sql .= "s.REF as REF, ";
    $sql .= "d.SN_LIST as SN_LIST "; // SN_LIST를 결과에 추가
    $sql .= "FROM SALES as s ";
    $sql .= "LEFT JOIN VENDOR AS v ON s.V_ID = v.V_ID ";
    $sql .= "LEFT JOIN CUSTOMER AS c ON s.C_ID = c.C_ID ";
    $sql .= "LEFT JOIN CUSTOMER AS cbiz ON s.CBIZ_ID = cbiz.C_ID ";
    $sql .= "LEFT JOIN BUSINESS AS b ON s.BIZ_ID = b.BIZ_ID ";
    $sql .= "LEFT JOIN (SELECT ORDER_NO, GROUP_CONCAT(SN) AS SN_LIST FROM DEVICE GROUP BY ORDER_NO) d ON s.ORDER_NO = d.ORDER_NO "; // DEVICE 테이블과 조인하여 SN_LIST 생성

    $conditions = [];

    if (!empty($saleId)) {
        $conditions[] = "s.SALE_ID LIKE '%" . $saleId . "%'";
    }
    if (!empty($vId)) {
        $conditions[] = "v.NAME LIKE '%" . $vId . "%'";
    }
    if (!empty($cId)) {
        $conditions[] = "c.NAME LIKE '%" . $cId . "%'";
    }
    if (!empty($cbizId)) {
        $conditions[] = "cbiz.NAME LIKE '%" . $cbizId . "%'";
    }
    if (!empty($bizId)) {
        $conditions[] = "b.NAME LIKE '%" . $bizId . "%'";
    }
    if (!empty($deliverDateFrom)) {
        $conditions[] = "s.DELIVER_DATE >= '" . $deliverDateFrom . "'";
    }
    if (!empty($deliverDateTo)) {
        $conditions[] = "s.DELIVER_DATE <= '" . $deliverDateTo . "'";
    }
    if (!empty($sDateFrom)) {
        $conditions[] = "s.S_DATE >= '" . $sDateFrom . "'";
    }
    if (!empty($sDateTo)) {
        $conditions[] = "s.S_DATE <= '" . $sDateTo . "'";
    }
    if (!empty($dDateFrom)) {
        $conditions[] = "s.D_DATE >= '" . $dDateFrom . "'";
    }
    if (!empty($dDateTo)) {
        $conditions[] = "s.D_DATE <= '" . $dDateTo . "'";
    }
    if (!empty($warranty)) {
        $conditions[] = "s.WARRANTY LIKE '%" . $warranty . "%'";
    }
    if (!empty($orderNo)) {
        $conditions[] = "s.ORDER_NO LIKE '%" . $orderNo . "%'";
    }
    if (!empty($ref)) {
        $conditions[] = "s.REF LIKE '%" . $ref . "%'";
    }

    if (!empty($conditions)) {
        $sql .= "WHERE " . implode(" AND ", $conditions) . " ";
    }

    $sql .= "ORDER BY s.SALE_ID DESC";
    return $sql;
}


//EOS 처리함수
function handleEosQuery($eos_start_date, $eos_end_date, $dbconnect)
{
    if (empty($eos_start_date) || empty($eos_end_date)) {
        return ['dateCount' => 0, 'salesData' => []];
    }

    // Count query - D_DATE를 DELIVER_DATE로 변경
    $sqlForDate = "SELECT COUNT(*) as cnt FROM SALES WHERE DELIVER_DATE BETWEEN ? AND ?";
    $stmt = $dbconnect->prepare($sqlForDate);
    if (!$stmt) {
        die('Statement Preparation Error: ' . mysqli_error($dbconnect));
    }
    $stmt->bind_param("ss", $eos_start_date, $eos_end_date);
    if ($stmt->execute()) {
        $resultForDate = $stmt->get_result();
        $row = mysqli_fetch_assoc($resultForDate);
        $dateCount = isset($row['cnt']) ? $row['cnt'] : 0;
    } else {
        die('Date Query Error: ' . mysqli_error($dbconnect));
    }
    $stmt->close();

    // Detailed query - JOIN 수정
    $sql  = "SELECT DISTINCT "; // DISTINCT 추가
    $sql .= "s.SALE_ID, ";
    $sql .= "COALESCE(v.NAME, '') as V_NAME, "; // COALESCE 추가
    $sql .= "COALESCE(c.NAME, '') as C_NAME, ";
    $sql .= "COALESCE(cbiz.NAME, '') as CBIZ_NAME, ";
    $sql .= "COALESCE(b.NAME, '') as BIZ_NAME, ";
    $sql .= "d.SN as SN_LIST, ";
    $sql .= "s.TOT_PRICE, ";
    $sql .= "s.DELIVER_DATE, ";
    $sql .= "s.S_DATE, ";
    $sql .= "s.D_DATE, ";
    $sql .= "s.WARRANTY, ";
    $sql .= "s.ORDER_NO, ";
    $sql .= "s.REF ";
    $sql .= "FROM SALES s ";
    $sql .= "LEFT JOIN VENDOR v ON s.V_ID = v.V_ID ";
    $sql .= "LEFT JOIN CUSTOMER c ON s.C_ID = c.C_ID ";
    $sql .= "LEFT JOIN CUSTOMER cbiz ON s.CBIZ_ID = cbiz.C_ID ";
    $sql .= "LEFT JOIN BUSINESS b ON s.BIZ_ID = b.BIZ_ID ";
    $sql .= "LEFT JOIN DEVICE d ON s.ORDER_NO = d.ORDER_NO ";
    $sql .= "WHERE s.DELIVER_DATE BETWEEN ? AND ? ";
    $sql .= "ORDER BY s.SALE_ID DESC";

    $stmt = $dbconnect->prepare($sql);
    $stmt->bind_param("ss", $eos_start_date, $eos_end_date);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $salesData = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        die('Detailed Query Error: ' . mysqli_error($dbconnect));
    }
    $stmt->close();

    return ['dateCount' => $dateCount, 'salesData' => $salesData];
}

$response = handleEosQuery($_GET['eos_start_date'] ?? null, $_GET['eos_end_date'] ?? null, $dbconnect);
$dateCount = $response['dateCount'];
$result = $response['salesData'];



// case 1. eos(dashboard.php에서 유입)
if (isset($_GET['condition']) && $_GET['condition'] == 'eos') {
    $message = "EOS : ";
    $response = handleEosQuery($_GET['eos_start_date'] ?? null, $_GET['eos_end_date'] ?? null, $dbconnect);
    $totalCount = $response['dateCount'];

    // case2. sale_ids는 대시보드에서 통합검색 했을때 필터링해서 보여주는 것.
} else if (isset($_GET['sale_ids']) && $_GET['sale_ids'] != '') {
    $saleIdsFilter = explode(',', $_GET['sale_ids']);
    $sortBy = "S_DATE";
    $sql = selectSales($sortBy, null, $saleIdsFilter); // SN, SALE_ID, OrderNo 중 SALE_ID만 사용
    $stmt = $dbconnect->prepare($sql);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $totalCount = mysqli_num_rows($result);
    } else {
        die('Query Error: ' . mysqli_error($dbconnect));
    }
    $stmt->close();

    // case 3. salesSearch.php 통해 유입
} else if ($action == "search") {
    $message = "조건 검색: ";
    $sql = searchFunction(
        $_POST["saleId"] ?? "",
        $_POST["vId"] ?? "",
        $_POST["cId"] ?? "",
        $_POST["cbizId"] ?? "",
        $_POST["bizId"] ?? "",
        $_POST["deliverDateFrom"] ?? "",
        $_POST["deliverDateTo"] ?? "",
        $_POST["sDateFrom"] ?? "",
        $_POST["sDateTo"] ?? "",
        $_POST["dDateFrom"] ?? "",
        $_POST["dDateTo"] ?? "",
        $_POST["WARRANTY"] ?? "",
        $_POST["orderNo"] ?? "",
        $_POST["REF"] ?? ""
    );
    $result = mysqli_query($dbconnect, $sql);
    $totalCount = mysqli_num_rows($result); // 결과의 총 건수 업데이트
    if (!$result) {
        die('Query Error: ' . mysqli_error($dbconnect));
    }

    // case 4. deviceMain.php, licenseMain.php의 SN, ORDER_NO, SALE_ID를 통해 들 유입
} else {
    $sortBy = $_GET['sort'] ?? 'SALE_ID'; // 기본 정렬 기준
    $sql = selectSales($sortBy, $receivedSN, $receivedSaleID, $receivedOrderNo);

    $result = mysqli_query($dbconnect, $sql);
    if (!$result) {
        die("Query Failed: " . mysqli_error($dbconnect));
    }

    $totalCount = mysqli_num_rows($result); // 결과의 총 건수 업데이트
}

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

    <script>
        // sales를 통해서 SN클릭하면 DEVICE로 SN과 SALE_ID를 전송한다.
        function openDeviceMain(DEVICE_SN, SALE_ID) {
            console.log('openDeviceMain called with SN:', DEVICE_SN, ', SALE_ID:', SALE_ID);
            // DEVICE_SN과 SALE_ID 값을 사용하여 관련 페이지로 이동하거나 필요한 작업 수행
            var url = 'deviceMain.php?SN=' + encodeURIComponent(DEVICE_SN) + '&SALE_ID=' + encodeURIComponent(SALE_ID);
            window.location.href = url;
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php if ($action != "search") {
        include 'navbar.php';
    } ?>
    <div class="container-fluid mt-5 main-screen">
        <div class="row">
            <div class="col-12 text-center">
                <header>거래명세서</header>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-start main-top-btn">
                <button type="button" class="btn btn-primary insert mr-2" onclick="goToSalesInsert()">신규</button>
                <button type="button" class="btn btn-primary search" onclick="goToSalesSearch()">검색</button>
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
                                <!-- tablesorter 쓰면 안되고, 나중에 pagination이랑 함께 해야하기 때문에 각 th를 클릭했을 때, 각각 select 해서 정렬하도록 한다. -->
                                <th scope="col" class="col-1" data-sortable="true">판매번호<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                                <th scope="col" class="col-1" data-sortable="true">납품처<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                                <th scope="col" class="col-1" data-sortable="true">거래처<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                                <th scope="col" class="col-1" data-sortable="true">거래처영업<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                                <th scope="col" class="col-1" data-sortable="true">담당자명<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>

                                <?php if (($userPermission == 'admin' || $userPermission == 'sales') && !$isExternalAccess) : ?>
                                    <th scope="col" class="col-2" data-sortable="true">
                                        공급가액합계<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon">
                                    </th>
                                <?php endif; ?>

                                <th scope="col" class="col-1" data-sortable="true">납품일<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                                <th scope="col" class="col-1" data-sortable="true">유지보수시작일<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                                <th scope="col" class="col-1" data-sortable="true">유지보수종료일<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                                <th scope="col" class="col-1" data-sortable="true">주문번호<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                                <th scope="col" class="col-1" data-sortable="true">보증기간<img src="/sales/img/up-down-arrow.png" alt="sort" class="arrow-icon"></th>
                            </tr>
                        </thead>
                        <tbody style="width:50%;">
                            <?php
                            $counter = 1;  // 카운터를 사용하여 각 아코디언 항목의 ID를 생성.
                            // while ($row = mysqli_fetch_assoc($result)): 
                            foreach ($result as $row) :
                            ?>
                                <tr data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $counter; ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $counter; ?>">
                                    <td class="col-1"><?php echo $row['SALE_ID']; ?></td>
                                    <td class="col-1"><?php echo $row['V_NAME']; ?></td>
                                    <td class="col-1"><?php echo $row['C_NAME']; ?></td>
                                    <td class="col-1"><?php echo $row['CBIZ_NAME']; ?></td>
                                    <td class="col-1"><?php echo $row['BIZ_NAME']; ?></td>

                                    <?php if (($userPermission == 'admin' || $userPermission == 'sales') && !$isExternalAccess) : ?>
                                        <td class="col-2" style="text-align: right; padding-right: 5%;">
                                            <?php
                                            $price = isset($row['TOT_PRICE']) ? $row['TOT_PRICE'] : 0;
                                            echo number_format($price) . '<span>원</span>';
                                            ?>
                                        </td>
                                    <?php endif; ?>

                                    <td class="col-1"><?php echo $row['DELIVER_DATE']; ?></td>
                                    <td class="col-1"><?php echo $row['S_DATE']; ?></td>
                                    <td class="col-1"><?php echo $row['D_DATE']; ?></td>
                                    <td class="col-1" <?php if (empty($row['SN_LIST'])) {
                                                            echo 'style="color: red;"';
                                                        } ?>><?php echo $row['ORDER_NO']; ?></td> <!--SN LIST 없는 경우 빨간색으로 추가요청사항-->
                                    <td class="col-1"><?php echo $row['WARRANTY']; ?><span>개월</span></td>
                                </tr>
                                <!-- 아코디언 -->
                                <tr>
                                    <td colspan="12">
                                        <div id="flush-collapse<?php echo $counter; ?>" class="collapse accor-style">
                                            <table class="detail-table">
                                                <!-- 여러개의 SN이 나와야하는데 하나의 SN만 나오는 것 솔루션 : GROUP_CONCAT 함수로 DEVICE 테이블의 모든 SN 값을 쉼표로 연결된 문자열로 반환 -->
                                                <!-- DEVICE 테이블에서 ORDER_NO="20190111-2" 조건을 사용하여 쿼리하면 결과로 두 개의 SN 값이 반환.
                                    그러나 SALES 테이블을 쿼리할 때 S.SALE_ID를 조건으로 사용하고, DEVICE 테이블과 LEFT JOIN을 사용하여 연결한다.
                                    그 결과, DEVICE_SN 컬럼의 값은 한 개의 SN 값만 반환될 수 있습니다. 왜냐하면 fetch_assoc() 함수는 결과 세트에서 단 하나의 행만 반환하기 때문.
                                    GROUP_CONCAT: GROUP_CONCAT 함수를 사용하여 여러 SN 값을 하나의 문자열로 연결할 수 있다. -->
                                                <?php
                                                $stmt = $dbconnect->prepare("SELECT 
                                        GROUP_CONCAT(D.SN) as DEVICE_SN,
                                        V.CONTACT as VENDOR_CONTACT, 
                                        V.EMAIL as VENDOR_EMAIL,
                                        S.REF AS SALES_REF,
                                        B.CONTACT as BUSINESS_CONTACT,
                                        B.EMAIL as BUSINESS_EMAIL,
                                        S.ORDER_NO as ORDER_NO
                                    FROM 
                                        SALES S
                                    LEFT JOIN 
                                        VENDOR V ON S.V_ID = V.V_ID
                                    LEFT JOIN 
                                        BUSINESS B ON S.BIZ_ID = B.BIZ_ID
                                    LEFT JOIN
                                        DEVICE D ON S.ORDER_NO = D.ORDER_NO
                                    WHERE 
                                        S.SALE_ID = ?
                                    GROUP BY 
                                        S.SALE_ID, V.CONTACT, V.EMAIL, S.REF, B.CONTACT, B.EMAIL, S.ORDER_NO;
                                    ");
                                                $stmt->bind_param("s", $row['SALE_ID']);
                                                $stmt->execute();
                                                $details = $stmt->get_result()->fetch_assoc();
                                                ?>
                                                <tr>
                                                    <th style="font-weight: bold; ">장비 SN :</th>
                                                    <td>
                                                        <?php
                                                        if (!empty($details['DEVICE_SN'])) {
                                                            $deviceSNs = explode(",", $details['DEVICE_SN']);

                                                            //디버깅
                                                            // echo "DEVICE_SN: " . $details['DEVICE_SN'];

                                                            //디버깅
                                                            // print_r($deviceSNs);

                                                            $links = []; // 각 DEVICE_SN에 대한 링크를 저장할 배열

                                                            foreach ($deviceSNs as $deviceSN) {
                                                                $deviceSN = trim($deviceSN); // 공백 제거
                                                                $deviceSNSaleIdLink = 'deviceMain.php?SN=' . urlencode($deviceSN) . '&ORDER_NO=' . urlencode($details['ORDER_NO']);
                                                                $links[] = '<a href="' . $deviceSNSaleIdLink . '">' . $deviceSN . '</a>';
                                                            }

                                                            // 모든 링크를 쉼표로 연결하여 출력
                                                            echo implode(", ", $links);
                                                        } else {
                                                            echo "-";
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th style="margin-left:10px; font-weight: bold;">납품처 전화 :</th>
                                                    <td><?php echo !empty($details['VENDOR_CONTACT']) ? $details['VENDOR_CONTACT'] : '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th style="margin-left:10px; font-weight: bold;">납품처 메일 :</th>
                                                    <td><?php echo !empty($details['VENDOR_EMAIL']) ? $details['VENDOR_EMAIL'] : '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th style="margin-left:10px; font-weight: bold;">비고 :</th>
                                                    <td><?php echo !empty($details['SALES_REF']) ? $details['SALES_REF'] : '-'; ?></td>
                                                </tr>
                                                <!-- <tr>
                                          <th style="padding-left: 5px; font-weight: bold;">영업 전화 :</th>
                                          <td><?php echo !empty($details['BUSINESS_CONTACT']) ? $details['BUSINESS_CONTACT'] : 'N/A'; ?></td>
                                      </tr>
                                      <tr>
                                          <th style="padding-left: 5px; font-weight: bold;">영업 메일 :</th>
                                          <td><?php echo !empty($details['BUSINESS_EMAIL']) ? $details['BUSINESS_EMAIL'] : 'N/A'; ?></td>
                                      </tr> -->
                                                <tr>
                                                    <td colspan="2" class="update-btn-container">
                                                        <a href="salesUpdate.php?saleId=<?php echo urlencode($row['SALE_ID']); ?>" class="btn btn-secondary accor-update" style="margin-left:15px;">수정</a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                $counter++;
                            endforeach;
                            // 데이터베이스 연결 종료
                            $dbconnect->close();
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- <div class="pagination"> -->
                    <!-- <ul class="pagination-list"> -->
                        <!-- 이전 버튼 -->
                        <!-- <li class="page-item prev">
                            <a href="?page=<?php echo ($page > 1) ? $page - 1 : 1; ?>">« 이전</a>
                        </li> -->

                        <!-- 페이지 버튼 -->
                        <!-- <?php for ($i = 1; $i < $totalPage; $i++) : ?>
                            <li class="page-item" data-page="<?= $i ?>">
                                <a href="javascript:void(0);" onclick="goToPage(<?= $i ?>)"><?= $i ?></a>
                            </li>
                        <?php endfor; ?> -->

                        <!-- 다음 버튼 -->
                        <!-- <li class="page-item next">
                            <a href="?page=<?php echo ($page < $totalPage) ? $page + 1 : $totalPage; ?>">다음 »</a>
                        </li>
                    </ul> -->
                <!-- </div> -->
            </div>
        </div>
        <script src="salesMain.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
        <script>
            var el = document.createElement("script");
            el.src = ".__/auto_complete.js";
            // el.src = "/sales/.__/auto_complete.js";
            document.body.appendChild(el);
        </script>
</body>

</html>