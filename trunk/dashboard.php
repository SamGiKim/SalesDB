<?php
//  dashboard.php
require_once "auth.php";

if (isset($_SESSION['success_message'])) {
    echo "<script>alert('" . $_SESSION['success_message'] . "');</script>";
    // 메시지를 표시한 후에는 세션에서 삭제
    unset($_SESSION['success_message']);
}

require_once "sales_db.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// session_start();

mysqli_set_charset($dbconnect, "utf8");

$today = date("Y-m-d");


// 1. 총 고객 totalVendors
function get_totalVendors() {
    global $dbconnect;
    $query_vendors = "SELECT COUNT(DISTINCT V_ID) as vendor_count FROM VENDOR";
    $result_vendors = mysqli_query($dbconnect, $query_vendors);
    
    if ($result_vendors) {
        $row_vendors = mysqli_fetch_assoc($result_vendors);
        $totalVendors = $row_vendors['vendor_count'];
    } else {
        // 쿼리 실행에 실패한 경우
        $totalVendors = "Error";
    }
    return $totalVendors;
}
$totalVendors = get_totalVendors();

// 2024.03.20 추가요청 : 장비수 
function get_totalDevices(){
    global $dbconnect;
    $query_devices="SELECT COUNT(DISTINCT SN) as device_count FROM DEVICE";
    $result_devices = mysqli_query($dbconnect, $query_devices);

    if($result_devices){
        $row_devices = mysqli_fetch_assoc($result_devices);
        $totalDevices = $row_devices['device_count'];
    }else{
        $totalDevices = "Error";
    }
    return $totalDevices;
}
$totalDevices = get_totalDevices();

// 2. 총 유지보수 고객 totalLicenses 
// 250711 jhkim 유상조건 추가
function get_totalLicenses($today, &$query_licenses = "", $_type = "") {
	global $dbconnect;
	if($_type == "유상") 
		$query_licenses = "SELECT COUNT(DISTINCT CONCAT(L.SALE_ID, '-', SN)) as license_count 
        FROM LICENSE L
        LEFT JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
        LEFT JOIN VENDOR AS V ON S.V_ID = V.V_ID
        WHERE L.TYPE = '$_type' AND '$today' <= L.D_DATE ";
	else 
		$query_licenses = "SELECT COUNT(DISTINCT CONCAT(L.SALE_ID, '-', SN)) as license_count 
        FROM LICENSE L
        LEFT JOIN SALES AS S ON L.SALE_ID = S.SALE_ID
        LEFT JOIN VENDOR AS V ON S.V_ID = V.V_ID
        WHERE '$today' <= L.D_DATE ";
// <<< 250711 hjkim -

    $result_licenses = mysqli_query($dbconnect, $query_licenses);
    if ($result_licenses) {
        $row_licenses = mysqli_fetch_assoc($result_licenses);
        $totalLicenses = $row_licenses['license_count'];
    } else {
        // 쿼리 실행에 실패한 경우
        $totalLicenses = "Error";
    }
    return $totalLicenses;
}
// >>> 250711 hjkim - 
$_tmp = "";
$totalLicenses_paid = get_totalLicenses($today, $_tmp, "유상");
// <<< 250711 hjkim - 
$totalLicenses = get_totalLicenses($today);

// 3. 유상 보증기간 종료예정 리스트
$paidToBeExpired = 0;  // 변수 초기화
$d_date_tobe_expired = "";
list($paidToBeExpired, $d_date_paid_tobe_expired) = get_paidToBeExpired($today, $d_date_tobe_expired);
function get_paidToBeExpired($today, &$d_date_tobe_expired, &$query_paid_tobe_expired = "") {
    global $dbconnect;
    $d_date_tobe_expired = date("Y-m-d", strtotime("+30 days"));
    
    $query_paid_tobe_expired = "SELECT COUNT(*) as cnt 
        FROM LICENSE L
        JOIN SALES S ON L.SALE_ID = S.SALE_ID
        JOIN VENDOR V ON S.V_ID = V.V_ID
        WHERE L.TYPE = '유상' 
        AND L.D_DATE BETWEEN CURDATE() AND '$d_date_tobe_expired'";
        
    $result_paid_tobe_expired = mysqli_query($dbconnect, $query_paid_tobe_expired);
    if ($result_paid_tobe_expired) {
        $row_paid_tobe_expired = mysqli_fetch_assoc($result_paid_tobe_expired);
        $paidToBeExpired = $row_paid_tobe_expired['cnt'];
    } else {
        $paidToBeExpired = "Error";
    }
    return array($paidToBeExpired, $d_date_tobe_expired);
}

