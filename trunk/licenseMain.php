<?php
// licenseMain.php
require_once "auth.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";
require_once "pagination.php";

// UTF-8 인코딩 설정
mysqli_set_charset($dbconnect, "utf8");

$today = date("Y-m-d");
$message = "전체 : ";
$action = $_GET['action'] ?? "";
$itemsPerPage = 50;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : '';
if ($page < 1) $page = 1;

// 동적 조건을 생성하는 함수
function addDynamicConditions($dbconnect, $params)
{
    $conditions = [];
    if (!empty($params['vendorName'])) {
        $vendorName = mysqli_real_escape_string($dbconnect, $params['vendorName']);
        $conditions[] = "V.NAME LIKE '%" . $vendorName . "%'";
    }
    // 다른 조건들도 이와 유사하게 추가
    return $conditions;
}

// 기본 쿼리 
$query = "SELECT L.SALE_ID, L.SN, L.TYPE, L.MANAGER, L.PRICE, L.S_DATE, L.D_DATE, L.WARRANTY, L.INSPECTION, L.SUPPORT, L.REF, V.NAME AS VENDOR_NAME
          FROM LICENSE AS L
          JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
          JOIN VENDOR AS V ON S.V_ID = V.V_ID"; //쿼리 잘 작동됨

// 사용자 입력 또는 기타 조건을 기반으로 한 파라미터 배열
$params = []; // 예를 들어, $_GET 또는 $_POST에서 파라미터를 가져올 수 있습니다.

// 동적 조건 추가
$conditions = addDynamicConditions($dbconnect, $params);
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// 조건이 추가된 쿼리를 바탕으로 데이터베이스에서 데이터 가져오기
$result = mysqli_query($dbconnect, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // VENDOR_NAME이 결과 집합에 포함되어 있다고 가정하고 직접 사용
        // 필요한 경우 여기에서 row 데이터를 처리하거나 출력

    }
} else {
    echo "Query failed: " . mysqli_error($dbconnect);
}

// 대시보드랑 연결
// Check connection
if ($dbconnect->connect_error) {
    die("Connection failed: " . $dbconnect->connect_error);
}

function get_count_from_dashboard($cmd) {
    global $dbconnect;

    $baseCount = "SELECT COUNT(*) AS cnt
                  FROM LICENSE AS L
                  JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
                  JOIN VENDOR AS V ON S.V_ID = V.V_ID";

    $d_date_tobe_expired = date("Y-m-d", strtotime("+30 days"));

    switch ($cmd) {
        case "001":
            $where = "L.TYPE = '유상' AND L.D_DATE BETWEEN CURDATE() AND '$d_date_tobe_expired'";
            break;
        case "002":
            $where = "L.TYPE = '무상' AND L.D_DATE BETWEEN CURDATE() AND '$d_date_tobe_expired'";
            break;
        case "003":
            $where = "L.TYPE = '유상' AND L.D_DATE <= CURDATE()";
            break;
        case "004":
            $where = "L.TYPE = '무상' AND L.D_DATE <= CURDATE()";
            break;
        default:
            $where = "1"; // 조건없음 전체
            break;
    }

    $countQuery = "$baseCount WHERE $where";
    $res = mysqli_query($dbconnect, $countQuery);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        return intval($row['cnt']);
    } else {
        return 0;
    }
}

