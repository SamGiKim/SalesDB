<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";

mysqli_set_charset($dbconnect, "utf8");

if (isset($_POST['search_query'])) {
    $search_query = mysqli_real_escape_string($dbconnect, $_POST['search_query']); 

    $sql =  "SELECT DISTINCT s.SALE_ID as SALE_ID ";
    $sql .= "FROM SALES as s ";
    $sql .= "LEFT JOIN VENDOR AS v ON s.V_ID = v.V_ID ";
    $sql .= "LEFT JOIN CUSTOMER AS c ON s.C_ID = c.C_ID ";
    $sql .= "LEFT JOIN BUSINESS AS b1 ON s.CBIZ_ID = b1.BIZ_ID "; 
    $sql .= "LEFT JOIN BUSINESS AS b2 ON s.BIZ_ID = b2.BIZ_ID ";
    $sql .= "WHERE v.NAME LIKE '%$search_query%' ";
    $sql .= "OR c.NAME LIKE '%$search_query%' ";
    $sql .= "OR b1.NAME LIKE '%$search_query%' ";
    $sql .= "OR b2.NAME LIKE '%$search_query%' ";
    
    //디버깅
    // var_dump($_POST['search_query']);
    // echo "<br>";
    // echo $sql;

    $result = mysqli_query($dbconnect, $sql);

    if (mysqli_num_rows($result) > 0) {
        $sale_ids = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sale_ids[] = $row['SALE_ID'];
        }
        // SALE_ID들을 콤마로 구분하여 문자열로 변환
        $sale_ids_string = implode(",", $sale_ids);
        
        // salesMain.php로 리디렉션하면서 SALE_ID들을 query parameter로 전송
        header("Location: salesMain.php?sale_ids={$sale_ids_string}");
        exit;
    } else {
        echo "검색 결과가 없습니다.";
    }
    

} else {
    echo "검색어를 입력해주세요.";
}

mysqli_close($dbconnect);
?>
