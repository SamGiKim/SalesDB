<?php
// index.php
// 에러 확인, 세션 시작, DB 연결 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 세션 데이터 초기화
// session_start(); // 세션 시작
// session_unset(); // 모든 세션 변수 해제
// session_destroy(); // 세션 파괴

// 세션이 이미 시작되었는지 확인하고, 아니라면 세션을 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 로그인 상태인 경우 대시보드 페이지로 리디렉션
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// echo "<pre>Session: ";
// print_r($_SESSION);
// echo "</pre>";

require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");

//로그인 폼 데이터 처리
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id']) && isset($_POST['password'])) {
    var_dump($_POST);
    $user_id = mysqli_real_escape_string($dbconnect, $_POST['user_id']);
    $pw = mysqli_real_escape_string($dbconnect, $_POST['password']);
    // 준비된 명령문 사용하여 SQL 인젝션 방지
    $stmt = $dbconnect->prepare("SELECT user_no, user_name, user_id, password FROM LOGIN WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // var_dump($user);
            if ($pw === $user['password']) { // 평문 비밀번호 비교
                // 세션에 사용자 고유 식별번호와 사용자 이름(username) 저장
                $_SESSION['user_no'] = $user['user_no'];  // 데이터베이스의 `no` 컬럼
                $_SESSION['user_name'] = $user['user_name'];  // 데이터베이스의 `username` 컬럼
                $_SESSION['user_id'] = $user['user_id'];  // 데이터베이스의 `user_id` 컬럼

                // 로그인이 성공한 경우 콘솔에 로그를 출력
                $_SESSION['success_message'] = "로그인 성공! " . $_SESSION['user_name'] . "님 환영합니다.";
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<script>alert('아이디 또는 비밀번호가 잘못되었습니다.');</script>";
            }
        } else {
            echo "<script>alert('해당 사용자가 없습니다.');</script>";
        }
    } else {
        echo "<script>alert('로그인 처리 중 오류가 발생했습니다.');</script>";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h2 class="text-center mb-4"></h2><br>
                <div class="card p-4 border-top border-bottom mx-auto shadow p-5 mt-4">
                    <div class="card-body text-center">
                        <?php
                        // 로그인 상태 확인
                        if (isset($_SESSION['user_id'])) {
                            // 로그인된 상태
                            $user_name = $_SESSION['user_name'];
                            echo "<p style=\"font-size: 20px;\">안녕하세요 {$user_name}님, 로그인 되었습니다.</p>";
                            // 로그아웃 버튼 표시
                            echo '<a href="logout.php" class="btn btn-secondary" style="margin-top:30px;width: 120px; font-size: 16px; font-weight: 900; border-radius: 10px;">로그아웃</a>';
                        } else {
                            // 로그인 폼 표시
                        ?>
                            <form action="index.php" method="post" id="login-frm" autocomplete="on">
                                <div class="form-group">
                                    <input type="text" class="form-control mb-3" id="loginId" name="user_id" placeholder="아이디" style="max-width: 300px; margin: 0 auto;" autocomplete="username">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control mb-3" id="loginPw" name="password" placeholder="비밀번호" style="max-width: 300px; margin: 0 auto;" autocomplete="current-password">
                                </div>
                                <div class="form-group text-center">
                                    <input type="submit" class="btn btn-primary" value="로그인" style="margin-top:30px;width: 100px; font-size: 17px; font-weight: 900; border-radius: 10px;">
                                </div>
                            </form>
                        <?php
                        }
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="./.__/auto_complete.js"></script>
</body>

</html>