function get_sql_queried_from_dashboard($cmd, $limit = null, $offset = null)
{
    $baseSelect = "SELECT L.SALE_ID, L.SN, L.TYPE, L.MANAGER, L.PRICE, L.S_DATE, L.D_DATE, L.REF, L.WARRANTY, 
                   L.INSPECTION, L.SUPPORT, V.NAME AS VENDOR_NAME
                   FROM LICENSE AS L
                   JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
                   JOIN VENDOR AS V ON S.V_ID = V.V_ID";

    $d_date_tobe_expired = date("Y-m-d", strtotime("+30 days"));

    switch ($cmd) {
        case "001":
            $message = "유상 보증기간 종료 예정(D-30) : ";
            $where = "L.TYPE = '유상' AND L.D_DATE BETWEEN CURDATE() AND '$d_date_tobe_expired'";
            $order = "ORDER BY L.D_DATE ASC";
            break;
        case "002":
            $message = "무상 보증기간 종료 예정(D-30) : ";
            $where = "L.TYPE = '무상' AND L.D_DATE BETWEEN CURDATE() AND '$d_date_tobe_expired'";
            $order = "ORDER BY L.D_DATE ASC";
            break;
        case "003":
            $message = "유상 보증기간 만료 : ";
            $where = "L.TYPE = '유상' AND L.D_DATE <= CURDATE()";
            $order = "ORDER BY L.D_DATE DESC";
            break;
        case "004":
            $message = "무상 보증기간 만료 : ";
            $where = "L.TYPE = '무상' AND L.D_DATE <= CURDATE()";
            $order = "ORDER BY L.D_DATE DESC";
            break;
        default:
            $message = "전체 : ";
            $where = "1";
            $order = "ORDER BY L.SALE_ID DESC";
            break;
    }

    $sql = "$baseSelect WHERE $where $order";

    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }

    return ['sql' => $sql, 'message' => $message];
}

//name 값들을 DB 컬럼값으로 고쳐줘야하므로 매핑을 하고
$fieldToDbColumnMapping = [
    'saleId'    => 'L.SALE_ID',
    'SN'        => 'L.SN',
    'type'      => 'L.TYPE',
    'manager'   => 'L.MANAGER',
    'sDateFrom' => 'L.S_DATE',
    'sDateTo'   => 'L.S_DATE',
    'dDateFrom' => 'L.D_DATE',
    'dDateTo'   => 'L.D_DATE',
    'ref'       => 'L.REF',
    'warranty'  => 'L.WARRANTY',
    'inspection' => 'L.INSPECTION',
    'support'   => 'L.SUPPORT',
    'vendorName' => "V.NAME" // 별칭이 아니라 AS 전에 오는 컬럼명을 적어줘야한다. 
];


$conditions = [];
//기간->날짜 검색은 아래와 같이 처리해준다. 
foreach ($fieldToDbColumnMapping as $field => $dbColumn) {
    if (isset($_REQUEST[$field]) && !empty($_REQUEST[$field])) {
        switch ($field) {
            case 'sDateFrom':
                // 유지보수 시작일 시작 날짜 범위 처리 (S_DATE의 시작 범위)
                $conditions[] = $dbColumn . " >= '" . mysqli_real_escape_string($dbconnect, $_REQUEST[$field]) . "'";
                break;
            case 'sDateTo':
                // 유지보수 시작일 종료 날짜 범위 처리 (S_DATE의 종료 범위)
                $conditions[] = $dbColumn . " <= '" . mysqli_real_escape_string($dbconnect, $_REQUEST[$field]) . "'";
                break;
            case 'dDateFrom':
                // 유지보수 종료일 시작 날짜 범위 처리 (D_DATE의 시작 범위)
                $conditions[] = $dbColumn . " >= '" . mysqli_real_escape_string($dbconnect, $_REQUEST[$field]) . "'";
                break;
            case 'dDateTo':
                // 유지보수 종료일 종료 날짜 범위 처리 (D_DATE의 종료 범위)
                $conditions[] = $dbColumn . " <= '" . mysqli_real_escape_string($dbconnect, $_REQUEST[$field]) . "'";
                break;
            default:
                // 일반 조건 처리
                // $conditions[] = $dbColumn . " = '%" . mysqli_real_escape_string($dbconnect, trim($_REQUEST[$field])) . "%'"; // 230920 trim으로 공백을 제거해줘야 SQL이 유효함
                $conditions[] = $dbColumn . " LIKE '%" . mysqli_real_escape_string($dbconnect, trim($_REQUEST[$field])) . "%'";
                break;
        }
    }
    // 조건이 있는 경우 WHERE 절 구성
    if (!empty($conditions)) {
        $where_clause = " WHERE " . implode(" AND ", $conditions);
        $query .= $where_clause;
    }
}

