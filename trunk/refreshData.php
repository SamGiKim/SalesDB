<?php
//echo "PHP code is executed! TOP"; (OK)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";
require_once "dashboard.php";
// 데이터 업데이트 작업 수행
$totalVendors = get_totalVendors();
$totalLicenses = get_totalLicenses($today);
list($paidToBeExpired, $d_date_paid_tobe_expired) = get_paidToBeExpired($today, $d_date_tobe_expired);
$freeToBeExpired = get_freeLicenseToBeExpired($today);
$paidExpired = get_paidExpired($today);
$freeExpired = get_freeExpired($today);
$eos = get_eos($today, $eos_tobe_expired);

// 데이터 업데이트가 성공했다고 가정하고, 응답 메시지 생성
$response = "데이터가 성공적으로 업데이트되었습니다.";

// JSON 형식으로 응답 메시지 생성
echo json_encode(['message' => $response, 'totalVendors' => $totalVendors, 'totalLicenses' => $totalLicenses, 'paidToBeExpired' => $paidToBeExpired, 'freeToBeExpired' => $freeToBeExpired, 'paidExpired' => $paidExpired, 'freeExpired' => $freeExpired, 'eos' => $eos]);
mysqli_set_charset($dbconnect, "utf8");

// 데이터 업데이트 작업 수행
$totalVendors = get_totalVendors();
$totalLicenses = get_totalLicenses($today);
list($paidToBeExpired, $d_date_paid_tobe_expired) = get_paidToBeExpired($today, $d_date_tobe_expired);
$freeToBeExpired = get_freeLicenseToBeExpired($today);
$paidExpired = get_paidExpired($today);
$freeExpired = get_freeExpired($today);
$eos = get_eos($today, $eos_tobe_expired);

// 데이터 업데이트가 성공했다고 가정하고, 응답 메시지 생성
$response = "데이터가 성공적으로 업데이트되었습니다.";

// JSON 형식으로 응답 메시지 생성
echo json_encode(['message' => $response, 'totalVendors' => $totalVendors, 'totalLicenses' => $totalLicenses, 'paidToBeExpired' => $paidToBeExpired, 'freeToBeExpired' => $freeToBeExpired, 'paidExpired' => $paidExpired, 'freeExpired' => $freeExpired, 'eos' => $eos]);

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
                        <th>진행중 유지보수</th>
                        <td><?= $totalLicenses ?><span>건</span></td>
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
                        <th>EOS<span style="color:red; font-weight:100; font-size: 15px;">(D-180)</span></th>
                        <td>
                            <a class="dashLink" href="salesMain.php?condition=eos&dDateOfEos=<?= $eos_tobe_expired ?>" id="licenseLink5"><?= $eos ?></a>
                            <span style="margin-left:3px;">건</span>
                        </td>
                    </tr>
                    <div class="btn-class">
                        <button type="button" class="btn btn-primary wide-btn insert" onclick="refreshData()">갱신</button>
                  </div>
            </table>
            <?php include 'dashboard_search.php'; ?>
        </div>
    </div>
        <div class="col-md-2 col-lg-3"></div> <!-- 오른쪽 여백을 위한 빈 div -->
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
   
</body>
</html>