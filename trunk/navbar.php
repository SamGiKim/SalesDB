<?php
//  navbar.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 현재 페이지의 파일 이름을 가져옴
$current_page = basename($_SERVER['PHP_SELF']);

// 세션이 이미 시작되었는지 확인하고, 아니라면 세션을 시작
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// 로그인 상태를 확인하기 위해 세션에 저장된 username을 가져옴
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

?>

<!--
  PHP의 다양한 기능을 사용하여 현재 페이지를 동적으로 감지하고 해당 메뉴 항목을 활성화.
  navbar.php 파일에서 각 메뉴 항목의 href 속성과 현재 URL을 비교합니다.
  일치하는 경우 해당 메뉴 항목에 active 클래스를 추가하여 현재 페이지를 표시합니다.
-->
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #5a9bd5;">
  <!-- 반응형 네비 주기 -->
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <!--네비바 메뉴 -->
    <ul class="navbar-nav me-auto">
      <li class="nav-item">
        <!-- strpos는 해당 파일에 '키워드'가 포함되어있으면 active 클래스를 추가하고 아니면 추가하지 않는다.  -->
        <a class="nav-link <?= strpos($current_page, 'dashboard') !== false ? 'active' : '' ?>" href="dashboard.php">대시보드</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'sales') !== false ? 'active' : '' ?>" href="salesMain.php">거래명세서</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'license') !== false ? 'active' : '' ?>" href="licenseMain.php">라이센스</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'device') !== false ? 'active' : '' ?>" href="deviceMain.php">장비</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= strpos($current_page, 'support') !== false ? 'active' : '' ?>" href="#">지원이력</a>
      </li>
    </ul>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item">
        <?php
        if (isset($_SESSION['user_id'])) {
          // 로그인된 상태
          $user_name = $_SESSION['user_name'];
          echo "<div style=\"display: flex; align-items: center; background-color: #5a9bd5;\">";
          echo "<p style=\"font-size: 20px; font-weight: bold; color: white; margin-right: 10px; margin-top:10px; padding: 10px 0;\">안녕하세요 " . htmlspecialchars($user_name) . "님, 로그인 되었습니다.</p>";
          echo "<a href=\"logout.php\" class=\"nav-link\" style=\"font-size: 18px; font-weight:600; color: #505050; background-color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; transition: background-color 0.3s ease;\">로그아웃</a>";
          echo "<style>";
          echo ".nav-link:hover { text-shadow: none !important; }"; // 마우스 오버 시 텍스트 그림자 제거
          echo "</style>";
          echo "</div>";
        } else {
          // 로그아웃 상태일 때 로그인 이미지와 링크 표시
          echo '<a href="index.php" class="nav-link">';
          echo '<img src="/sales/img/login.png" alt="Login" style="height: 45px; width: auto; margin-right:5%;">';
          echo '</a>';
        }
        ?>

      </li>
    </ul>
  </div>
</nav>