// <<< 230907 hjkim - REFERER별 처리
// referer : 현재 페이지에 접근하기 직전에 사용자가 머물렀던 웹 페이지의 URL을 가져올 때 리퍼러라 부름.
$from_url_path = "";  // 기본값 설정

if (isset($_SERVER['HTTP_REFERER'])) { //referer 값이 설정되어있는지 먼저 확인.
    $from_url = parse_url($_SERVER['HTTP_REFERER']);
    if (isset($from_url['path'])) {
        $from_url_path = $from_url["path"];
    }
}

switch ($from_url_path) {
    case "/sales/dashboard.html":
    case "/sales/dashboard.php":
        if (!isset($_GET["cmd"])) {
            goto DEFAULT_PAGE;
        }
        $dashboard_result = get_sql_queried_from_dashboard($_GET["cmd"]);
        $query = $dashboard_result['sql'];
        $message = $dashboard_result['message'];
        // 쿼리 실행
        $result = mysqli_query($dbconnect, $query);
        if (!$result) {
            // echo "Query Error: " . mysqli_error($dbconnect) . "<br>";
        } else {
            $totalCount = mysqli_num_rows($result);
        }
        echo "</div>";
        break;
    case "/sales/deviceMain.php":
        if (isset($_GET['SN'])) {
            $sn = mysqli_real_escape_string($dbconnect, $_GET['SN']);
            $query = "SELECT L.SALE_ID, L.SN, L.TYPE, L.MANAGER, L.PRICE, L.S_DATE, L.D_DATE, L.WARRANTY, 
                             L.INSPECTION, L.SUPPORT, L.REF, V.NAME AS VENDOR_NAME
                      FROM LICENSE AS L
                      JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
                      JOIN VENDOR AS V ON S.V_ID = V.V_ID
                      WHERE L.SN = '$sn'
                      ORDER BY L.SALE_ID DESC";
            
            $message = "SN으로 조회: " . $sn;
            // 쿼리 실행
            $result = mysqli_query($dbconnect, $query);
            if (!$result) {
                echo "Query Error: " . mysqli_error($dbconnect) . "<br>";
            } else {
                $totalCount = mysqli_num_rows($result);
            }
        } else {
            goto DEFAULT_PAGE;
        }
        break;
    // 테스트 코드
    case "/sales_test/dashboard.html":
    case "/sales_test/dashboard.php":
        if (!isset($_GET["cmd"])) {
            goto DEFAULT_PAGE;
        }
        $dashboard_result = get_sql_queried_from_dashboard($_GET["cmd"]);
        $query = $dashboard_result['sql'];
        $message = $dashboard_result['message'];
        // 쿼리 실행
        $result = mysqli_query($dbconnect, $query);
        if (!$result) {
            // echo "Query Error: " . mysqli_error($dbconnect) . "<br>";
        } else {
            $totalCount = mysqli_num_rows($result);
        }
        echo "</div>";
        break;
    case "/sales_test/deviceMain.php":
        if (isset($_GET['SN'])) {
            $sn = mysqli_real_escape_string($dbconnect, $_GET['SN']);
            $query = "SELECT L.SALE_ID, L.SN, L.TYPE, L.MANAGER, L.PRICE, L.S_DATE, L.D_DATE, L.WARRANTY, 
                             L.INSPECTION, L.SUPPORT, L.REF, V.NAME AS VENDOR_NAME
                      FROM LICENSE AS L
                      JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
                      JOIN VENDOR AS V ON S.V_ID = V.V_ID
                      WHERE L.SN = '$sn'
                      ORDER BY L.SALE_ID DESC";
            
            $message = "SN으로 조회: " . $sn;
            // 쿼리 실행
            $result = mysqli_query($dbconnect, $query);
            if (!$result) {
                echo "Query Error: " . mysqli_error($dbconnect) . "<br>";
            } else {
                $totalCount = mysqli_num_rows($result);
            }
        } else {
            goto DEFAULT_PAGE;
        }
        break;
    default:
        DEFAULT_PAGE:
        $query = "SELECT 
            L.SALE_ID, L.SN, L.TYPE, L.MANAGER, L.PRICE, L.S_DATE, L.D_DATE, L.WARRANTY, L.INSPECTION, L.SUPPORT, L.REF, V.NAME AS VENDOR_NAME
            FROM LICENSE AS L
            JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
            JOIN VENDOR AS V ON S.V_ID = V.V_ID";

        if (!empty($conditions)) {
            $where_clause = " WHERE " . implode(" AND ", $conditions);
            $query .= $where_clause;
        }

        $query .= " ORDER BY L.SALE_ID DESC";
        $result = mysqli_query($dbconnect, $query);
        if (!$result) {
            // echo "Query Error: " . mysqli_error($dbconnect) . "<br>";
        } else {
            $totalCount = mysqli_num_rows($result);
        }
        break;
}