// 4. 무상 보증기간 종료예정 리스트
$freeToBeExpired = 0;  // 변수 초기화
$freeToBeExpired = get_freeLicenseToBeExpired($today);
function get_freeLicenseToBeExpired($today, &$query_free_tobe_expired = "") {
    global $dbconnect;
    $freeToBeExpired = 0;  // 기본값 설정
    
    $d_date_tobe_expired = date("Y-m-d", strtotime("+30 days"));
    $query_free_tobe_expired = "SELECT COUNT(*) as cnt 
        FROM LICENSE L
        JOIN SALES S ON L.SALE_ID = S.SALE_ID
        JOIN VENDOR V ON S.V_ID = V.V_ID
        WHERE L.TYPE = '무상' 
        AND L.D_DATE BETWEEN CURDATE() AND '$d_date_tobe_expired'";
        
    $result_free_tobe_expired = mysqli_query($dbconnect, $query_free_tobe_expired);
    if ($result_free_tobe_expired) {
        $row_free_tobe_expired = mysqli_fetch_assoc($result_free_tobe_expired);
        $freeToBeExpired = $row_free_tobe_expired['cnt'];
    }
    return $freeToBeExpired;
}

// 5. 유상 보증기간 만료
$paidExpired = 0;  // 변수 초기화
$paidExpired = get_paidExpired($today);
function get_paidExpired($today, &$query_paid_expired = "") {
    global $dbconnect;
    $paidExpired = 0;  // 기본값 설정
    
    $query_paid_expired = "SELECT COUNT(*) as cnt 
        FROM LICENSE L
        JOIN SALES S ON L.SALE_ID = S.SALE_ID
        JOIN VENDOR V ON S.V_ID = V.V_ID
        WHERE L.TYPE = '유상' 
        AND L.D_DATE <= CURDATE()";
        
    $result_paid_expired = mysqli_query($dbconnect, $query_paid_expired);
    if ($result_paid_expired) {
        $row_paid_expired = mysqli_fetch_assoc($result_paid_expired);
        $paidExpired = $row_paid_expired['cnt'];
    }
    return $paidExpired;
}

// 6. 무상 보증기간 만료
$freeExpired = 0;  // 변수 초기화
$freeExpired = get_freeExpired($today);
function get_freeExpired($today, &$query_free_expired = "") {
    global $dbconnect;
    $freeExpired = 0;  // 기본값 설정
    
    $query_free_expired = "SELECT COUNT(*) as cnt 
        FROM LICENSE L
        JOIN SALES S ON L.SALE_ID = S.SALE_ID
        JOIN VENDOR V ON S.V_ID = V.V_ID
        WHERE L.TYPE = '무상' 
        AND L.D_DATE <= CURDATE()";
        
    $result_free_expired = mysqli_query($dbconnect, $query_free_expired);
    if ($result_free_expired) {
        $row_free_expired = mysqli_fetch_assoc($result_free_expired);
        $freeExpired = $row_free_expired['cnt'];
    }
    return $freeExpired;
}

// 7.EOS
function get_eos($today, &$query_eos = "") {
    global $dbconnect;
   
    // 8년 전 날짜 계산 (시작일)
    $start_date = date("Y-m-d", strtotime($today . " -8 years"));

    // 6년 6개월 전 날짜 계산 (종료일)
    $end_date = date("Y-m-d", strtotime($today . " -6 years -6 months"));

    // 수정된 쿼리 (올바른 순서)
    $query_eos = "SELECT COUNT(*) as cnt FROM SALES WHERE '$start_date' <= DELIVER_DATE AND DELIVER_DATE <= '$end_date'";

    $result_eos_query = mysqli_query($dbconnect, $query_eos);

    if($result_eos_query) {
        $row_eos = mysqli_fetch_assoc($result_eos_query);
        $eos = $row_eos['cnt'];
    } else {
        $eos = "Error";
    }
    return array($eos, $start_date, $end_date); // Return eos, start_date, and end_date
}

