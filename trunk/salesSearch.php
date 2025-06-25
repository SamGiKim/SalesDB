<?php
//echo "PHP code is executed! TOP"; (OK)
//salesSearch.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";
mysqli_set_charset($dbconnect, "utf8");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>거래명세서 검색</title>
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
    <div class="main">
        <div class="header-container">
            <header>거래명세서 검색</header>
        </div>
        <div class="content">
            <div class="inputBox mx-auto shadow p-5 mt-4">
                <div class="btn-cancel position-relative top-0">
                    <button type="button" class="btn-close" aria-label="Close" onclick="redirectToSalesMain()"></button>
                </div>
                <form id="searchForm" hx-post="salesMain.php?action=search" hx-trigger="submit" hx-target=".main">
                    <table class="inputTbl">
                        <tr>
                            <td><label for="saleId">판매번호</label></td>
                            <td><input type="text" class="input" name="saleId" id="saleId">
                            <span class="error-message">&nbsp;</span>
                        </td>
                        </tr>
                        <tr>
                            <td><label for="vId">납품처</label></td>
                            <td>
                                <input type="text" class="input" name="vId" id="vId">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr> 
                            <td><label for="cId">거래처</label></td>
                            <td>
                                <input type="text" class="input" name="cId" id="cId">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="pbizId">거래처영업</label></td>
                            <td>
                                <input type="text" class="input" name="cbizId" id="cbizId">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="bizId">담당자명</label></td>
                            <td>
                                <input type="text" class="input" name="bizId" id="bizId">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="deliverDate">납품일</label></td>
                            <td>
                                <!-- Input for start date -->
                                <input type="date" class="input short" name="deliverDate" id="deliverDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="dDate">유지보수 시작일</label></td>
                            <td>
                                <!-- Input for start date -->
                                <input type="date" class="input short" name="sDate" id="sDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="dDate">유지보수 종료일</label></td>
                            <td>
                                <!-- Input for start date -->
                                <input type="date" class="input short" name="dDate" id="dDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="orderNo">주문번호</label></td>
                            <td>
                                <input type="text" class="input" name="orderNo" id="orderNo">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="ref">비고</label></td>
                            <td>
                                <textarea class="txtarea" name="ref" id="ref" rows="2" cols="52"></textarea>
                            </td>
                        </tr>
                    </table>
                    <div class="btn-class">
                        <button type="submit" class="btn btn-primary search wide-btn">검색</button>
                    </div>
                </form>
            </div>
        </div>
            </div>                   
    </div>  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
</body>
</html>