// 결과 커서 리셋
if ($result) {
    mysqli_data_seek($result, 0);
}

// .detail-table 안에 들어갈 데이터 불러오기
$details = [];  // 빈 배열로 초기화
$sale_ids = [];  // SALE_ID 값을 저장하기 위한 배열

while ($row = mysqli_fetch_assoc($result)) {
    $saleIdKey = $row['SALE_ID'];
    $details[$saleIdKey] = $row;
}

if ($sale_ids) {
    $ids_list = implode(',', $sale_ids);
    $query = "
        SELECT 
            L.SALE_ID,
            L.SN,
            B.NAME as BUSINESS_NAME, 
            B.CONTACT as BUSINESS_CONTACT, 
            B.EMAIL as BUSINESS_EMAIL, 
            C.NAME as CUSTOMER_NAME
        FROM LICENSE L
        JOIN SALES S ON L.SALE_ID = S.SALE_ID
        JOIN BUSINESS B ON S.CBIZ_ID = B.BIZ_ID
        JOIN CUSTOMER C ON S.C_ID = C.C_ID
        WHERE L.SALE_ID IN ($ids_list)";

    if ($result = $dbconnect->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $details[$row['SALE_ID']] = $row;
        }
    } else {
        die("Query failed: (" . $dbconnect->errno . ") " . $dbconnect->error);
    }
}

$searchMode = false;
$searchConditions = [];
$params = [];
$types = '';

if (!empty($_GET['saleId'])) {
    $searchMode = true;
    $searchConditions[] = "L.SALE_ID LIKE ?";
    $params[] = "%" . $_GET['saleId'] . "%";
    $types .= 's';
}
if (!empty($_GET['SN'])) {
    $searchMode = true;
    $searchConditions[] = "L.SN LIKE ?";
    $params[] = "%" . $_GET['SN'] . "%";
    $types .= 's';
}
if (!empty($_GET['vendorName'])) {
    $searchMode = true;
    $searchConditions[] = "V.NAME LIKE ?";
    $params[] = "%" . $_GET['vendorName'] . "%";
    $types .= 's';
}
if (!empty($_GET['type'])) {
    $searchMode = true;
    $searchConditions[] = "L.TYPE LIKE ?";
    $params[] = "%" . $_GET['type'] . "%";
    $types .= 's';
}
if (!empty($_GET['manager'])) {
    $searchMode = true;
    $searchConditions[] = "L.MANAGER LIKE ?";
    $params[] = "%" . $_GET['manager'] . "%";
    $types .= 's';
}
if (!empty($_GET['sDateFrom']) && !empty($_GET['sDateTo'])) {
    $searchMode = true;
    $searchConditions[] = "L.S_DATE BETWEEN ? AND ?";
    $params[] = $_GET['sDateFrom'];
    $params[] = $_GET['sDateTo'];
    $types .= 'ss';
} else if (!empty($_GET['sDateFrom'])) {
    $searchMode = true;
    $searchConditions[] = "L.S_DATE >= ?";
    $params[] = $_GET['sDateFrom'];
    $types .= 's';
} else if (!empty($_GET['sDateTo'])) {
    $searchMode = true;
    $searchConditions[] = "L.S_DATE <= ?";
    $params[] = $_GET['sDateTo'];
    $types .= 's';
}
if (!empty($_GET['dDateFrom']) && !empty($_GET['dDateTo'])) {
    $searchMode = true;
    $searchConditions[] = "L.D_DATE BETWEEN ? AND ?";
    $params[] = $_GET['dDateFrom'];
    $params[] = $_GET['dDateTo'];
    $types .= 'ss';
} else if (!empty($_GET['dDateFrom'])) {
    $searchMode = true;
    $searchConditions[] = "L.D_DATE >= ?";
    $params[] = $_GET['dDateFrom'];
    $types .= 's';
} else if (!empty($_GET['dDateTo'])) {
    $searchMode = true;
    $searchConditions[] = "L.D_DATE <= ?";
    $params[] = $_GET['dDateTo'];
    $types .= 's';
}
if (!empty($_GET['type'])) {
    $searchMode = true;
    $searchConditions[] = "L.TYPE LIKE ?";
    $params[] = "%" . $_GET['type'] . "%";
    $types .= 's';
}
if (!empty($_GET['inspection'])) {
    $searchMode = true;
    $searchConditions[] = "L.INSPECTION LIKE ?";
    $params[] = "%" . $_GET['inspection'] . "%";
    $types .= 's';
}
if (!empty($_GET['support'])) {
    $searchMode = true;
    $searchConditions[] = "L.SUPPORT LIKE ?";
    $params[] = "%" . $_GET['support'] . "%";
    $types .= 's';
}
if (!empty($_GET['ref'])) {
    $searchMode = true;
    $searchConditions[] = "L.REF LIKE ?";
    $params[] = "%" . $_GET['ref'] . "%";
    $types .= 's';
}

