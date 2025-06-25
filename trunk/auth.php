<?php
// auth.php
session_start();

require_once "sales_db.php"; // 데이터베이스 연결 설정

// 로그인 검증 로직
if (!isset($_SESSION['user_id'])) {
    // 사용자가 로그인하지 않았으면 로그인 페이지로 리디렉션
    header("Location: index.php");
    exit();
}
