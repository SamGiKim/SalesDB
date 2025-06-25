<?php
// logout.php
session_start(); // 세션 시작

// 세션 변수 제거
$_SESSION = array();

// 세션 쿠키 제거
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 세션 파괴
session_destroy();

header("Location: index.php"); // 로그인 페이지(또는 다른 페이지)로 리다이렉트
exit();
?>