if ($searchMode) {
    $sqlCount = "SELECT COUNT(*) AS cnt
                 FROM LICENSE AS L
                 JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
                 JOIN VENDOR AS V ON S.V_ID = V.V_ID
                 WHERE " . implode(' AND ', $searchConditions);

    $stmt = $dbconnect->prepare($sqlCount);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $totalCount = intval($row['cnt']);
    $totalPages = ceil($totalCount / $itemsPerPage);

    $offset = ($page - 1) * $itemsPerPage;
    $sqlData = "SELECT L.*, V.NAME AS VENDOR_NAME
                FROM LICENSE AS L
                JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
                JOIN VENDOR AS V ON S.V_ID = V.V_ID
                WHERE " . implode(' AND ', $searchConditions) .
            " LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $dbconnect->prepare($sqlData);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // 결과 재설정 (원래대로 돌아가서 다시 사용 가능하게 함)
    mysqli_data_seek($result, 0);

    // 1. 전체 데이터 개수 구하기
    $totalCount = get_count_from_dashboard($cmd);
    $totalPages = ceil($totalCount / $itemsPerPage);

    // 2. 오프셋 계산
    $offset = ($page - 1) * $itemsPerPage;

    // 3. 페이징 포함 데이터 쿼리 생성
    $queryInfo = get_sql_queried_from_dashboard($cmd, $itemsPerPage, $offset);
    $query = $queryInfo['sql'];
    $message = $queryInfo['message'];

    // 4. 쿼리 실행
    $result = mysqli_query($dbconnect, $query);
    if (!$result) {
        die("Query Failed: " . mysqli_error($dbconnect));
    }
}
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
    <style>
        .item-separator td {
            padding: 5px 0;
            /* 위아래로 패딩을 줘서 구분선과의 간격을 만듭니다. */
        }

        .item-separator td::after {
            content: "";
            /* 가상 요소를 사용하여 구분선을 만듭니다. */
            display: block;
            margin: 10px 0;
            /* 구분선 위아래로 마진을 줘서 공간을 만듭니다. */
            border-bottom: 1px solid #ccc;
            /* 구분선 스타일 */
        }

        .detail-table tbody tr th,
        .detail-table tbody tr td {
            text-align: left;
        }
    </style>
    <script>
        function downloadFilteredCsv() {
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            const params = url.search;
            window.location.href = 'export_license_filter_csv.php' + params;
        }
    </script>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid mt-5  main-screen">
        <div class="row">
            <div class="col-12 text-center">
                <header>라이센스</header>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-start main-top-btn">
                <button type="button" class="btn btn-primary insert mr-2" onclick="goToLicenseInsert()">신규</button>
                <button type="button" class="btn btn-primary search" onclick="goToLicenseSearch()">검색</button>
                <button type="button" class="btn-primary csv" onclick="window.location.href='export_license_csv.php'">전체 CSV</button>
                <button type="button" class="btn-primary csv" onclick="downloadFilteredCsv()">검색 CSV</button>
            </div>
            <!-- >>>>>> 230920 총 건수 -->
            <div class="total-number" style="text-align:left; margin-left:2%; font-size: 1.2em; font-weight:bold;">
                <!-- <?= $message ?><span> </span><?= $totalCount ?> 건 -->
                <?= $message ?><?= $totalCount ?> 건
            </div>
            <!-- <<<<<< 230920 총 건수 -->
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-wrapper table-responsive">
                    <!-- The rest of the table here... -->
                    <table class="table main-tbl lcs-main-tbl">
                        <thead>
                            <tr>
                                <!-- tablesorter 쓰면 안되고, 나중에 pagination이랑 함께 해야하기 때문에 각 th를 클릭했을 때, 각각 select 해서 정렬하도록 한다. -->
                                <th scope="col" class="col-1">명세서번호</th>
                                <th scope="col" class="col-2">SN</th>
                                <th scope="col" class="col-1">납품처</th>
                                <th scope="col" class="col-1 maintenance-type">유형</th>
                                <th scope="col" class="col-1">담당 엔지니어</th>
                                <th scope="col" class="col-1" style="text-align:right; padding-right:2%;">가격</th>
                                <th scope="col" class="col-1">보증기간</th>
                                <th scope="col" class="col-1">시작일</th>
                                <th scope="col" class="col-1">종료일</th>
                                <th scope="col" class="col-1">점검 / 파트너지원</th>
                                <th scope="col" class="col-1">비고</th>
                                <th scope="col" class="col-1">수정</th>
                            </tr>
                        </thead>
                        <tbody class="main-screen">
                            <?php
                            $counter = 1;  // 각 데이터 행과 세부 정보 행의 ID를 생성하는 카운터 변수
                            while ($row = mysqli_fetch_assoc($result)) :

                                //0322 추가코드
                                // 거래처 정보 가져오기
                                // $vendor_query = "SELECT NAME FROM VENDOR WHERE V_ID = (SELECT V_ID FROM SALES WHERE SALE_ID = '{$row['SALE_ID']}')";
                                // $vendor_result = mysqli_query($dbconnect, $vendor_query);
                                // if ($vendor_result) {
                                //     $vendor_row = mysqli_fetch_assoc($vendor_result);
                                // } else {
                                //     // 쿼리 실행 실패 처리
                                //     echo "쿼리 실행 실패";
                                // }
                            ?>
                                <!-- 데이터 행 -->
                                <tr data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $counter; ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $counter; ?>">
                                    <td class="col-1">
                                        <span class="custom-link" onclick="window.location.href='salesMain.php?SALE_ID=<?php echo $row['SALE_ID']; ?>'">
                                            <?php echo $row['SALE_ID']; ?>
                                        </span>
                                    </td>
                                    <td class="col-1" style="text-align: left; padding-left: 3%;">
                                        <span class="custom-link" onclick="window.location.href='deviceMain.php?SN=<?php echo $row['SN']; ?>'">
                                            <?php echo $row['SN']; ?>
                                        </span>
                                    </td>
                                    <td class="col-1">
                                        <?php
                                        // $vendor_row가 null이 아니고, NAME 키에 해당하는 값이 존재하면 그 값을 출력
                                        // 그렇지 않으면 '정보없음'을 출력
                                        // echo isset($vendor_row['NAME']) ? $vendor_row['NAME'] : '-';
                                        echo isset($row['VENDOR_NAME']) ? $row['VENDOR_NAME'] : '-';
                                        ?>
                                    </td>
                                    <td class="col-1"><?php echo $row['TYPE']; ?></td>
                                    <td class="col-1"><?php echo $row['MANAGER']?></td>
                                    <td class="col-1" style="text-align:right; padding-right:2%;"><?php echo number_format($row['PRICE'] ?? 0); ?><span>원</span></td>
                                    <td class="col-1">
                                        <?php
                                        if (isset($row['WARRANTY']) && !empty($row['WARRANTY'])) {
                                            echo $row['WARRANTY'] . " 개월";
                                        }
                                        ?>
                                    </td>
                                    <td class="col-1"><?php echo $row['S_DATE']; ?></td>
                                    <td class="col-1"><?php echo $row['D_DATE']; ?></td>
                                    <td class="col-1">
                                        <?php
                                        echo $row['INSPECTION'] . ' / ' . $row['SUPPORT'];
                                        ?>
                                    </td>
                                    <td class="col-1"><?php echo $row['REF']; ?></td>
                                    <td class="col-1"><button class="btn btn-secondary" onclick="location.href='licenseUpdate.php?saleId=<?php echo urlencode($row['SALE_ID']); ?>&SN=<?php echo urlencode($row['SN']); ?>';">수정</button></td>
                                    </td>
                                </tr>
                                <!-- 아코디언 내용 -->
                                <tr>
                                    <td colspan="12">
                                        <div id="flush-collapse<?php echo $counter; ?>" class="collapse accor-style">
                                            <table class="detail-table">
                                                <?php
                                                $stmt = $dbconnect->prepare("SELECT * FROM LICENSE_HISTORY WHERE SALE_ID=? AND SN=?");
                                                $stmt->bind_param("ss", $row['SALE_ID'], $row['SN']);
                                                $stmt->execute();
                                                $details_result = $stmt->get_result();

                                                if ($details_result->num_rows > 0) {
                                                    echo "<tr><td colspan='2'> 총 갱신 횟수: " . $details_result->num_rows . "회</td></tr>";
                                                    echo "<tr class='item-separator'><td colspan='2'></td></tr>";
                                                    while ($details = $details_result->fetch_assoc()) {
                                                ?>
                                                        <tr>
                                                            <th style="margin-left:10px; font-weight: bold;">갱신번호 :</th>
                                                            <td><?php echo !empty($details['NO']) ? $details['NO'] : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th style="margin-left:10px; font-weight: bold;">유형 :</th>
                                                            <td><?php echo !empty($details['TYPE']) ? $details['TYPE'] : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th style="margin-left:10px; font-weight: bold;">담당 엔지니어 :</th>
                                                            <td><?php echo !empty($details['MANAGER']) ? $details['MANAGER'] : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th style="margin-left:10px; font-weight: bold;">가격 :</th>
                                                            <td><?php echo number_format($row['PRICE'] ?? 0); ?>원</td>
                                                        </tr>
                                                        <tr>
                                                            <th style="margin-left:10px; font-weight: bold;">시작일 :</th>
                                                            <td><?php echo !empty($details['S_DATE']) ? $details['S_DATE'] : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th style="margin-left:10px; font-weight: bold;">종료일 :</th>
                                                            <td><?php echo !empty($details['D_DATE']) ? $details['D_DATE'] : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th style="margin-left:10px; font-weight: bold;">보증기간 :</th>
                                                            <td><?php echo !empty($details['WARRANTY']) ? $details['WARRANTY'] . ' 개월' : '-'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th style="margin-left:10px; font-weight: bold;">검사 :</th>
                                                            <td><?php echo !empty($details['INSPECTION']) ? $details['INSPECTION'] : '-'; ?></td>
                                                        </tr>
                                                        <tr class="detail-row">
                                                            <th style="margin-left:10px; font-weight: bold;">지원 :</th>
                                                            <td><?php echo !empty($details['SUPPORT']) ? $details['SUPPORT'] : '-'; ?></td>
                                                        </tr>
                                                        <tr class="item-separator">
                                                            <td colspan="2"></td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                    // 일치하는 데이터가 없는 경우 처리
                                                    echo "<tr><td colspan='2'>해당 라이센스 히스토리가 없습니다.</td></tr>";
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <!-- 아코디언 내용 끝 -->
                            <?php
                                $counter++;
                            endwhile;
                            ?>
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
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
        <script src="salesMain.js"></script>
        <script src=".__/auto_complete.js"></script>
</body>

</html>