// Call the function and list the results
list($eos, $start_date, $end_date) = get_eos($today, $eos_tobe_expired);




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>대시보드 메인</title>
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
  <div class="container-fluid mt-5  main-screen">
    <div class="row">
        <div class="col-12 text-center">
            <header></header>
        </div>
    </div>
    <div class="row dashboard-row">
        <div class="col-md-2 col-lg-3"></div> <!-- md(태블릿화면)에서 2개 열, lg(PC화면)에서 3개열 -->
        <div class="col-12 col-md-8 col-lg-6">
        <div class="table-wrapper">
            <table class="table dashboard-tbl">
                <tbody class="main-screen">
                    <tr>
                        <th>납품처</th>
                        <td><?= $totalVendors ?><span>건</span></td>
                    </tr>
                    <tr>
                        <th>장비</th>
                        <td><?= $totalDevices ?><span>대</span></td>
                    </tr>
                    <tr>
                        <th>유상 서비스 계약</th>
                        <td>
                            <a class="dashLink" 
                                href="licenseMain.php?cmd=000&dDateFrom=<?= date('Y-m-d') ?>&type=유상" 
                                id="licenseLink0">
                                <?= $totalLicenses_paid ?>
                            </a>
                            <span>건 (총</span> <?= $totalLicenses ?><span>건)</span>

                        </td>
                    </tr>
                    <tr>
                        <th>유상 보증기간 종료 예정<span style="color:red; font-weight:100; font-size: 15px;">(D-30)</span></th>
                        <td>
                            <a class="dashLink" href="licenseMain.php?cmd=001&dDateOfLicense=<?= $d_date_tobe_expired ?>" id="licenseLink1"><?=$paidToBeExpired ?></a>
                            <span style="margin-left:3px;">건</span>
                        </td>
                    </tr>
                    <tr>
                        <th>무상 보증기간 종료 예정<span style="color:red; font-weight:100; font-size: 15px; margin-left:3px">(D-30)</span></th>
                        <td>
                            <a class="dashLink" href="licenseMain.php?cmd=002&dDateOfLicense=<?= $d_date_tobe_expired ?>" id="licenseLink2"><?=$freeToBeExpired ?></a>
                            <span style="margin-left:3px;">건</span>
                        </td>
                    </tr>
                    <tr>
                        <th>유상 보증기간 만료</th>
                        <td>
                            <a class="dashLink" href="licenseMain.php?cmd=003" id="licenseLink3"><?= $paidExpired ?></a>
                            <span style="margin-left:3px;">건</span>
                        </td>
                    </tr>
                    <tr>
                        <th>무상 보증기간 만료</th>
                        <td>
                            <a class="dashLink" href="licenseMain.php?cmd=004" id="licenseLink4"><?= $freeExpired ?></a>
                            <span style="margin-left:3px;">건</span>
                        </td>
                    </tr>
                    <tr>
                        <th>EOS<span style="color:red; font-weight:100; font-size: 15px;">(납품일+6년6개월~8년)</span></th>
                        <td>
                            <!-- eos_start_date와 eos_end_date를 링크 파라미터로 추가 -->
                            <a class="dashLink" href="salesMain.php?condition=eos&eos_start_date=<?= $start_date ?>&eos_end_date=<?= $end_date ?>" id="licenseLink5"><?= $eos ?></a>
                            <span style="margin-left:3px;">건</span>
                        </td>
                    </tr>
                </table>
                <div class="btn-class">
                    <button type="button" class="btn btn-primary" id="refresh-button">갱신</button>
              </div>
            <?php include 'dashboard_search.php'; ?>
        </div>
    </div>
        <div class="col-md-2 col-lg-3"></div> <!-- 오른쪽 여백을 위한 빈 div -->
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src=".__/auto_complete.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // console.log("페이지 로딩 완료");

        // 버튼 클릭 시 데이터 업데이트
        document.getElementById('refresh-button').addEventListener('click', function () {
            // AJAX 요청을 이용해 서버에서 데이터를 업데이트하는 PHP 파일을 호출합니다.
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'refreshData.php', true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    // 성공적으로 데이터 업데이트를 완료하면 페이지를 다시 로드합니다.
                    location.reload();
                    alert('실시간 데이터로 업데이트했습니다.')
                } else {
                    // 실패한 경우 에러 메시지를 표시합니다.
                    alert('데이터 업데이트에 실패했습니다.');
                }
            };

            xhr.send();
        });
    });
</script>

</body>
</html>