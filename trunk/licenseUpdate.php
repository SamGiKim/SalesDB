<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

function checkExistsWithCompositeKey($dbconnect, $table, $column1, $value1, $column2, $value2, $sel_column)
{
    $query = "SELECT " . $sel_column . " FROM " . $table . " WHERE " . $column1 . " = ? AND " . $column2 . " = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("ss", $value1, $value2);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row[$sel_column] ?? null;
}
function fetchLicense($dbconnect, $saleId, $SN)
{
    $query = "SELECT * FROM LICENSE WHERE SALE_ID = ? AND SN = ?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("ss", $saleId, $SN);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}
function updateLicense($dbconnect, $type, $price, $sDate, $dDate, $ref, $warranty, $inspection, $support, $saleId, $SN)
{
    $query = "UPDATE LICENSE SET `TYPE`=?, PRICE=?, S_DATE=?, D_DATE=?, REF=?, WARRANTY=?, INSPECTION=?, SUPPORT=? WHERE SALE_ID=? AND SN=?";
    $stmt = $dbconnect->prepare($query);
    $stmt->bind_param("sisssissss", $type, $price, $sDate, $dDate, $ref, $warranty, $inspection, $support, $saleId, $SN);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}
function calculateDDate($sDate, $warranty)
{
    $date = new DateTime($sDate);
    $date->modify("+$warranty months - 1 day");
    return $date->format('Y-m-d');
}

$html_values = [];

function validateInputs($dbconnect, $saleId, $SN)
{
    // TODO: 입력값 검증 로직 추가
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 폼이 제출된 경우 처리
    $saleId = $_POST['saleId'] ?? '';
    $SN = $_POST['SN'] ?? '';
    $type = $_POST['type'] ?? '';
    $price = $_POST['price'] ?? '';
    $sDate = trim($_POST['sDate']) !== '' ? $_POST['sDate'] : $existingLicense['S_DATE'];
    $dDate = trim($_POST['dDate']) !== '' ? $_POST['dDate'] : $existingLicense['D_DATE'];
    $ref = $_POST['ref'] ?? '';
    $inspection = $_POST['inspection'] ?? null;
    $support = $_POST['support'] ?? null;
    $existingLicense = fetchLicense($dbconnect, $saleId, $SN);
    $warranty = trim($_POST['warranty']) !== '' ? $_POST['warranty'] : $existingLicense['WARRANTY'];
    // $warranty = $existingLicense['WARRANTY'];
    // $sDate = $_POST['sDate'] ?? '';
    // $dDate = $_POST['dDate'] ?? '';
    // post에서 직접 가져오는 대신, db의 fetchLicense 함수 사용하여 warranty 값 가져오면
    // $warranty = isset($_POST['warranty']) ? intval($_POST['warranty']) : 0;


    if (!validateInputs($dbconnect, $saleId, $SN)) {
        echo "<script>alert('유효하지 않은 입력!');</script>";
        exit();
    }

    $dDate = calculateDDate($sDate, $warranty);
    $affected = updateLicense($dbconnect, $type, $price, $sDate, $dDate, $ref, $warranty, $inspection, $support,  $saleId, $SN);

    if ($affected >= 0) {
        echo "<script>alert('업데이트 성공!'); window.location.href='licenseMain.php';</script>";
    } else {
        echo "<script>alert('업데이트 실패!');</script>";
    }
    $html_values = fetchLicense($dbconnect, $saleId, $SN);
} else {
    //페이지가 로드되는 경우 처리
    $saleId = $_GET['saleId'] ?? null;
    $SN = $_GET['SN'] ?? null;

    //license 정보 파라미터로 보내기
    $html_values = fetchLicense($dbconnect, $saleId, $SN);
}
if ($html_values) {
    // 여기서 $html_values 사용
} else {
    echo "결과없음";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>거래명세서 수정</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="salesMain.css">
    <script src="https://unpkg.com/htmx.org@1.9.4"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.27.0/dist/date-fns.umd.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="main">
        <div class="header-container">
            <header>라이센스 수정</header>
        </div>
        <div class="content">
            <div class="inputBox mx-auto shadow p-5 mt-4">
                <div class="btn-cancel position-relative top-0">
                    <button type="button" class="btn-close" aria-label="Close" onclick="redirectToLicenseMain()"></button>
                </div>
                <form id="updateForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <table class="inputTbl">
                        <tr>
                            <td><label for="saleId">명세서번호</label></td>
                            <td><input type="text" class="text" name="saleId" id="saleId" value="<?php echo isset($html_values['SALE_ID']) ? $html_values['SALE_ID'] : ''; ?>" readonly></td>
                            <!-- <td><input type="text" class="input" name="saleId" id="saleId" value="<?php echo $html_values['SALE_ID'] ?>" readonly></td> -->
                        </tr>
                        <tr>
                            <td><label for="SN">SN</label></td>
                            <td><input type="text" class="text" name="SN" id="SN" value="<?php echo isset($html_values['SN']) ? $html_values['SN'] : ''; ?>"></td>
                            <!-- <td><input type="text" class="text" name="SN" id="SN" value="<?php echo isset($html_values['SN']) ? $html_values['SN'] : ''; ?>" readonly></td> -->
                        </tr>
                        <tr>
                            <td><label for="type">유지보수유형</label></td>
                            <td>
                                <select class="input short selectstyle" name="type" id="type">
                                    <option value="유상" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '유상' ? 'selected' : ''; ?>>유상</option>
                                    <option value="무상" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '무상' ? 'selected' : ''; ?>>무상</option>
                                    <option value="건당" <?php echo isset($html_values['TYPE']) && $html_values['TYPE'] == '건당' ? 'selected' : ''; ?>>건당</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="price">라이센스 가격</label></td>
                            <td><input type="text" class="input short" name="price" id="price" value="<?php echo isset($html_values['PRICE']) ? $html_values['PRICE'] : ''; ?>" style="text-align:right;"><span>원</span></td>
                            <!-- <td><input type="text" class="input short" name="price" id="price" value="<?php echo $html_values['PRICE'] ?>"><span>원</span></td> -->
                        </tr>
                        <tr>
                            <td><label for="warranty">보증기간</label></td>
                            <td>
                                <input type="text" class="input short" name="warranty" id="warranty" value="<?php echo $html_values['WARRANTY'] ?? ''; ?>" style="text-align:right; padding-right: 30px;"><span>개월</span>
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="sDate">시작일</label></td>
                            <td><input type="date" class="input short" name="sDate" id="sDate" value="<?php echo $html_values['S_DATE'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="dDate">종료일</label></td>
                            <td><input type="date" class="input short" name="dDate" id="dDate" value="<?php echo $html_values['D_DATE'] ?>"></td>
                        </tr>
                        <tr>
                            <td><label for="inspection">점검</label></td>
                            <!--건별, 월 방문, 월 원격, 분기 방문, 분기 원격, 반기 방문, 반기 원격, 비고참조-->
                            <td>
                                <select class="input short selectstyle" name="inspection" id="inspection" value="<?php echo $html_values['INSPECTION'] ?? ''; ?>">
                                    <option value="">선택 안함</option> <!-- 선택 안함 옵션 추가 -->
                                    <option value="건별" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '건별' ? 'selected' : ''; ?>>건별</option>
                                    <option value="월방문" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '월방문' ? 'selected' : ''; ?>>월방문</option>
                                    <option value="월원격" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '월원격' ? 'selected' : ''; ?>>월원격</option>
                                    <option value="분기방문" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '분기방문' ? 'selected' : ''; ?>>분기방문</option>
                                    <option value="분기원격" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '분기원격' ? 'selected' : ''; ?>>분기원격</option>
                                    <option value="반기방문" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '반기방문' ? 'selected' : ''; ?>>반기방문</option>
                                    <option value="반기원격" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '반기원격' ? 'selected' : ''; ?>>반기원격</option>
                                    <option value="비고참조" <?php echo isset($html_values['INSPECTION']) && $html_values['INSPECTION'] == '비고참조' ? 'selected' : ''; ?>>비고참조</option>
                                </select>
                                <span class="error-message" id="error-type"></span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="support">파트너지원</label></td>
                            <!-- T0: 지원없음, T1: 정기점검, T2:유지 보수-->
                            <!-- 2024.03.20 변경 : T5: 지원없음, T3: 정기점검, T1:유지 보수-->
                            <td>
                                <select class="input short selectstyle" name="support" id="support" value="<?php echo $html_values['SUPPORT'] ?? ''; ?>">
                                    <option value="">선택 안함</option> <!-- 선택 안함 옵션 추가 -->
                                    <option value="T5:지원없음" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T5:지원없음' ? 'selected' : ''; ?>>T5:지원없음</option>
                                    <option value="T3:정기점검" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T3:정기점검' ? 'selected' : ''; ?>>T3:정기점검</option>
                                    <option value="T1:유지보수" <?php echo isset($html_values['SUPPORT']) && $html_values['SUPPORT'] == 'T1:유지보수' ? 'selected' : ''; ?>>T1:유지보수</option>
                                </select>
                                <span class="error-message" id="error-type"></span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="ref">비고</label></td>
                            <td>
                                <!-- textarea에는 value 속성이 사용되지 않는다. 태그 사이에 넣어야 값이 나온다.  -->
                                <textarea class="txtarea" name="ref" id="ref" rows="2" cols="52"><?php echo isset($html_values['REF']) ? $html_values['REF'] : ''; ?></textarea>
                            </td>
                        </tr>
                    </table>
                    <div class="btn-class">
                        <button type="submit" class="btn btn-primary update wide-btn">수정</button>
                        <button class="btn btn-primary delete wide-btn" hx-post="licenseDelete.php?saleId=<?php echo $html_values['SALE_ID']; ?>&SN=<?php echo $html_values['SN']; ?>" hx-confirm="정말 삭제하시겠습니까?" hx-swap="outerHTML" hx-target="#updateForm">삭제
                        </button>
                        <button type="button" id="renewalButton" class="btn btn-primary renewal wide-btn">재계약</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // 엔터치면 수정되는거 방지
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('updateForm').addEventListener('keypress', function(event) {
                if (event.keyCode === 13) { // 13은 엔터 키 코드
                    event.preventDefault(); // 폼 제출을 방지
                }
            });
        });

        //사용자가 sDate 설정하면 동적으로 warranty의 개월 수 더해서 dDate 나오도록 하기.
        window.onload = function() {
            const sDateInput = document.getElementById('sDate');
            const dDateInput = document.getElementById('dDate');
            const warrantyInput = document.getElementById('warranty');

            sDateInput.addEventListener('change', function() {
                const sDate = new Date(sDateInput.value);

                // console.log("Warranty value:", warrantyInput.value);

                // warranty 값 넘긴다.
                let warrantyMonths = parseInt(warrantyInput.value);

                if (isNaN(warrantyMonths)) {
                    console.error("warranty 값 넘기기 실패");
                    return; // Exit if parsing failed
                }
                // 계산
                sDate.setMonth(sDate.getMonth() + warrantyMonths);

                //-1 day 추가
                sDate.setDate(sDate.getDate() - 1);

                const resultDate = sDate.toISOString().split('T')[0];
                dDateInput.value = resultDate;
            });
        };

        //type이 무상일 때, price 0원으로 비활성화
        document.addEventListener('DOMContentLoaded', function() {
            // 유지보수 유형 select 요소를 가져옵니다.
            let typeSelect = document.getElementById('type');
            // 라이센스 가격 input 요소를 가져옵니다.
            let priceInput = document.getElementById('price');

            // 유지보수 유형 select 요소에 변경 이벤트 리스너를 추가합니다.
            typeSelect.addEventListener('change', function() {
                // 선택된 값이 '무상'인 경우
                if (this.value === '무상') {
                    // 라이센스 가격을 0으로 설정하고 비활성화합니다.
                    priceInput.value = 0;
                    priceInput.setAttribute('disabled', true);
                } else {
                    // 그렇지 않은 경우, 입력란을 활성화합니다.
                    priceInput.removeAttribute('disabled');
                }
            });

            // 페이지 로드 시 초기 설정을 위한 함수 호출
            typeSelect.dispatchEvent(new Event('change'));
            console.log('typeSelect 변경 이벤트 발생');
        });


        // 새로운 정보를 입력해서 '재계약'버튼을 누르면 licenseRenewal.php로 이동해서 ajax로 동시에 입력
        document.getElementById('renewalButton').addEventListener('click', function() {
            let saleId = document.getElementById('saleId').value;
            let SN = document.getElementById('SN').value;
            let type = document.getElementById('type').value;
            let price = document.getElementById('price').value;
            let warranty = document.getElementById('warranty').value;
            let sDate = document.getElementById('sDate').value;
            let dDate = document.getElementById('dDate').value;
            let inspection = document.getElementById('inspection').value;
            let support = document.getElementById('support').value;
            let ref = document.getElementById('ref').value;

            fetch('licenseRenewal.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        saleId,
                        SN,
                        type,
                        price,
                        warranty,
                        sDate,
                        dDate,
                        inspection,
                        support,
                        ref
                    })
                })
                .then(response => response.text()) // 응답 본문을 텍스트로 가져옵니다.
                .then(text => {
                    console.log("Response text:", text);
                    try {
                        // 텍스트를 JSON으로 변환
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("JSON 파싱 오류:", e);
                        throw new Error("서버로부터 잘못된 응답을 받았습니다.");
                    }
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = 'licenseMain.php'; // 페이지 이동
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
</body>

</html>